<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index() {
        $hrdUsers      = User::where('role', 'hrd')->orderBy('name')->get();
        $mahasiswaUsers = User::where('role', 'mahasiswa')->orderBy('name')->get();
        return view('admin.users.index', compact('hrdUsers', 'mahasiswaUsers'));
    }

    public function create() {
        return view('admin.users.create');
    }

    public function store(Request $r) {
        $r->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name'     => $r->name,
            'email'    => $r->email,
            'password' => Hash::make($r->password),
            'role'     => 'hrd'
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User HRD berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id) {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['hrd', 'mahasiswa'])) {
            abort(403, 'Tidak dapat menghapus user ini.');
        }

        $user->delete();

        $label = $user->role === 'hrd' ? 'HRD' : 'Mahasiswa';
        return back()->with('success', "User {$label} berhasil dihapus.");
    }
}
