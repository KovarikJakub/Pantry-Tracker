<?php

namespace Modules\Identity\Domain;

interface IUserRepository
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;
}
