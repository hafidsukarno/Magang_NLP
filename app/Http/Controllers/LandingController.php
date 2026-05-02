<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\DepartmentQuota;
use App\Models\Application;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->get();
        return view('landing', compact('departments'));
    }

    public function checkQuota(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $departmentId = $request->department_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $dept = Department::find($departmentId);

        // 1. Get Quota Value (Period-based or Legacy)
        $quotaRecord = DepartmentQuota::where('department_id', $departmentId)
            ->where('period_start', '<=', $startDate)
            ->where('period_end', '>=', $endDate)
            ->first();
        
        $quotaValue = $quotaRecord ? (int)$quotaRecord->quota : (int)($dept->quota ?? 0);

        // 2. Count used slots (Accepted people overlapping the period)
        $acceptedApps = Application::where('department_id', $departmentId)
            ->where('status', 'diterima')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('period_start', '<=', $endDate)
                  ->where('period_end', '>=', $startDate);
            })
            ->with('members')
            ->get();

        $usedPeople = $acceptedApps->sum(function ($a) {
            return $a->type === 'group' ? ($a->members->count() + 1) : 1;
        });

        $availableSlots = max(0, $quotaValue - $usedPeople);

        return response()->json([
            'success' => true,
            'department' => $dept->name,
            'quota' => $quotaValue,
            'used' => $usedPeople,
            'available' => $availableSlots,
            'message' => $availableSlots > 0 
                ? "Masih tersedia $availableSlots slot untuk periode tersebut." 
                : "Maaf, kuota untuk periode tersebut sudah penuh."
        ]);
    }
}
