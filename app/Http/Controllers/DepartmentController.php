<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentQuota;
use App\Models\DepartmentPeriod;
use App\Models\DepartmentSkill;
use App\Models\DepartmentMajor;
use Illuminate\Http\Request;
use App\Models\Application;

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
        $request->validate([
            'name' => 'required|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'periods' => 'nullable|array',
            'periods.*' => 'nullable|integer|min:1',
            'majors' => 'nullable|array',
            'majors.*' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|string|max:255',
        ]);

        $department = Department::create($request->only('name', 'quota'));

        // Simpan periode magang (durasi dalam bulan)
        if ($request->filled('periods')) {
            foreach ($request->periods as $index => $weeks) {
                if ($weeks) {
                    DepartmentPeriod::create([
                        'department_id' => $department->id,
                        'duration' => $weeks,
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
        $request->validate([
            'name' => 'nullable|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'periods' => 'nullable|array',
            'periods.*' => 'nullable|integer|min:1',
            'majors' => 'nullable|array',
            'majors.*' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|string|max:255',
        ]);

        // Update basic info
        $department->update($request->only('name', 'quota'));

        // Update periode magang (hapus yang lama, tambah yang baru)
        if ($request->filled('periods')) {
            $department->periods()->delete();
            foreach ($request->periods as $index => $weeks) {
                if ($weeks) {
                    DepartmentPeriod::create([
                        'department_id' => $department->id,
                        'duration' => $weeks,
                        'position' => $index,
                    ]);
                }
            }
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

