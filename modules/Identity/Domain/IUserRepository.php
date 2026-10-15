<?php

namespace Modules\Identity\Domain;

interface IUserRepository
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(string $email, string $passwordHash, string $firstName, string $lastName): User;
}
