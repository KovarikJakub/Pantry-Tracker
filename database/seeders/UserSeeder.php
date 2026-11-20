<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the demo users, all with the password "password". Safe to re-run.
 */
class UserSeeder extends Seeder
{
    /**
     * Columns: email, first name, last name, is admin, is deactivated.
     *
     * @var list<array{string, string, string, bool, bool}>
     */
    private const USERS = [
        ['admin@example.com', 'Admin', 'User', true, false],
        ['owner@example.com', 'Jana', 'Nováková', false, false],
        ['manager@example.com', 'Petr', 'Novák', false, false],
        ['user@example.com', 'Normal', 'User', false, false],
        ['free@example.com', 'Eva', 'Svobodová', false, false],
        ['blocked@example.com', 'Karel', 'Blocked', false, true],
    ];

    public function run(): void
    {
        $passwordHash = Hash::make('password');

        foreach (self::USERS as [$email, $firstName, $lastName, $isAdmin, $isDeactivated]) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'password_hash' => $passwordHash,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'is_admin' => $isAdmin,
                    'deactivated' => $isDeactivated,
                ],
            );
        }
    }
}
