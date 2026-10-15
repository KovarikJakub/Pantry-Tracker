<?php

namespace Modules\Identity\Domain;

/**
 * Framework-independent representation of a user account.
 */
final readonly class User
{
    public function __construct(
        public string $id,
        public string $email,
        public string $passwordHash,
        public string $firstName,
        public string $lastName,
        public bool $isAdmin,
        public bool $isDeactivated,
    ) {}

    public function fullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
