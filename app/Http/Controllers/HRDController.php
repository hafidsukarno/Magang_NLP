<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Department;
use App\Models\DepartmentQuota;
use App\Services\RpaScoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HRDController extends Controller {

    public function index() {
        // load departments with quotas and accepted_count
        $departments = Department::with(['quotas' => function($q){
            $q->orderBy('period_start', 'desc');
        }])->withCount(['applications as accepted_count' => function($q){
            $q->where('status','diterima');
        }])->get();

        $applications = Application::latest()->paginate(20);

        return view('hrd.dashboard', compact('applications','departments'));
    }


    public function show($id) {
        $app = Application::with(['members','rpaResult','department'])->findOrFail($id);

        $rpa = new RpaScoringService();

        $score = null;
        $breakdown = null;
        $recommended = null;
        $deptRecommendations = null;

        if ($app->rpaResult && $app->rpaResult->fields) {

            $fields = $app->rpaResult->fields;

            // Always compute score
            $result = $rpa->computeScore($app, $fields);

            $score = $result['total'];
            $breakdown = $result['details'];

            // Status
            $recommended = $rpa->recommendStatus($app, $score);

            // Rekomendasi per departemen
            $deptRecommendations = $rpa->simulateForAllDepartments($app, $fields);
        }

        $departments = Department::all();

        return view('hrd.show',
            compact('app','score','breakdown','recommended','departments','deptRecommendations')
        );
    }

    public function applications()
    {
        $search = request('search');

        $applications = Application::with('department')
            ->when($search, function($q) use ($search) {
                $q->where('leader_name','like',"%$search%")
                ->orWhere('major','like',"%$search%")
                ->orWhereHas('department', function($q2) use ($search){
                    $q2->where('name','like',"%$search%");
                });
            })
            ->oldest()
            ->paginate(10)
            ->withQueryString();

        return view('hrd.applications', compact('applications'));
    }

    /**
     * Update application (department change, status change).
     * Handles:
     * - status transition checks (diterima requires available quota)
     * - ditolak requires hrd_note
     * - if moving accepted app to other department => check quota in new department
     */
    public function update(Request $r, $id) {
        $r->validate([
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|in:menunggu,diproses,diterima,ditolak',
            'hrd_note' => 'nullable|string',
        ]);

        $app = Application::with('members')->findOrFail($id);

        $oldStatus = $app->status;
        $oldDepartmentId = $app->department_id;

        $newStatus = $r->status;
        $newDepartmentId = $r->department_id ?: null;

        // If rejecting, require hrd_note
        if ($newStatus === 'ditolak' && trim($r->hrd_note ?? '') === '') {
            return back()->withErrors(['hrd_note' => 'Keterangan penolakan wajib diisi.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Only check quota when moving to 'diterima'
            if ($newStatus === 'diterima') {
                $targetDeptId = $newDepartmentId ?? $app->department_id;
                if (!$targetDeptId) {
                    DB::rollBack();
                    return back()->withErrors(['department_id' => 'Tidak ada departemen tujuan untuk diterima.'])->withInput();
                }

                // prefer period-based quota that covers the app period and lock it
                $quotaRecord = DepartmentQuota::where('department_id', $targetDeptId)
                    ->where('period_start', '<=', $app->period_start)
                    ->where('period_end', '>=', $app->period_end)
                    ->lockForUpdate()
                    ->first();

                $quotaValue = null;
                if ($quotaRecord) {
                    $quotaValue = (int) $quotaRecord->quota;
                } else {
                    // fallback to legacy department quota (no lock possible on single value)
                    $dept = Department::find($targetDeptId);
                    $quotaValue = $dept ? (int)($dept->quota ?? 0) : null;
                }

                if ($quotaValue !== null) {
                    // count already accepted PEOPLE (status == 'diterima') overlapping the period, exclude current application
                    $acceptedApps = Application::where('department_id', $targetDeptId)
                        ->where('status', 'diterima')
                        ->where('id', '!=', $app->id)
                        ->where(function ($q) use ($app) {
                            $q->whereBetween('period_start', [$app->period_start, $app->period_end])
                              ->orWhereBetween('period_end', [$app->period_start, $app->period_end])
                              ->orWhere(function($qq) use ($app) {
                                  $qq->where('period_start', '<=', $app->period_start)
                                     ->where('period_end', '>=', $app->period_end);
                              });
                        })
                        ->get();

                    $usedPeople = $acceptedApps->sum(function ($a) {
                        return $a->type === 'group' ? ($a->members->count() + 1) : 1;
                    });

                    // how many people does this application occupy?
                    $thisPeople = $app->type === 'group' ? ($app->members->count() + 1) : 1;

                    if (($usedPeople + $thisPeople) > $quotaValue) {
                        DB::rollBack();
                        return back()->withErrors(['quota' => 'Kuota sudah penuh untuk periode tersebut — tidak dapat menerima pengajuan.'])->withInput();
                    }
                } else {
                    DB::rollBack();
                    return back()->withErrors(['quota' => 'Tidak ada kuota yang tersedia untuk departemen ini.'])->withInput();
                }
            }

            // Apply changes
            $app->department_id = $newDepartmentId;
            $app->status = $newStatus;
            // set hrd_note only when rejecting or explicit provided
            if ($newStatus === 'ditolak') {
                $app->hrd_note = $r->hrd_note;
            } else {
                if ($r->hrd_note) {
                    $app->hrd_note = $r->hrd_note;
                } else {
                    $app->hrd_note = null;
                }
            }

            $app->save();

            // Recompute score if rpaResult exists and department assigned
            if ($app->department_id && $app->rpaResult) {
                $rpa = new RpaScoringService();
                $score = $rpa->computeScore($app, $app->rpaResult->fields);
                $app->score = $score['total'];
                $app->save();
            }

            DB::commit();
            return back()->with('success','Data berhasil diperbarui.');
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error('HRD update error: '.$ex->getMessage());
            return back()->withErrors(['general' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }


    public function viewPdf($id) {
        $app = Application::findOrFail($id);

        $file = storage_path('app/private/magang_uploads/' . basename($app->file_path));

        if (!file_exists($file)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return response()->file($file);
    }
}
