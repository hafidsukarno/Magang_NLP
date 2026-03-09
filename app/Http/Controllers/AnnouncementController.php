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
        if ($request->code) {
            $data = Application::with('department')
                ->where('registration_code', $request->code)
                ->first();
        }

        return view('pengumuman.index', compact('data'));
    }
}
