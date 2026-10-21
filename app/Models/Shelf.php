<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelf extends Model
{
    use HasUuids;

    protected $table = 'shelves';

    protected $fillable = [
        'label',
        'shelf_order',
        'max_load_capacity_g',
    ];

    protected function casts(): array
    {
        return [
            'shelf_order' => 'integer',
            'max_load_capacity_g' => 'decimal:2',
        ];
    }

    protected function totalWeight(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->foodItems()->sum('weight_volume')
        );
    }

    /**
     * @return BelongsTo<Appliance, $this>
     */
    public function appliance(): BelongsTo
    {
        return $this->belongsTo(Appliance::class);
    }

    /**
     * @return HasMany<FoodItem, $this>
     */
    public function foodItems(): HasMany
    {
        return $this->hasMany(FoodItem::class);
    }
}
