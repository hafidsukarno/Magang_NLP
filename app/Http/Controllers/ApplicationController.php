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
        $departments = Department::with('periods')->get()->map(function($dept) {
            // Ambil durasi dari period pertama
            $duration = $dept->periods->first()?->duration ?? 5;
            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'duration' => $duration,
            ];
        });
        return view('mahasiswa.create', compact('departments'));
    }

    public function store(Request $r)
    {
        // Function helper untuk hitung durasi dalam bulan
        $calculateMonths = function(\DateTime $start, \DateTime $end) {
            $months = $end->diff($start)->m;
            $years = $end->diff($start)->y;
            return ($years * 12) + $months;
        };

        // Get max duration dari department_periods
        $maxDuration = 5; // default fallback
        if ($r->department_id) {
            $dept = Department::with('periods')->find($r->department_id);
            $maxDuration = $dept?->periods->first()?->duration ?? 5;
        }

        // Validasi periode
        $periodStart = \DateTime::createFromFormat('Y-m-d', $r->period_start);
        $periodEnd = \DateTime::createFromFormat('Y-m-d', $r->period_end);
        
        if ($periodStart && $periodEnd) {
            $applicationMonths = $calculateMonths($periodStart, $periodEnd);
        } else {
            $applicationMonths = 0;
        }

        // ======================
        // VALIDASI FORM
        // ======================
        $msgs = [
            'period_start.required' => 'Tanggal mulai magang wajib diisi.',
            'period_end.required' => 'Tanggal selesai magang wajib diisi.',
            'period_end.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($r->all(), [
            'type' => 'required|in:individual,group',
            'leader_name' => 'required|string|max:255',
            'leader_email' => 'required|email',
            'leader_phone' => 'required|string|max:20',
            'university' => 'required|string|max:255',
            'major' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'duration' => 'nullable|numeric',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'file' => 'required|mimes:pdf|max:10240',
        ], $msgs);

        // Tambah validasi custom: durasi pengajuan tidak boleh melebihi max duration
        $validator->after(function ($validator) use ($applicationMonths, $maxDuration) {
            if ($applicationMonths > $maxDuration) {
                $validator->errors()->add(
                    'period_end',
                    "Durasi pengajuan Anda ({$applicationMonths} bulan) melebihi durasi maksimal ({$maxDuration} bulan)."
                );
            }
        });

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator->errors());
        }

        // Auto-set duration dari department_periods
        $duration = $r->duration;
        if ($r->department_id) {
            $dept = Department::with('periods')->find($r->department_id);
            $duration = $dept?->periods->first()?->duration ?? 5;
        }

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
                'duration' => $duration,
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
        
        // Get student's applications with members
        $applications = Application::where('user_id', $user->id)
            ->with('members')
            ->latest()
            ->paginate(5);

        // Get summary - hitung berdasarkan leader_status dan member status
        $allApps = Application::where('user_id', $user->id)
            ->with('members')
            ->get();

        $summary = [
            'total' => $allApps->count(),
            'total_individual' => 0,
            'total_group' => 0,
            'menunggu' => 0,
            'menunggu_individual' => 0,
            'menunggu_group' => 0,
            'diterima' => 0,
            'diterima_individual' => 0,
            'diterima_group' => 0,
            'diterima_count' => 0, // jumlah orang yang diterima
            'ditolak' => 0,
            'ditolak_count' => 0, // jumlah orang yang ditolak
        ];

        foreach ($allApps as $app) {
            if ($app->type === 'individual') {
                $summary['total_individual']++;
                // Individual: tergantung leader_status
                if ($app->leader_status == 'menunggu') {
                    $summary['menunggu']++;
                    $summary['menunggu_individual']++;
                } elseif ($app->leader_status == 'diterima') {
                    $summary['diterima']++;
                    $summary['diterima_individual']++;
                    $summary['diterima_count']++;
                } elseif ($app->leader_status == 'ditolak') {
                    $summary['ditolak']++;
                    $summary['ditolak_count']++;
                }
            } else {
                // Group
                $summary['total_group']++;
                $allMembersReviewed = $app->members->every(fn($m) => $m->status !== 'menunggu') 
                    && $app->leader_status !== 'menunggu';
                
                if (!$allMembersReviewed) {
                    $summary['menunggu']++;
                    $summary['menunggu_group']++;
                } 
                
                // Count diterima: ketua + anggota yang diterima
                $diterima_people = 0;
                if ($app->leader_status == 'diterima') {
                    $diterima_people++;
                }
                $diterima_people += $app->members->where('status', 'diterima')->count();
                
                if ($diterima_people > 0) {
                    $summary['diterima']++;
                    $summary['diterima_group']++;
                    $summary['diterima_count'] += $diterima_people;
                }
                
                // Count ditolak: ketua (jika ditolak) + anggota yang ditolak
                $ditolak_people = 0;
                if ($app->leader_status == 'ditolak') {
                    $ditolak_people++;
                }
                $ditolak_people += $app->members->where('status', 'ditolak')->count();
                
                if ($ditolak_people > 0) {
                    $summary['ditolak']++;
                    $summary['ditolak_count'] += $ditolak_people;
                }
            }
        }

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

        $allApplications = Application::where('user_id', $user->id)
            ->with('members')
            ->when($search, function($q) use ($search) {
                $q->where('leader_name', 'like', "%$search%")
                  ->orWhere('registration_code', 'like', "%$search%")
                  ->orWhere('university', 'like', "%$search%");
            })
            ->latest()
            ->get();

        // Filter by status menggunakan logic baru
        if ($status && $status !== 'all') {
            $allApplications = $allApplications->filter(function($app) use ($status) {
                if ($app->type === 'individual') {
                    return $app->leader_status === $status;
                } else {
                    // Group: filter berdasarkan apakah ada members dengan status tertentu
                    if ($status === 'menunggu') {
                        return $app->leader_status === 'menunggu' || $app->members->where('status', 'menunggu')->count() > 0;
                    } elseif ($status === 'diterima') {
                        return $app->leader_status === 'diterima' && $app->members->where('status', 'diterima')->count() > 0;
                    } elseif ($status === 'ditolak') {
                        return $app->leader_status === 'ditolak' || $app->members->where('status', 'ditolak')->count() > 0;
                    }
                }
                return true;
            });
        }

        // Manually paginate
        $page = request('page', 1);
        $perPage = 10;
        $applications = new \Illuminate\Pagination\Paginator(
            $allApplications->forPage($page, $perPage)->values(),
            $perPage,
            $page,
            [
                'path' => route('mahasiswa.applications.index'),
                'query' => request()->query(),
            ]
        );

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
