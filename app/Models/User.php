<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids;

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

    public function fullName(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
