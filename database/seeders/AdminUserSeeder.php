<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin default
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Default',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now()
            ]
        );

        // HRD default
        User::updateOrCreate(
            ['email' => 'hrd@example.com'],
            [
                'name' => 'HRD Default',
                'password' => Hash::make('password'),
                'role' => 'hrd',
                'email_verified_at' => now()
            ]
        );
    }
}
