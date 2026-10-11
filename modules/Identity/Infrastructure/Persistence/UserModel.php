<?php

namespace Modules\Identity\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Infrastructure\Database\Factories\UserFactory;

/**
 * Eloquent mapping of the "users" table. Used only inside the Infrastructure layer.
 */
class UserModel extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'password_hash',
        'first_name',
        'last_name',
        'is_admin',
        'deactivated',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'deactivated' => 'boolean',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
