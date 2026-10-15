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

    protected function casts(): array
    {
        return [
            'has_custom_categories' => 'boolean',
        ];
    }

    /**
     * @return HasMany<HouseholdMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(HouseholdMember::class);
    }

    /**
     * @return HasMany<Appliance, $this>
     */
    public function appliances(): HasMany
    {
        return $this->hasMany(Appliance::class);
    }

    /**
     * The household's own category tree. Empty until it copies the default tree.
     *
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function isManagedBy(string $userId): bool
    {
        return $this->members()->where('user_id', $userId)->where('role', 'manager')->exists();
    }
}
