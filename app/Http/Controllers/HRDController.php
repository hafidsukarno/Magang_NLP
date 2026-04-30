<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Department;
use App\Models\DepartmentQuota;
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
        $app = Application::with(['members', 'department.majors', 'department.skills'])->findOrFail($id);
        
        // Calculate for CURRENT department
        $currentCalc = $this->calculateCompatibility($app, $app->department);
        $score = $currentCalc['total'];
        $breakdown = $currentCalc['breakdown'];

        // Calculate for ALL departments to provide alternatives
        $allDepts = Department::with(['majors', 'skills'])->get();
        $deptRecommendations = [];

        foreach ($allDepts as $dept) {
            // Calculate available slots
            $quotaValue = (int)($dept->quota ?? 0);
            
            // Count accepted people for this department overlapping the app period
            $acceptedApps = Application::where('department_id', $dept->id)
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
                ->with('members')
                ->get();

            $usedPeople = $acceptedApps->sum(function ($a) {
                return $a->type === 'group' ? ($a->members->count() + 1) : 1;
            });
            
            $availableSlots = max(0, $quotaValue - $usedPeople);
            $appPeopleCount = $app->type === 'group' ? ($app->members->count() + 1) : 1;

            $calc = $this->calculateCompatibility($app, $dept);
            $deptRecommendations[] = [
                'id' => $dept->id,
                'name' => $dept->name,
                'score' => $calc['total'],
                'is_current' => $app->department_id == $dept->id,
                'target_majors' => $dept->majors->pluck('name')->toArray(),
                'target_skills' => $dept->skills->pluck('name')->toArray(),
                'available_slots' => $availableSlots,
                'can_fit' => $availableSlots >= $appPeopleCount
            ];
        }

        // Sort by score descending
        usort($deptRecommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $departments = $allDepts;

        return view('hrd.show', compact('app', 'departments', 'score', 'breakdown', 'deptRecommendations'));
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

        // 1. Major Match (Base 80%)
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
            $totalScore += 80;
            $breakdown[] = [
                'label' => "Kesesuaian Jurusan ($matchedRequirement)",
                'points' => 80,
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

        return ['total' => $totalScore, 'breakdown' => $breakdown];
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
                WHEN status = 'diproses' THEN 2 
                ELSE 3 END")
            ->latest()
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
     * Supports type query: main, permohonan, laporan.
     */
    public function viewFile(Request $r, $id) {
        $app = Application::findOrFail($id);
        $type = $r->query('type', 'main');

        $path = '';
        if ($type === 'permohonan') {
            $path = $app->surat_permohonan_path;
        } elseif ($type === 'laporan') {
            $path = $app->surat_laporan_path;
        } else {
            $path = $app->file_path;
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

    /**
     * Update status untuk member tertentu dalam group application
     */
    public function updateMember(Request $r, $memberId) {
        $r->validate([
            'status' => 'required|in:menunggu,diterima,ditolak',
            'hrd_note' => 'nullable|string',
        ]);

        $member = \App\Models\ApplicationMember::findOrFail($memberId);
        $app = $member->application;

        // If rejecting, require hrd_note
        if ($r->status === 'ditolak' && trim($r->hrd_note ?? '') === '') {
            return back()->withErrors(['hrd_note' => 'Keterangan penolakan wajib diisi.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Only check quota when moving to 'diterima'
            if ($r->status === 'diterima') {
                $targetDeptId = $app->department_id;
                if (!$targetDeptId) {
                    DB::rollBack();
                    return back()->withErrors(['department_id' => 'Tidak ada departemen tujuan untuk diterima.'])->withInput();
                }

                // Check quota - count already accepted people including this new one
                $quotaRecord = DepartmentQuota::where('department_id', $targetDeptId)
                    ->where('period_start', '<=', $app->period_start)
                    ->where('period_end', '>=', $app->period_end)
                    ->lockForUpdate()
                    ->first();

                $quotaValue = null;
                if ($quotaRecord) {
                    $quotaValue = (int) $quotaRecord->quota;
                } else {
                    $dept = Department::find($targetDeptId);
                    $quotaValue = $dept ? (int)($dept->quota ?? 0) : null;
                }

                if ($quotaValue !== null) {
                    // Tentukan berapa quota yang dibutuhkan
                    // - Individual: leader = 1 orang
                    // - Group: leader (ketua) = 1 orang + members
                    $neededPeople = 1; // 1 untuk member yang sedang diproses

                    // count already accepted PEOPLE overlapping the period, exclude this application
                    $existingApps = Application::where('department_id', $targetDeptId)
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

                    $usedPeople = 0;
                    foreach ($existingApps as $a) {
                        if ($a->type === 'individual') {
                            // Individual: hitung jika leader diterima
                            if ($a->leader_status == 'diterima') {
                                $usedPeople++;
                            }
                        } else {
                            // Group: hitung ketua + members yang diterima
                            if ($a->leader_status == 'diterima') {
                                $usedPeople++; // ketua
                            }
                            $usedPeople += $a->members->where('status', 'diterima')->count();
                        }
                    }

                    if (($usedPeople + $neededPeople) > $quotaValue) {
                        DB::rollBack();
                        return back()->withErrors(['quota' => 'Kuota sudah penuh untuk periode tersebut — tidak dapat menerima member ini.'])->withInput();
                    }
                } else {
                    DB::rollBack();
                    return back()->withErrors(['quota' => 'Tidak ada kuota yang tersedia untuk departemen ini.'])->withInput();
                }
            }

            // Update member status
            $member->status = $r->status;
            if ($r->status === 'ditolak') {
                $member->hrd_note = $r->hrd_note;
            } else {
                $member->hrd_note = $r->hrd_note ?: null;
            }
            $member->save();

            // Sync aplikasi status: jika semua (leader + members) sudah punya keputusan, ubah status ke 'selesai'
            $this->syncApplicationStatus($app);

            DB::commit();
            return back()->with('success', 'Status member berhasil diperbarui.');
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error('Member update error: '.$ex->getMessage());
            return back()->withErrors(['general' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }

    /**
     * Update status untuk leader (ketua tim) dalam group application
     */
    public function updateLeader(Request $r, $appId) {
        $r->validate([
            'status' => 'required|in:menunggu,diterima,ditolak',
            'hrd_note' => 'nullable|string',
        ]);

        $app = Application::findOrFail($appId);

        // If rejecting, require hrd_note
        if ($r->status === 'ditolak' && trim($r->hrd_note ?? '') === '') {
            return back()->withErrors(['hrd_note' => 'Keterangan penolakan wajib diisi.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Only check quota when moving to 'diterima'
            if ($r->status === 'diterima') {
                $targetDeptId = $app->department_id;
                if (!$targetDeptId) {
                    DB::rollBack();
                    return back()->withErrors(['department_id' => 'Tidak ada departemen tujuan untuk diterima.'])->withInput();
                }

                // Check quota - count already accepted people including this leader
                $quotaRecord = DepartmentQuota::where('department_id', $targetDeptId)
                    ->where('period_start', '<=', $app->period_start)
                    ->where('period_end', '>=', $app->period_end)
                    ->lockForUpdate()
                    ->first();

                $quotaValue = null;
                if ($quotaRecord) {
                    $quotaValue = (int) $quotaRecord->quota;
                } else {
                    $dept = Department::find($targetDeptId);
                    $quotaValue = $dept ? (int)($dept->quota ?? 0) : null;
                }

                if ($quotaValue !== null) {
                    // Tentukan berapa quota yang dibutuhkan
                    // - Individual: leader = 1 orang
                    // - Group: leader (ketua) = 1 orang
                    $neededPeople = 1;

                    // count already accepted PEOPLE overlapping the period, exclude this application
                    $existingApps = Application::where('department_id', $targetDeptId)
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

                    $usedPeople = 0;
                    foreach ($existingApps as $a) {
                        if ($a->type === 'individual') {
                            // Individual: hitung jika leader diterima
                            if ($a->leader_status == 'diterima') {
                                $usedPeople++;
                            }
                        } else {
                            // Group: hitung ketua + members yang diterima
                            if ($a->leader_status == 'diterima') {
                                $usedPeople++; // ketua
                            }
                            $usedPeople += $a->members->where('status', 'diterima')->count();
                        }
                    }

                    if (($usedPeople + $neededPeople) > $quotaValue) {
                        DB::rollBack();
                        return back()->withErrors(['quota' => 'Kuota sudah penuh untuk periode tersebut — tidak dapat menerima leader.'])->withInput();
                    }
                } else {
                    DB::rollBack();
                    return back()->withErrors(['quota' => 'Tidak ada kuota yang tersedia untuk departemen ini.'])->withInput();
                }
            }

            // Update leader status
            $app->leader_status = $r->status;
            if ($r->status === 'ditolak') {
                $app->leader_note = $r->hrd_note;
            } else {
                $app->leader_note = $r->hrd_note ?: null;
            }
            $app->save();

            // Sync aplikasi status: jika semua (leader + members) sudah punya keputusan, ubah status ke 'selesai'
            $this->syncApplicationStatus($app);

            DB::commit();
            return back()->with('success', 'Status leader berhasil diperbarui.');
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error('Leader update error: '.$ex->getMessage());
            return back()->withErrors(['general' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }

    /**
     * Sync status aplikasi:
     * - Jika tipe individual: check leader status
     * - Jika tipe group: check leader + semua members
     * - Jika semua sudah punya keputusan (tidak ada 'menunggu'), ubah app status menjadi 'selesai'
     */
    private function syncApplicationStatus(Application $app) {
        $app = $app->fresh(['members']);

        // Jika Individual: Status aplikasi = Status leader
        if ($app->type === 'individual') {
            if ($app->leader_status !== 'menunggu') {
                $app->status = $app->leader_status;
                $app->save();
            }
            return;
        }

        // Jika Group:
        $memberStatuses = $app->members->pluck('status')->toArray();
        $memberStatuses[] = $app->leader_status;

        $hasMenunggu = in_array('menunggu', $memberStatuses);
        $hasDiterima = in_array('diterima', $memberStatuses);
        $hasDitolak = in_array('ditolak', $memberStatuses);

        if ($hasMenunggu) {
            // Masih ada yang belum diputuskan
            $app->status = 'diproses';
        } else {
            // Semua sudah diputuskan
            if ($hasDiterima) {
                // Ada setidaknya satu yang diterima
                $app->status = 'diterima';
            } else {
                // Semua ditolak
                $app->status = 'ditolak';
            }
        }
        
        $app->save();
    }
}
