<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<HouseholdMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function isManagedBy(string $userId): bool
    {
        return $this->members()->where('user_id', $userId)->where('role', 'manager')->exists();
    }
}
