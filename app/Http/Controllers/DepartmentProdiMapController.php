<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentProdiMap;
use Illuminate\Http\Request;

class DepartmentProdiMapController extends Controller
{
    public function index(Department $department)
    {
        return view('hrd.departments.prodi-maps.index', [
            'department' => $department,
            'maps' => $department->prodiMaps
        ]);
    }

    public function store(Request $request, Department $department)
    {
        $request->validate([
            'prodi_keyword' => 'required|string|max:255',
        ]);

        $department->prodiMaps()->create([
            'prodi_keyword' => strtolower($request->prodi_keyword)
        ]);

        return back()->with('success', 'Prodi berhasil ditambahkan.');
    }

    public function destroy(Department $department, DepartmentProdiMap $map)
    {
        $map->delete();
        return back()->with('success', 'Mapping dihapus.');
    }
}
