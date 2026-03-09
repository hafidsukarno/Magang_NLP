<?php

use Illuminate\Support\Facades\Route;

// Controllers sistem magang
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\HRDController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentProdiMapController;

// Controllers auth
use App\Http\Controllers\ProfileController;

// Controller Admin
use App\Http\Controllers\AdminUserController;

//   PUBLIC ROUTES
Route::get('/', fn() => redirect()->route('login'));

Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('pengumuman.index');


// Dashboard → redirect sesuai role
Route::get('/dashboard', function () {
    $user = auth()->user()->fresh();

    if ($user->isAdmin()) {
        return redirect()->route('admin.users.index');
    }
    
    if ($user->isHrd()) {
        return redirect()->route('hrd.dashboard');
    }
    
    // Default: mahasiswa
    return redirect()->route('mahasiswa.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//   MAHASISWA ROUTES (Siswa yang mendaftar)
Route::middleware(['auth', 'role:mahasiswa'])
    ->prefix('mahasiswa')
    ->group(function () {
        
        Route::get('/dashboard', [ApplicationController::class, 'mahasiswaDashboard'])
            ->name('mahasiswa.dashboard');

        Route::get('/pengajuan/create', [ApplicationController::class, 'create'])
            ->name('apply.form');

        Route::post('/pengajuan', [ApplicationController::class, 'store'])
            ->name('apply.store');

        Route::get('/pengajuan', [ApplicationController::class, 'mahasiswaApplications'])
            ->name('mahasiswa.applications.index');

        Route::get('/pengajuan/{id}', [ApplicationController::class, 'mahasiswaShow'])
            ->name('mahasiswa.applications.show');
    });

//   ADMIN ROUTES
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('admin.users.index');

        Route::get('/users/create', [AdminUserController::class, 'create'])
            ->name('admin.users.create');

        Route::post('/users', [AdminUserController::class, 'store'])
            ->name('admin.users.store');

        Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', AdminUserController::class)
        ->names('admin.users');
});


        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])
            ->name('admin.users.destroy');
    });

//   HRD ONLY ROUTES
Route::middleware(['auth', 'role:hrd'])->group(function () {

    Route::get('/hrd/dashboard', [HRDController::class, 'index'])->name('hrd.dashboard');

    Route::get('/hrd/application/{id}', [HRDController::class, 'show'])
        ->name('hrd.application.show');

    Route::post('/hrd/application/{id}/update', [HRDController::class, 'update'])
        ->name('hrd.application.update');

    Route::get('/hrd/applications', [HRDController::class, 'applications'])
        ->name('hrd.applications.index');

    Route::get('/hrd/application/{id}/file', [HRDController::class, 'viewPdf'])
        ->name('hrd.application.viewPdf');

    // Departments CRUD
    Route::get('/hrd/departments', [DepartmentController::class, 'index'])
        ->name('departments.index');

    Route::post('/hrd/departments', [DepartmentController::class, 'store'])
        ->name('departments.store');

    Route::patch('/hrd/departments/{department}/update', [DepartmentController::class, 'update'])
        ->name('departments.update');

    Route::patch('/hrd/departments/{department}/update-period', [DepartmentController::class, 'updatePeriod'])
        ->name('departments.updatePeriod');

    // Lihat mahasiswa diterima per departemen
    Route::get(
        '/hrd/departments/{department}/accepted',
        [DepartmentController::class, 'accepted']
    )->name('departments.accepted');
    
    // Department – Prodi Maps
    Route::prefix('/hrd/departments/{department}/prodi-maps')->group(function () {
        Route::get('/', [DepartmentProdiMapController::class, 'index'])->name('prodi-maps.index');
        Route::post('/', [DepartmentProdiMapController::class, 'store'])->name('prodi-maps.store');
        Route::delete('/{map}', [DepartmentProdiMapController::class, 'destroy'])->name('prodi-maps.destroy');
    });
});

// PROFILE ROUTES - Untuk semua user yang login (admin & hrd)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
