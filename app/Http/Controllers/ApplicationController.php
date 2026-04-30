<?php

namespace App\Http\Controllers;

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

    /**
     * Tampilkan halaman upload surat permohonan
     */
    public function uploadSurat(Request $request)
    {
        $type = $request->query('type');

        // Validasi type
        if (!in_array($type, ['individual', 'group'])) {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Tipe pengajuan tidak valid');
        }

        return view('mahasiswa.upload-surat', compact('type'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type');
        $applicationId = $request->query('id');

        // Validasi type
        if (!in_array($type, ['individual', 'group'])) {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Tipe pengajuan tidak valid');
        }

        $ocrData = [];
        if ($applicationId) {
            $existingApp = Application::with('members')->where('user_id', auth()->id())->find($applicationId);
            if ($existingApp) {
                $ocrData = [
                    'nama'           => $existingApp->leader_name,
                    'university'     => $existingApp->university,
                    'jurusan'        => $existingApp->major,
                    'program_studi'  => $existingApp->program_studi,
                    'major'          => $existingApp->major,
                    'keahlian'       => $existingApp->keahlian,
                    'tanggal_masuk'  => $existingApp->period_start ? $existingApp->period_start->format('Y-m-d') : null,
                    'tanggal_keluar' => $existingApp->period_end ? $existingApp->period_end->format('Y-m-d') : null,
                    'type'           => $existingApp->type,
                    'surat_permohonan_path' => $existingApp->surat_permohonan_path,
                    'members'        => $existingApp->members->map(function($m) {
                        return [
                            'Nama' => $m->name,
                            'NIM'  => $m->nim,
                            'Prodi' => $m->major
                        ];
                    })->toArray(),
                ];
            }
        }

        if (empty($ocrData)) {
            $parseOcrDate = function($dateStr) {
                if (!$dateStr || $dateStr === 'Tidak ditemukan') return null;
                $months = [
                    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
                    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
                    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
                ];
                foreach ($months as $name => $num) {
                    if (stripos($dateStr, $name) !== false) {
                        $parts = explode(' ', trim($dateStr));
                        if (count($parts) >= 3) {
                            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                            $year = $parts[2];
                            return "$year-$num-$day";
                        }
                    }
                }
                return null;
            };

            $ocrData = [
                'nama'           => $request->query('ocr_nama'),
                'university'     => $request->query('ocr_university'),
                'jurusan'        => $request->query('ocr_jurusan'),
                'program_studi'  => $request->query('ocr_program_studi'),
                'major'          => $request->query('ocr_major'),
                'keahlian'       => $request->query('ocr_keahlian'),
                'tanggal_masuk'  => $parseOcrDate($request->query('ocr_tanggal_masuk')),
                'tanggal_keluar' => $parseOcrDate($request->query('ocr_tanggal_keluar')),
                'type'           => $request->query('ocr_type'),
                'surat_permohonan_path' => $request->query('surat_permohonan_path'),
                'members'        => $request->query('ocr_members', []),
            ];
        }

        $departments = Department::get();
        return view('mahasiswa.create', compact('departments', 'type', 'ocrData', 'applicationId'));
    }

    public function prefill(Request $request)
    {
        $type = $request->input('type', 'individual');
        $parseOcrDate = function($dateStr) {
            if (!$dateStr || $dateStr === 'Tidak ditemukan') return null;
            $months = [
                'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
                'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
                'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
            ];
            foreach ($months as $name => $num) {
                if (stripos($dateStr, $name) !== false) {
                    $parts = explode(' ', trim($dateStr));
                    if (count($parts) >= 3) {
                        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                        $year = $parts[2];
                        return "$year-$num-$day";
                    }
                }
            }
            return null;
        };

        do {
            $code = 'PAG-' . date('Y') . '-TEMP-' . strtoupper(Str::random(4));
        } while (Application::where('registration_code', $code)->exists());

        DB::beginTransaction();
        try {
            $app = new Application();
            $app->user_id = auth()->id();
            $app->registration_code = $code;
            $app->type = $type;
            $app->status = 'pending';
            $app->leader_name = $request->input('ocr_nama');
            $app->university = $request->input('ocr_university');
            $app->major = $request->input('ocr_jurusan');
            $app->program_studi = $request->input('ocr_program_studi');
            $app->keahlian = $request->input('ocr_keahlian');
            $app->period_start = $parseOcrDate($request->input('ocr_tanggal_masuk'));
            $app->period_end = $parseOcrDate($request->input('ocr_tanggal_keluar'));
            $app->surat_permohonan_path = $request->input('surat_permohonan_path');
            $app->surat_permohonan_extracted_text = $request->input('ocr_extracted_text');
            $app->file_path = $request->input('surat_permohonan_path');
            $app->save();

            if ($type === 'group' && $request->has('ocr_members')) {
                foreach ($request->input('ocr_members') as $m) {
                    $member = new ApplicationMember();
                    $member->application_id = $app->id;
                    $member->name = $m['Nama'] ?? '-';
                    $member->nim = $m['NIM'] ?? '-';
                    $member->major = $m['Prodi'] ?? '-';
                    $member->status = 'menunggu';
                    $member->save();
                }
            }
            DB::commit();
            return redirect()->route('apply.form', ['type' => $type, 'id' => $app->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Prefill Error: " . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan draf pengajuan.');
        }
    }

    public function store(Request $r)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($r->all(), [
            'type' => 'required|in:individual,group',
            'leader_name' => 'required|string|max:255',
            'leader_nim' => 'required|string|max:30',
            'leader_email' => 'required|email',
            'leader_phone' => 'required|string|max:20',
            'university' => 'required|string|max:255',
            'major' => 'required|string|max:255',
            'keahlian' => 'nullable|string',
            'program_studi' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator->errors());
        }

        $departmentId = $r->department_id ?: null;
        $periodStart = $r->period_start;
        $periodEnd = $r->period_end;
        $neededPeople = 1;
        if ($r->type === 'group' && $r->members && is_array($r->members)) {
            $neededPeople = 1 + count($r->members);
        }

        if ($departmentId) {
            $quotaRecord = DepartmentQuota::where('department_id', $departmentId)
                ->where('period_start', '<=', $periodStart)
                ->where('period_end', '>=', $periodEnd)
                ->first();
            $quotaValue = $quotaRecord ? (int)$quotaRecord->quota : (Department::find($departmentId)->quota ?? null);

            if ($quotaValue !== null) {
                $acceptedPeople = Application::where('department_id', $departmentId)
                    ->where('status', 'diterima')
                    ->where(function ($q) use ($periodStart, $periodEnd) {
                        $q->whereBetween('period_start', [$periodStart, $periodEnd])
                          ->orWhereBetween('period_end', [$periodStart, $periodEnd])
                          ->orWhere(function($qq) use ($periodStart, $periodEnd) {
                              $qq->where('period_start', '<=', $periodStart)
                                 ->where('period_end', '>=', $periodEnd);
                          });
                    })->get()->sum(fn($a) => $a->type === 'group' ? ($a->members->count() + 1) : 1);

                if (($acceptedPeople + $neededPeople) > $quotaValue) {
                    return back()->withInput()->withErrors(['quota' => "Kuota tidak mencukupi."]);
                }
            }
        }

        DB::beginTransaction();
        try {
            if ($r->application_id) {
                $app = Application::findOrFail($r->application_id);
                if (strpos($app->registration_code, 'TEMP') !== false) {
                    do {
                        $code = 'PAG-' . date('Y') . '-' . strtoupper(Str::random(6));
                    } while (Application::where('registration_code', $code)->exists());
                    $app->registration_code = $code;
                }
            } else {
                do {
                    $code = 'PAG-' . date('Y') . '-' . strtoupper(Str::random(6));
                } while (Application::where('registration_code', $code)->exists());
                $app = new Application();
                $app->registration_code = $code;
                $app->user_id = auth()->id();
            }

            $app->type = $r->type;
            $app->leader_name = $r->leader_name;
            $app->leader_nim = $r->leader_nim;
            $app->leader_email = $r->leader_email;
            $app->leader_phone = $r->leader_phone;
            $app->university = $r->university;
            $app->major = $r->major;
            $app->keahlian = $r->keahlian;
            $app->program_studi = $r->program_studi;
            $app->department_id = $departmentId;
            $app->period_start = $periodStart;
            $app->period_end = $periodEnd;
            $app->status = 'menunggu';
            $app->leader_status = 'menunggu';

            if ($r->hasFile('file')) {
                $app->file_path = $r->file('file')->store('magang_uploads', 'public');
            }

            $app->save();

            if ($r->type === 'group' && $r->members) {
                ApplicationMember::where('application_id', $app->id)->delete();
                foreach ($r->members as $m) {
                    if (empty($m['name'])) continue;
                    $member = new ApplicationMember();
                    $member->application_id = $app->id;
                    $member->name = $m['name'];
                    $member->nim = $m['nim'] ?? '-';
                    $member->major = $m['major'] ?? '-';
                    $member->status = 'menunggu';
                    $member->save();
                }
            }
            DB::commit();
            return redirect()->route('mahasiswa.dashboard')->with('success', 'Pengajuan berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Store Error: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Gagal menyimpan pengajuan: ' . $e->getMessage()]);
        }
    }

    public function mahasiswaDashboard()
    {
        $user = auth()->user();
        $applications = Application::where('user_id', $user->id)->with('members')->latest()->paginate(5);
        $allApps = Application::where('user_id', $user->id)->with('members')->get();
        $summary = [
            'total' => $allApps->count(),
            'total_individual' => 0, 'total_group' => 0,
            'menunggu' => 0, 'menunggu_individual' => 0, 'menunggu_group' => 0,
            'diterima' => 0, 'diterima_individual' => 0, 'diterima_group' => 0, 'diterima_count' => 0,
            'ditolak' => 0, 'ditolak_count' => 0,
        ];
        foreach ($allApps as $app) {
            if ($app->type === 'individual') {
                $summary['total_individual']++;
                if ($app->leader_status == 'menunggu') { $summary['menunggu']++; $summary['menunggu_individual']++; }
                elseif ($app->leader_status == 'diterima') { $summary['diterima']++; $summary['diterima_individual']++; $summary['diterima_count']++; }
                elseif ($app->leader_status == 'ditolak') { $summary['ditolak']++; $summary['ditolak_count']++; }
            } else {
                $summary['total_group']++;
                $allMembersReviewed = $app->members->every(fn($m) => $m->status !== 'menunggu') && $app->leader_status !== 'menunggu';
                if (!$allMembersReviewed) { $summary['menunggu']++; $summary['menunggu_group']++; }
                $diterima_people = ($app->leader_status == 'diterima' ? 1 : 0) + $app->members->where('status', 'diterima')->count();
                if ($diterima_people > 0) { $summary['diterima']++; $summary['diterima_group']++; $summary['diterima_count'] += $diterima_people; }
                $ditolak_people = ($app->leader_status == 'ditolak' ? 1 : 0) + $app->members->where('status', 'ditolak')->count();
                if ($ditolak_people > 0) { $summary['ditolak']++; $summary['ditolak_count'] += $ditolak_people; }
            }
        }
        return view('mahasiswa.dashboard', compact('applications', 'summary'));
    }

    public function mahasiswaApplications()
    {
        $user = auth()->user();
        $search = request('search');
        $status = request('status');
        $allApplications = Application::where('user_id', $user->id)->with('members')
            ->when($search, function($q) use ($search) {
                $q->where('leader_name', 'like', "%$search%")->orWhere('registration_code', 'like', "%$search%")->orWhere('university', 'like', "%$search%");
            })->latest()->get();
        if ($status && $status !== 'all') {
            $allApplications = $allApplications->filter(function($app) use ($status) {
                if ($app->type === 'individual') return $app->leader_status === $status;
                if ($status === 'menunggu') return $app->leader_status === 'menunggu' || $app->members->where('status', 'menunggu')->count() > 0;
                if ($status === 'diterima') return $app->leader_status === 'diterima' && $app->members->where('status', 'diterima')->count() > 0;
                if ($status === 'ditolak') return $app->leader_status === 'ditolak' || $app->members->where('status', 'ditolak')->count() > 0;
                return true;
            });
        }
        $page = request('page', 1); $perPage = 10;
        $applications = new \Illuminate\Pagination\Paginator($allApplications->forPage($page, $perPage)->values(), $perPage, $page, ['path' => route('mahasiswa.applications.index'), 'query' => request()->query()]);
        return view('mahasiswa.applications', compact('applications'));
    }

    public function mahasiswaShow($id)
    {
        $app = Application::with('members', 'department')->where('user_id', auth()->id())->findOrFail($id);
        return view('mahasiswa.show', compact('app'));
    }
}
