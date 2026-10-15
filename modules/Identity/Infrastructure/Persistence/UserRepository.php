<?php

namespace Modules\Identity\Infrastructure\Persistence;

use Modules\Identity\Domain\IUserRepository;
use Modules\Identity\Domain\User;

class UserRepository implements IUserRepository
{
    public function findById(string $id): ?User
    {
        $model = UserModel::query()->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::query()->where('email', $email)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function create(string $email, string $passwordHash, string $firstName, string $lastName): User
    {
        $model = UserModel::query()->create([
            'email' => $email,
            'password_hash' => $passwordHash,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        return $this->toDomain($model->refresh());
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            id: $model->id,
            email: $model->email,
            passwordHash: $model->password_hash,
            firstName: $model->first_name,
            lastName: $model->last_name,
            isAdmin: $model->is_admin,
            isDeactivated: $model->deactivated,
        );
    }
}
