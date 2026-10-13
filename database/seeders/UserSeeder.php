<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'password_hash' => Hash::make('password'),
                'first_name' => 'Normal',
                'last_name' => 'User',
                'is_admin' => false,
            ],
        );
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'password_hash' => Hash::make('password'),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'is_admin' => true,
            ],
        );
    }
}
