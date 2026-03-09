<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentApiController extends Controller
{
    public function show(Department $department)
    {
        $department->load('periods', 'majors', 'skills');
        
        return response()->json([
            'id' => $department->id,
            'name' => $department->name,
            'quota' => $department->quota,
            'periods' => $department->periods->map(fn($p) => ['id' => $p->id, 'weeks' => $p->weeks])->values(),
            'majors' => $department->majors->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values(),
            'skills' => $department->skills->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values(),
        ]);
    }
}
