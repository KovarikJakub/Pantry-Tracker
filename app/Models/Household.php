<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * The household's private products. Global products have a NULL household_id.
     *
     * @return HasMany<ProductCatalog, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(ProductCatalog::class);
    }

    /**
     * The household_id of the catalogue rows this household uses: its own once it has a custom catalogue,
     * otherwise NULL (the default tree and global products).
     */
    public function catalogueOwnerId(): ?string
    {
        return $this->has_custom_categories ? $this->id : null;
    }

    /**
     * The categories of the catalogue this household currently uses (its own copy or the default tree).
     *
     * @return Builder<Category>
     */
    public function catalogueCategories(): Builder
    {
        return Category::query()->where('household_id', $this->catalogueOwnerId());
    }

    /**
     * The products of the catalogue this household currently uses (its own copies or the global products).
     *
     * @return Builder<ProductCatalog>
     */
    public function catalogueProducts(): Builder
    {
        return ProductCatalog::query()->where('household_id', $this->catalogueOwnerId());
    }

    public function isManagedBy(string $userId): bool
    {
        return $this->members()->where('user_id', $userId)->where('role', 'manager')->exists();
    }
}
