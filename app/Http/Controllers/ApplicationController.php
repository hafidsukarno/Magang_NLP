<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    private function parseOcrDate($dateStr)
    {
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
    }

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
                    'leader_nim'     => $existingApp->leader_nim,
                    'leader_email'   => $existingApp->leader_email,
                    'leader_phone'   => $existingApp->leader_phone,
                    'department_id'  => $existingApp->department_id,
                    'surat_permohonan_path' => $existingApp->surat_permohonan_path,
                    'members'        => $existingApp->members->map(function($m) {
                        return [
                            'name'          => $m->name,
                            'nim'           => $m->nim,
                            'email'         => $m->email,
                            'phone'         => $m->phone,
                        ];
                    })->toArray(),
                ];
            }
        }

        if (empty($ocrData) && session()->has('ocr_data')) {
            $sessionData = session('ocr_data');
            $members = $sessionData['ocr_members'] ?? [];
            $normalizedMembers = [];
            
            foreach (array_slice($members, 1) as $m) {
                $normalizedMembers[] = [
                    'name'          => $m['Nama'] ?? '',
                    'nim'           => $m['NIM'] ?? '',
                    'email'         => '',
                    'phone'         => ''
                ];
            }

            $ocrData = [
                'nama'           => $sessionData['ocr_nama'] ?? '',
                'university'     => $sessionData['ocr_university'] ?? '',
                'jurusan'        => $sessionData['ocr_jurusan'] ?? '',
                'program_studi'  => $sessionData['ocr_program_studi'] ?? '',
                'major'          => $sessionData['ocr_major'] ?? '',
                'keahlian'       => $sessionData['ocr_keahlian'] ?? '',
                'tanggal_masuk'  => $this->parseOcrDate($sessionData['ocr_tanggal_masuk'] ?? ''),
                'tanggal_keluar' => $this->parseOcrDate($sessionData['ocr_tanggal_keluar'] ?? ''),
                'type'           => $sessionData['type'] ?? $type,
                'surat_permohonan_path' => $sessionData['surat_permohonan_path'] ?? '',
                'extracted_text' => $sessionData['ocr_extracted_text'] ?? '',
                'members'        => $normalizedMembers,
                'leader_nim'     => $members[0]['NIM'] ?? '',
            ];
        }

        if (empty($ocrData)) {
            $ocrData = [
                'nama'           => $request->query('ocr_nama'),
                'university'     => $request->query('ocr_university'),
                'jurusan'        => $request->query('ocr_jurusan'),
                'program_studi'  => $request->query('ocr_program_studi'),
                'major'          => $request->query('ocr_major'),
                'keahlian'       => $request->query('ocr_keahlian'),
                'tanggal_masuk'  => $this->parseOcrDate($request->query('ocr_tanggal_masuk')),
                'tanggal_keluar' => $this->parseOcrDate($request->query('ocr_tanggal_keluar')),
                'type'           => $request->query('ocr_type'),
                'surat_permohonan_path' => $request->query('surat_permohonan_path'),
                'members'        => [], // Already handled or empty
            ];
        }

        $departments = Department::get();
        return view('mahasiswa.create', compact('departments', 'type', 'ocrData', 'applicationId'));
    }

    public function prefill(Request $request)
    {
        $type = $request->input('type', 'individual');
        session(['ocr_data' => $request->all()]);
        return redirect()->route('apply.form', ['type' => $type]);
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
            'department_id' => 'required|exists:departments,id',
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

        /* 
        if ($departmentId) {
            $dept = Department::find($departmentId);
            $quotaValue = $dept ? (int)($dept->quota ?? 0) : 0;

            if ($quotaValue > 0) {
                $acceptedPeople = Application::where('department_id', $departmentId)
                    ->where('status', 'diterima')
                    ->where(function ($q) use ($periodStart, $periodEnd) {
                        $q->where('period_start', '<=', $periodEnd)
                          ->where('period_end', '>=', $periodStart);
                    })->get()->sum(fn($a) => $a->type === 'group' ? ($a->members->count() + 1) : 1);

                if (($acceptedPeople + $neededPeople) > $quotaValue) {
                    return back()->withInput()->withErrors(['quota' => "Kuota tidak mencukupi."]);
                }
            }
        }
        */

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

            // Save OCR Data from Previous Step (Surat Permohonan)
            if ($r->surat_permohonan_path) $app->surat_permohonan_path = $r->surat_permohonan_path;
            if ($r->ocr_extracted_text) $app->surat_permohonan_extracted_text = $r->ocr_extracted_text;

            // Save OCR Raw Data from Report (Step in this form)
            if ($r->surat_laporan_path) $app->surat_laporan_path = $r->surat_laporan_path;
            if ($r->surat_laporan_raw_text) $app->surat_laporan_raw_text = $r->surat_laporan_raw_text;
            if ($r->surat_laporan_extracted_text) $app->surat_laporan_extracted_text = $r->surat_laporan_extracted_text;
            if ($r->keahlian_raw_text) $app->keahlian_raw_text = $r->keahlian_raw_text;



            $app->save();

            if ($r->type === 'group' && $r->members) {
                ApplicationMember::where('application_id', $app->id)->delete();
                foreach ($r->members as $m) {
                    if (empty($m['name'])) continue;
                    $member = new ApplicationMember();
                    $member->application_id = $app->id;
                    $member->name = $m['name'];
                    $member->nim = $m['nim'] ?? '-';
                    $member->email = $m['email'] ?? '-';
                    $member->phone = $m['phone'] ?? '-';
                    $member->status = 'menunggu';
                    $member->save();
                }
            }
            $isEdit = $r->filled('application_id');
            session()->forget('ocr_data');
            DB::commit();
            
            $msg = $isEdit ? 'Pengajuan berhasil diperbarui!' : 'Pengajuan berhasil dikirim!';
            return redirect()->route('mahasiswa.dashboard')->with('success', $msg);
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

    public function edit($id)
    {
        $app = Application::where('user_id', auth()->id())->findOrFail($id);

        // Cek apakah boleh diedit
        if ($app->status !== 'menunggu' && $app->status !== 'pending') {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Pengajuan yang sudah diproses tidak dapat diubah.');
        }

        return redirect()->route('apply.form', ['type' => $app->type, 'id' => $app->id]);
    }

    public function update(Request $r, $id)
    {
        // Logika update mirip store, kita bisa arahkan store untuk handle update jika ada ID
        return $this->store($r);
    }

    public function destroy($id)
    {
        $app = Application::where('user_id', auth()->id())->findOrFail($id);

        // Cek apakah boleh dihapus
        if ($app->status !== 'menunggu' && $app->status !== 'pending') {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Pengajuan yang sudah diproses tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            ApplicationMember::where('application_id', $app->id)->delete();
            $app->delete();
            DB::commit();
            return redirect()->route('mahasiswa.dashboard')->with('success', 'Pengajuan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pengajuan.');
        }
    }
}
