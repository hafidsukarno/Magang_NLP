<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $data = null;

        // Jika user memasukkan kode
        if ($request->nim) {
            $data = Application::with('department')
                ->where('leader_nim', $request->nim)
                ->first();
        }

        return view('pengumuman.index', compact('data'));
    }
}
