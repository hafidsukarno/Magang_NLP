<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentQuota;
use App\Models\DepartmentPeriod;
use App\Models\DepartmentSkill;
use App\Models\DepartmentMajor;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        // eager load quotas so blade bisa akses ->quotas etc
        $query = Department::with('quotas', 'periods', 'skills', 'majors');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $departments = $query->orderBy('name')->paginate(10);

        return view('hrd.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        // DEBUG: Log semua request data
        \Log::info('🔍 STORE - Semua request data:', $request->all());
        \Log::info('🔍 STORE - period_start:', ['value' => $request->get('period_start'), 'filled' => $request->filled('period_start'), 'type' => gettype($request->get('period_start'))]);
        \Log::info('🔍 STORE - period_end:', ['value' => $request->get('period_end'), 'filled' => $request->filled('period_end'), 'type' => gettype($request->get('period_end'))]);
        \Log::info('🔍 STORE - periods:', ['value' => $request->get('periods'), 'filled' => $request->filled('periods')]);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'periods' => 'required|array|min:1',
            'periods.*' => 'required|integer|min:1',
            'majors' => 'nullable|array',
            'majors.*' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|string|max:255',
        ]);

        // Jika hanya salah satu tanggal diisi, error
        if (($request->filled('period_start') && !$request->filled('period_end')) || 
            (!$request->filled('period_start') && $request->filled('period_end'))) {
            return back()->withErrors([
                'period_end' => 'Mohon isi KEDUA tanggal (mulai dan berakhir) atau kosongkan keduanya.'
            ])->withInput();
        }
        
        // Validasi durasi sesuai dengan periode - hanya jika KEDUA dates diisi
        if ($request->filled('period_start') && $request->filled('period_end')) {
            $period = $request->periods[0] ?? 0;
            if ($period > 0) {
                try {
                    $startDate = \Carbon\Carbon::parse($request->period_start);
                    $endDate = \Carbon\Carbon::parse($request->period_end);
                    
                    // Hitung hari dan konversi ke bulan (1 bulan = 30.44 hari rata-rata)
                    $daysInPeriod = $endDate->diffInDays($startDate);
                    $monthsFromDays = $daysInPeriod / 30.44;
                    
                    \Log::info('📅 Duration validation:', [
                        'period_requested' => $period,
                        'start' => $request->period_start,
                        'end' => $request->period_end,
                        'days' => $daysInPeriod,
                        'months_from_days' => $monthsFromDays
                    ]);
                    
                    // Toleransi ±0.5 bulan (±15 hari) - lebih fleksibel
                    if (abs($monthsFromDays - $period) > 0.5) {
                        return back()->withErrors([
                            'period_end' => "Durasi tidak sesuai: {$daysInPeriod} hari = " . number_format($monthsFromDays, 2) . " bln vs durasi {$period} bln. Toleransi ±15 hari."
                        ])->withInput();
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(['period_end' => 'Format tanggal tidak valid.'])->withInput();
                }
            }
        }

        $department = Department::create($request->only('name', 'quota'));
        \Log::info('✅ Department created:', $department->only('id', 'name'));

        // Simpan periode magang (durasi dalam bulan)
        if ($request->filled('periods')) {
            foreach ($request->periods as $index => $weeks) {
                if ($weeks) {
                    \Log::info('📝 Creating DepartmentPeriod:', [
                        'department_id' => $department->id,
                        'duration' => (int)$weeks,
                        'period_start' => $request->period_start,
                        'period_end' => $request->period_end,
                        'position' => $index,
                    ]);
                    
                    DepartmentPeriod::create([
                        'department_id' => $department->id,
                        'duration' => (int)$weeks,
                        'period_start' => $request->period_start ?? null,
                        'period_end' => $request->period_end ?? null,
                        'position' => $index,
                    ]);
                }
            }
        }

        // Simpan jurusan yang relevan
        if ($request->filled('majors')) {
            foreach ($request->majors as $index => $major) {
                if ($major) {
                    DepartmentMajor::create([
                        'department_id' => $department->id,
                        'name' => $major,
                        'position' => $index,
                    ]);
                }
            }
        }

        // Simpan keahlian
        if ($request->filled('skills')) {
            foreach ($request->skills as $index => $skill) {
                if ($skill) {
                    DepartmentSkill::create([
                        'department_id' => $department->id,
                        'name' => $skill,
                        'position' => $index,
                    ]);
                }
            }
        }

        // Jika periode diberikan, buat quota record.
        // Jika quota per-periode tidak diisi -> fallback ke department->quota (legacy) atau 0.
        if ($request->filled('period_start') && $request->filled('period_end')) {
            $quotaValue = $request->filled('quota') ? (int)$request->quota : (int)($department->quota ?? 0);

            DepartmentQuota::create([
                'department_id' => $department->id,
                'quota' => $quotaValue,
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
            ]);
        }

        return back()->with('success', 'Departemen berhasil ditambahkan.');
    }

    /**
     * Update department basic info or create quota entry if period provided.
     * This keeps backward compatibility while enabling period-based quotas.
     */
    public function update(Request $request, Department $department)
    {
        // DEBUG: Log semua request data
        \Log::info('🔍 UPDATE - Semua request data:', $request->all());
        \Log::info('🔍 UPDATE - period_start:', ['value' => $request->get('period_start'), 'filled' => $request->filled('period_start')]);
        \Log::info('🔍 UPDATE - period_end:', ['value' => $request->get('period_end'), 'filled' => $request->filled('period_end')]);
        
        $request->validate([
            'name' => 'nullable|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'periods' => 'required|array|min:1',
            'periods.*' => 'required|integer|min:1',
            'majors' => 'nullable|array',
            'majors.*' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|string|max:255',
        ]);

        // Validasi durasi sesuai dengan periode
        if ($request->filled('periods') && $request->filled('period_start') && $request->filled('period_end')) {
            $period = $request->periods[0] ?? null;
            if ($period) {
                try {
                    $startDate = \Carbon\Carbon::parse($request->period_start);
                    $endDate = \Carbon\Carbon::parse($request->period_end);
                    
                    // Hitung hari dan konversi ke bulan (1 bulan = 30.44 hari rata-rata)
                    $daysInPeriod = $endDate->diffInDays($startDate);
                    $monthsFromDays = $daysInPeriod / 30.44;
                    
                    // Toleransi ±0.5 bulan (±15 hari) - lebih fleksibel
                    if (abs($monthsFromDays - $period) > 0.5) {
                        return back()->withErrors([
                            'period_end' => "Durasi tidak sesuai: {$daysInPeriod} hari = " . number_format($monthsFromDays, 2) . " bln vs durasi {$period} bln. Toleransi ±15 hari."
                        ])->withInput();
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(['period_end' => 'Format tanggal tidak valid.'])->withInput();
                }
            }
        }

        try {
            // Update basic info
            $department->update($request->only('name', 'quota'));
            \Log::info('✅ Department updated:', ['id' => $department->id, 'name' => $department->name]);

            // Update periode magang (hapus yang lama, tambah yang baru)
            if ($request->filled('periods')) {
                \Log::info('📝 Deleting old periods for department:', ['department_id' => $department->id]);
                $department->periods()->delete();
                
                foreach ($request->periods as $index => $weeks) {
                    if ($weeks) {
                        \Log::info('📝 Creating new DepartmentPeriod:', [
                            'department_id' => $department->id,
                            'duration' => $weeks,
                            'period_start' => $request->get('period_start'),
                            'period_end' => $request->get('period_end'),
                            'position' => $index,
                        ]);
                        
                        DepartmentPeriod::create([
                            'department_id' => $department->id,
                            'duration' => (int)$weeks,
                            'period_start' => $request->period_start ?? null,
                            'period_end' => $request->period_end ?? null,
                            'position' => $index,
                        ]);
                    }
                }
            } else {
                \Log::warning('No periods provided in request', ['department_id' => $department->id]);
            }

            // Update jurusan yang relevan
            if ($request->filled('majors')) {
                $department->majors()->delete();
                foreach ($request->majors as $index => $major) {
                    if ($major) {
                        DepartmentMajor::create([
                            'department_id' => $department->id,
                            'name' => $major,
                            'position' => $index,
                        ]);
                    }
                }
            }

            // Update keahlian
            if ($request->filled('skills')) {
                $department->skills()->delete();
                foreach ($request->skills as $index => $skill) {
                    if ($skill) {
                        DepartmentSkill::create([
                            'department_id' => $department->id,
                            'name' => $skill,
                            'position' => $index,
                        ]);
                    }
                }
            }

            return back()->with('success', 'Data departemen berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Department update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Create a department quota record (period-based) - quota optional (fallback to legacy)
     */
    public function updatePeriod(Request $request, Department $department)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'quota' => 'nullable|integer|min:0',
        ]);

        $quotaValue = $request->filled('quota') ? (int)$request->quota : (int)($department->quota ?? 0);

        DepartmentQuota::create([
            'department_id' => $department->id,
            'quota' => $quotaValue,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
        ]);

        return back()->with('success', 'Periode magang berhasil ditambahkan (quota terdaftar).');
    }

    public function accepted(Department $department)
    {
        $applications = Application::with('department')
            ->where('department_id', $department->id)
            ->where(function ($q) {
                $q->where('status', 'diterima')
                  ->orWhere('status', 'selesai');
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('hrd.departments.accepted', compact('department', 'applications'));
    }

    /**
     * Delete a department and all related data
     */
    public function destroy(Department $department)
    {
        try {
            // Delete related data
            $department->periods()->delete();
            $department->majors()->delete();
            $department->skills()->delete();
            $department->quotas()->delete();

            // Delete department
            $department->delete();

            return back()->with('success', 'Departemen berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Gagal menghapus departemen: ' . $e->getMessage()]);
        }
    }
}

