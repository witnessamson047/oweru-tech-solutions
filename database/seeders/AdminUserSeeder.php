<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@oweru.co.tz')],
            [
                'name' => 'Oweru Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'change-me-now')),
                'role' => 'admin',
            ]
        );

        // Example staff account (role 'manager' can access admin too, but is not an owner)
        User::updateOrCreate(
            ['email' => env('MANAGER_EMAIL', 'manager@oweru.co.tz')],
            [
                'name' => 'Oweru Manager',
                'password' => Hash::make(env('MANAGER_PASSWORD', 'change-me-too')),
                'role' => 'manager',
            ]
        );
    }
}
