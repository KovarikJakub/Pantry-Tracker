<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HouseholdMember extends Model
{
    use HasUuids;

    /** Created the household. Has manager rights and is the only one who can demote or remove managers. */
    public const ROLE_OWNER = 'owner';

    /** Manages appliances, the catalogue and regular members. */
    public const ROLE_MANAGER = 'manager';

    public const ROLE_MEMBER = 'member';

    /** @var list<string> roles with manager rights */
    public const MANAGING_ROLES = [self::ROLE_OWNER, self::ROLE_MANAGER];

    protected $fillable = [
        'household_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function hasManagingRole(): bool
    {
        return in_array($this->role, self::MANAGING_ROLES, true);
    }

    /**
     * Regular members can be removed by any manager, managers only by the owner. The owner cannot be
     * removed and nobody can remove themselves.
     */
    public function canRemove(self $target): bool
    {
        if ($this->is($target)) {
            return false;
        }

        return match ($target->role) {
            self::ROLE_MEMBER => $this->hasManagingRole(),
            self::ROLE_MANAGER => $this->role === self::ROLE_OWNER,
            default => false,
        };
    }

    public function canPromote(self $target): bool
    {
        return ! $this->is($target) && $target->role === self::ROLE_MEMBER && $this->hasManagingRole();
    }

    public function canDemote(self $target): bool
    {
        return ! $this->is($target) && $target->role === self::ROLE_MANAGER && $this->role === self::ROLE_OWNER;
    }
}
