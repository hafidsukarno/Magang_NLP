<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index() {
        $users = User::where('role', 'hrd')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create() {
        return view('admin.users.create');
    }

    public function store(Request $r) {
        $r->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $r->name,
            'email' => $r->email,
            'password' => Hash::make($r->password),
            'role' => 'hrd'
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User HRD berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
    ]);

    $user->update([
        'name'  => $request->name,
        'email' => $request->email,
    ]);

    return back()->with('success', 'User berhasil diperbarui');
}

    public function destroy($id) {
        $user = User::findOrFail($id);

        if ($user->role !== 'hrd') {
            abort(403, "Tidak dapat menghapus user selain HRD");
        }

        $user->delete();

        return back()->with('success', 'User HRD dihapus.');
    }
}
