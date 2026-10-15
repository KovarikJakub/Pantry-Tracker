<?php

namespace Modules\Identity\Application;

use Illuminate\Contracts\Hashing\Hasher;
use Modules\Identity\Domain\EmailAlreadyRegistered;
use Modules\Identity\Domain\IUserRepository;
use Modules\Identity\Domain\User;

/**
 * Creates new user accounts.
 */
class UserRegistrar
{
    public function __construct(
        private IUserRepository $usersRepository,
        private Hasher $hasher,
    ) {}

    /**
     * @throws EmailAlreadyRegistered
     */
    public function register(string $email, string $password, string $firstName, string $lastName): User
    {
        if ($this->usersRepository->findByEmail($email) !== null) {
            throw new EmailAlreadyRegistered($email);
        }

        return $this->usersRepository->create($email, $this->hasher->make($password), $firstName, $lastName);
    }
}
