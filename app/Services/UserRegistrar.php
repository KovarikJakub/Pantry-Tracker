<?php

namespace App\Services;

use App\Exceptions\EmailAlreadyRegistered;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;

/**
 * Creates new user accounts.
 */
class UserRegistrar
{
    public function __construct(private Hasher $hasher) {}

    /**
     * @throws EmailAlreadyRegistered
     */
    public function register(string $email, string $password, string $firstName, string $lastName): User
    {
        if (User::query()->where('email', $email)->exists()) {
            throw new EmailAlreadyRegistered($email);
        }

        return User::query()->create([
            'email' => $email,
            'password_hash' => $this->hasher->make($password),
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }
}
