<?php

namespace Modules\Identity\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Infrastructure\Persistence\UserModel;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        UserModel::query()->updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'password_hash' => Hash::make('password'),
                'first_name' => 'Normal',
                'last_name' => 'User',
                'is_admin' => false,
            ],
        );
        UserModel::query()->updateOrCreate(
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
