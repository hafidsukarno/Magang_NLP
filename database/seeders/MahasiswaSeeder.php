<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Mahasiswa 1
        User::updateOrCreate(
            ['email' => 'mahasiswa1@example.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'email_verified_at' => now()
            ]
        );
    }
}
