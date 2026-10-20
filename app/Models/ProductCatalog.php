<?php

namespace App\Models;

use Database\Factories\ProductCatalogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A product that food items are instances of. A NULL household_id means the product is global.
 */
class ProductCatalog extends Model
{
    /** @use HasFactory<ProductCatalogFactory> */
    use HasFactory, HasUuids;

    protected $table = 'product_catalogs';

    protected $fillable = [
        'category_id',
        'barcode',
        'name',
        'default_size_g_ml',
        'measurement_unit',
        'calories_per_100g',
        'protein_per_100g',
        'fat_per_100g',
        'carbs_per_100g',
        'fiber_per_100g',
    ];

    protected function casts(): array
    {
        return [
            'default_size_g_ml' => 'decimal:2',
            'calories_per_100g' => 'decimal:2',
            'protein_per_100g' => 'decimal:2',
            'fat_per_100g' => 'decimal:2',
            'carbs_per_100g' => 'decimal:2',
            'fiber_per_100g' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Household, $this>
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<FoodItem, $this>
     */
    public function foodItems(): HasMany
    {
        return $this->hasMany(FoodItem::class, 'product_id');
    }
}
