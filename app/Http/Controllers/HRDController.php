<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HRDController extends Controller {

    public function index() {
        // load departments with accepted_count (kuota diambil langsung dari kolom dept->quota)
        $departments = Department::withCount(['applications as accepted_count' => function($q){
            $q->where('status','diterima');
        }])->get();

        $applications = Application::orderByRaw("CASE 
                WHEN status = 'menunggu' THEN 1 
                WHEN status = 'diterima' THEN 2 
                WHEN status = 'ditolak' THEN 3 
                ELSE 4 END")
            ->latest()
            ->paginate(20);

        return view('hrd.dashboard', compact('applications','departments'));
    }


    public function show($id) {
        $app = Application::with(['members', 'department.majors', 'department.skills'])->findOrFail($id);
        
        // Calculate for CURRENT department
        $currentCalc = $this->calculateCompatibility($app, $app->department);
        $score = $currentCalc['total'];
        $breakdown = $currentCalc['breakdown'];

        // Calculate for ALL departments to provide alternatives
        $allDepts = Department::with(['majors', 'skills'])->get();
        $deptRecommendations = [];

        foreach ($allDepts as $dept) {
            // Calculate available slots via helper
            $availability = $this->getQuotaAvailability($dept, $app);
            
            $calc = $this->calculateCompatibility($app, $dept);
            $deptRecommendations[] = [
                'id' => $dept->id,
                'name' => $dept->name,
                'score' => $calc['total'],
                'is_current' => $app->department_id == $dept->id,
                'target_majors' => $dept->majors->pluck('name')->toArray(),
                'target_skills' => $dept->skills->pluck('name')->toArray(),
                'available_slots' => $availability['available'],
                'can_fit' => $availability['can_fit']
            ];
        }

        // Sort by score descending
        usort($deptRecommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $departments = $allDepts;
        $compatibility = $currentCalc; // Set compatibility for the blade

        return view('hrd.show', compact('app', 'departments', 'score', 'breakdown', 'deptRecommendations', 'compatibility'));
    }

    /**
     * Calculate compatibility score between an application and a department
     */
    private function calculateCompatibility($app, $dept) {
        if (!$dept) return ['total' => 0, 'breakdown' => []];
        
        $totalScore = 0;
        $breakdown = [];
        $matchedRequirement = '';

        // Prepare student data for matching
        $studentMajor = strtolower($app->major ?? '');
        $studentProdi = strtolower($app->program_studi ?? '');
        $studentSkills = strtolower($app->keahlian ?? '');

        // 1. Major Match (Base 60%)
        $majorMatch = false;
        if ($dept->majors->count() > 0) {
            foreach ($dept->majors as $m) {
                $reqMajor = strtolower($m->name);
                // Bidirectional matching: Requirement in Student Data OR Student Data in Requirement
                if (
                    ($studentMajor && (stripos($studentMajor, $reqMajor) !== false || stripos($reqMajor, $studentMajor) !== false)) ||
                    ($studentProdi && (stripos($studentProdi, $reqMajor) !== false || stripos($reqMajor, $studentProdi) !== false))
                ) {
                    $majorMatch = true;
                    $matchedRequirement = $m->name;
                    break;
                }
            }
        } else {
            $majorMatch = true;
            $matchedRequirement = 'Umum';
        }

        if ($majorMatch) {
            $totalScore += 60;
            $breakdown[] = [
                'label' => "Kesesuaian Jurusan ($matchedRequirement)",
                'points' => 60,
                'status' => 'Cocok',
                'icon' => 'graduation-cap',
                'color' => 'text-green-600'
            ];
        } else {
            $breakdown[] = [
                'label' => 'Kesesuaian Jurusan',
                'points' => 0,
                'status' => 'Tidak Cocok',
                'icon' => 'graduation-cap',
                'color' => 'text-red-600'
            ];
        }

        // 2. Skill Bonus (Max 20%)
        if ($studentSkills && $dept->skills->count() > 0) {
            $skillCount = $dept->skills->count();
            $bonusPerSkill = 20 / $skillCount;
            $matchedSkills = 0;
            $matchedNames = [];

            foreach ($dept->skills as $s) {
                $reqSkill = strtolower($s->name);
                if (stripos($studentSkills, $reqSkill) !== false || stripos($reqSkill, $studentSkills) !== false) {
                    $matchedSkills++;
                    $matchedNames[] = $s->name;
                }
            }

            if ($matchedSkills > 0) {
                $skillBonus = (int) min(20, round($matchedSkills * $bonusPerSkill));
                $totalScore += $skillBonus;
                $breakdown[] = [
                    'label' => "Keahlian Relevan (" . implode(', ', $matchedNames) . ")",
                    'points' => $skillBonus,
                    'status' => 'Bonus +',
                    'icon' => 'zap',
                    'color' => 'text-blue-600'
                ];
            }
        }

        // 3. Quota & Period Match (Max 20%)
        $availability = $this->getQuotaAvailability($dept, $app);
        
        if ($availability['can_fit']) {
            $totalScore += 20;
            $breakdown[] = [
                'label' => "Ketersediaan Kuota & Periode (" . $availability['available'] . " slot)",
                'points' => 20,
                'status' => 'Tersedia',
                'icon' => 'calendar',
                'color' => 'text-green-600'
            ];
        } else {
            $breakdown[] = [
                'label' => "Ketersediaan Kuota & Periode (" . $availability['available'] . " slot)",
                'points' => 0,
                'status' => 'Penuh/Tidak Cukup',
                'icon' => 'calendar',
                'color' => 'text-red-600'
            ];
        }

        return ['total' => min(100, $totalScore), 'breakdown' => $breakdown];
    }

    /**
     * Helper to calculate quota availability for a specific department and application period
     */
    private function getQuotaAvailability($dept, $app) {
        if (!$dept || !$app || !$app->period_start || !$app->period_end) {
            return ['quota' => 0, 'used' => 0, 'available' => 0, 'can_fit' => false];
        }

        $periodStart = $app->period_start;
        $periodEnd = $app->period_end;
        $appPeopleCount = $app->type === 'group' ? ($app->members->count() + 1) : 1;

        // Ambil kuota langsung dari departemen
        $quotaValue = (int)($dept->quota ?? 0);

        // 2. Count used slots (Accepted people overlapping the period)
        $acceptedApps = Application::where('department_id', $dept->id)
            ->where('status', 'diterima')
            ->where('id', '!=', $app->id)
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->where('period_start', '<=', $periodEnd)
                  ->where('period_end', '>=', $periodStart);
            })
            ->with('members')
            ->get();

        $usedPeople = $acceptedApps->sum(function ($a) {
            return $a->type === 'group' ? ($a->members->count() + 1) : 1;
        });

        $availableSlots = max(0, $quotaValue - $usedPeople);

        return [
            'quota' => $quotaValue,
            'used' => $usedPeople,
            'available' => $availableSlots,
            'can_fit' => $availableSlots >= $appPeopleCount
        ];
    }

    /**
     * Cek apakah jurusan mahasiswa cocok dengan persyaratan departemen.
     * Mengembalikan true jika cocok ATAU jika departemen tidak punya persyaratan jurusan (umum).
     */
    private function isMajorMatch($app, $dept): bool {
        if (!$dept) return false;

        // Jika departemen tidak punya persyaratan jurusan, dianggap cocok (umum)
        if ($dept->majors->count() === 0) return true;

        $studentMajor = strtolower($app->major ?? '');
        $studentProdi = strtolower($app->program_studi ?? '');

        foreach ($dept->majors as $m) {
            $reqMajor = strtolower($m->name);
            if (
                ($studentMajor && (stripos($studentMajor, $reqMajor) !== false || stripos($reqMajor, $studentMajor) !== false)) ||
                ($studentProdi && (stripos($studentProdi, $reqMajor) !== false || stripos($reqMajor, $studentProdi) !== false))
            ) {
                return true;
            }
        }

        return false;
    }

    public function applications()
    {
        $search = request('search');
        $type = request('type');
        $status = request('status');

        $applications = Application::with('department', 'members')
            ->where('status', '!=', 'pending')
            ->when($search, function($q) use ($search) {
                $q->where(function($qq) use ($search) {
                    $qq->where('leader_name','like',"%$search%")
                      ->orWhere('major','like',"%$search%")
                      ->orWhereHas('department', function($q2) use ($search){
                          $q2->where('name','like',"%$search%");
                      });
                });
            })
            ->when($type, function($q) use ($type) {
                $q->where('type', $type);
            })
            ->when($status, function($q) use ($status) {
                $q->where('status', $status);
            })
            // Custom Sort: Menunggu (1), Diproses (2), then others
            ->orderByRaw("CASE 
                WHEN status = 'menunggu' THEN 1 
                WHEN status = 'diterima' THEN 2 
                WHEN status = 'ditolak' THEN 3 
                ELSE 4 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Calculate score for each app
        foreach ($applications as $a) {
            $calc = $this->calculateCompatibility($a, $a->department);
            $a->ai_score = $calc['total'];
        }

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

                // Validasi kesesuaian jurusan
                $targetDept = Department::with('majors')->find($targetDeptId);
                if ($targetDept && !$this->isMajorMatch($app, $targetDept)) {
                    DB::rollBack();
                    $syarat = $targetDept->majors->pluck('name')->join(', ');
                    return back()->withErrors([
                        'quota' => "Jurusan mahasiswa ({$app->major}) tidak sesuai dengan syarat departemen {$targetDept->name}. Syarat jurusan: {$syarat}."
                    ])->withInput();
                }

                // Ambil kuota langsung dari departemen
                $dept = Department::find($targetDeptId);
                $quotaValue = $dept ? (int)($dept->quota ?? 0) : 0;

                if ($quotaValue > 0) {
                    // count already accepted PEOPLE (status == 'diterima') overlapping the period, exclude current application
                    $acceptedApps = Application::where('department_id', $targetDeptId)
                        ->where('status', 'diterima')
                        ->where('id', '!=', $app->id)
                        ->where(function ($q) use ($app) {
                            $q->where('period_start', '<=', $app->period_end)
                              ->where('period_end', '>=', $app->period_start);
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

            // Only update note if provided, or if department changed
            if ($r->filled('hrd_note')) {
                $app->hrd_note = $r->hrd_note;
            } elseif ($oldDepartmentId != $newDepartmentId && $newDepartmentId != null) {
                $newDeptName = Department::find($newDepartmentId)->name ?? '-';
                $app->hrd_note = "Anda dipindahkan ke departemen {$newDeptName} untuk menyesuaikan ketersediaan kuota dan kompetensi Anda.";
            }

            $app->save();

            DB::commit();
            return back()->with('success','Data berhasil diperbarui.');
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error('HRD update error: '.$ex->getMessage());
            return back()->withErrors(['general' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }


    /**
     * View any application related file safely.
     * Supports type query: main, permohonan, proposal.
     */
    public function viewFile(Request $r, $id) {
        $app = Application::findOrFail($id);
        $type = $r->query('type', 'main');

        $path = '';
        if ($type === 'permohonan') {
            $path = $app->surat_permohonan_path;
        } elseif ($type === 'proposal' || $type === 'main') {
            $path = $app->proposal_path;
        } else {
            $path = $app->proposal_path;
        }

        if (!$path) {
            abort(404, 'File tidak ditemukan untuk tipe ini.');
        }

        // Try to locate file in various possible storage locations
        $storagePaths = [
            storage_path('app/public/' . $path),
            storage_path('app/private/' . $path),
            storage_path('app/private/magang_uploads/' . basename($path)),
            storage_path('app/' . $path),
        ];

        foreach ($storagePaths as $filePath) {
            if (file_exists($filePath)) {
                return response()->file($filePath);
            }
        }

        abort(404, 'File fisik tidak ditemukan di server.');
    }
}
