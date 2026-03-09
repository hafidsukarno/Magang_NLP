<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOcr;
use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\Department;
use App\Models\DepartmentQuota;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function create()
    {
        $departments = Department::all();
        return view('mahasiswa.create', compact('departments'));
    }

    public function store(Request $r)
    {
        // ======================
        // VALIDASI FORM
        // ======================
        $r->validate([
            'type' => 'required|in:individual,group',

            'leader_name' => 'required|string|max:255',
            'leader_email' => 'required|email',
            'leader_phone' => 'required|string|max:20',

            'university' => 'required|string|max:255',
            'major' => 'required|string|max:255',

            'department_id' => 'nullable|exists:departments,id',
            'duration' => 'required|numeric|max:5',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',

            'file' => 'required|mimes:pdf|max:10240',

        ], [
            // messages...
            'period_start.required' => 'Tanggal mulai magang wajib diisi.',
            'period_end.required' => 'Tanggal selesai magang wajib diisi.',
            'period_end.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        // generate registration code
        do {
            $code = 'PAG-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (Application::where('registration_code', $code)->exists());

        $departmentId = $r->department_id ?: null;
        $periodStart = $r->period_start;
        $periodEnd = $r->period_end;

        // how many people this application will occupy
        $neededPeople = 1;
        if ($r->type === 'group' && $r->members && is_array($r->members)) {
            $neededPeople = 1 + count($r->members);
        }

        // ======================
        // QUOTA CHECK (submission-time)
        // NOTE: only count 'diterima' as occupying quota (so 'menunggu' does NOT reserve seat)
        // ======================
        if ($departmentId) {
            // find period-based quota covering the requested period
            $quotaRecord = DepartmentQuota::where('department_id', $departmentId)
                ->where('period_start', '<=', $periodStart)
                ->where('period_end', '>=', $periodEnd)
                ->first();

            if ($quotaRecord) {
                $quotaValue = (int) $quotaRecord->quota;
            } else {
                $dept = Department::find($departmentId);
                $quotaValue = $dept ? (int)($dept->quota ?? 0) : null;
            }

            if ($quotaValue !== null) {
                // count only accepted people ('diterima') overlapping the period
                $acceptedApps = Application::where('department_id', $departmentId)
                    ->where('status', 'diterima')
                    ->where(function ($q) use ($periodStart, $periodEnd) {
                        $q->whereBetween('period_start', [$periodStart, $periodEnd])
                            ->orWhereBetween('period_end', [$periodStart, $periodEnd])
                            ->orWhere(function ($qq) use ($periodStart, $periodEnd) {
                                $qq->where('period_start', '<=', $periodStart)
                                    ->where('period_end', '>=', $periodEnd);
                            });
                    })
                    ->get();

                $acceptedPeople = $acceptedApps->sum(function ($a) {
                    return $a->type === 'group' ? ($a->members->count() + 1) : 1;
                });

                // If accepted already full, we should reject new submission (no seats left).
                if (($acceptedPeople + $neededPeople) > $quotaValue) {
                    return back()
                        ->withInput()
                        ->withErrors(['quota' => "Kuota tidak mencukupi untuk periode yang dipilih (sisa " . max(0, $quotaValue - $acceptedPeople) . ")."]);
                }
            }
        }

        // ======================
        // Simpan data (no DB lock needed here because acceptance checks lock)
        // ======================
        DB::beginTransaction();
        try {
            $path = $r->file('file')->store('magang_uploads');

            $app = Application::create([
                'user_id' => auth()->id(),
                'registration_code' => $code,
                'type' => $r->type,
                'leader_name' => $r->leader_name,
                'leader_email' => $r->leader_email,
                'leader_phone' => $r->leader_phone,
                'university' => $r->university,
                'major' => $r->major,
                'department_id' => $departmentId,
                'duration' => $r->duration,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'file_path' => $path,
                'status' => 'menunggu'
            ]);

            if ($r->type === 'group' && $r->members) {
                foreach ($r->members as $m) {
                    ApplicationMember::create([
                        'application_id' => $app->id,
                        'name' => $m['name'] ?? null,
                        'university' => $m['university'] ?? $r->university,
                        'major' => $m['major'] ?? $r->major,
                        'email' => $m['email'] ?? null,
                        'phone' => $m['phone'] ?? null,
                    ]);
                }
            } else {
                ApplicationMember::create([
                    'application_id' => $app->id,
                    'name' => $r->leader_name ?? 'Anonymous',
                    'university' => $r->university,
                    'major' => $r->major,
                    'email' => $r->leader_email,
                    'phone' => $r->leader_phone
                ]);
            }

            ProcessOcr::dispatch($app);

            DB::commit();

            return redirect()
                ->route('mahasiswa.applications.index')
                ->with('success', 'Pengajuan berhasil! Kode pengajuan Anda: ')
                ->with('code', $code);
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error('Application store error: ' . $ex->getMessage());
            return back()->withInput()->withErrors(['general' => 'Terjadi kesalahan saat menyimpan pengajuan.']);
        }
    }

    /**
     * Dashboard Mahasiswa - Lihat status pengajuan terbaru
     */
    public function mahasiswaDashboard()
    {
        $user = auth()->user();
        
        // Get student's applications
        $applications = Application::where('user_id', $user->id)
            ->latest()
            ->paginate(5);

        // Get summary
        $summary = [
            'total' => Application::where('user_id', $user->id)->count(),
            'menunggu' => Application::where('user_id', $user->id)->where('status', 'menunggu')->count(),
            'diproses' => Application::where('user_id', $user->id)->where('status', 'diproses')->count(),
            'diterima' => Application::where('user_id', $user->id)->where('status', 'diterima')->count(),
            'ditolak' => Application::where('user_id', $user->id)->where('status', 'ditolak')->count(),
        ];

        return view('mahasiswa.dashboard', compact('applications', 'summary'));
    }

    /**
     * List semua pengajuan mahasiswa (history)
     */
    public function mahasiswaApplications()
    {
        $user = auth()->user();
        
        $search = request('search');
        $status = request('status');

        $applications = Application::where('user_id', $user->id)
            ->when($search, function($q) use ($search) {
                $q->where('leader_name', 'like', "%$search%")
                  ->orWhere('registration_code', 'like', "%$search%")
                  ->orWhere('university', 'like', "%$search%");
            })
            ->when($status && $status !== 'all', function($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('mahasiswa.applications', compact('applications'));
    }

    /**
     * Detail pengajuan mahasiswa
     */
    public function mahasiswaShow($id)
    {
        $user = auth()->user();
        
        $app = Application::with(['members', 'rpaResult', 'department'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return view('mahasiswa.show', compact('app'));
    }
}
