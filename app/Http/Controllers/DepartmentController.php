<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentQuota;
use App\Models\DepartmentSkill;
use App\Models\DepartmentMajor;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        // eager load quotas so blade bisa akses ->quotas etc
        $query = Department::with('quotas', 'skills', 'majors');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('majors', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('skills', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $departments = $query->orderBy('name')->paginate(10);

        return view('hrd.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        // DEBUG: Log semua request data
        \Log::info('🔍 STORE - Semua request data:', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'majors' => 'nullable|array',
            'majors.*' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|string|max:255',
        ]);

        $department = Department::create($request->only('name', 'quota'));
        \Log::info('✅ Department created:', $department->only('id', 'name'));

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
        
        $request->validate([
            'name' => 'nullable|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'majors' => 'nullable|array',
            'majors.*' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|string|max:255',
        ]);

        try {
            // Update basic info
            $department->update($request->only('name', 'quota'));
            \Log::info('✅ Department updated:', ['id' => $department->id, 'name' => $department->name]);

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

