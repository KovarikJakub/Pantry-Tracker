<?php

namespace App\Models;

use Database\Factories\FoodItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A concrete instance of a catalogue product on a shelf. Status is stored, consumed or discarded.
 */
class FoodItem extends Model
{
    /** @use HasFactory<FoodItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'product_id',
        'registered_by_user_id',
        'weight_volume',
        'purchase_price',
        'expiration_date',
        'status',
        'added_at',
        'removed_at',
        'resolved_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'weight_volume' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'expiration_date' => 'date',
            'added_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Shelf, $this>
     */
    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    /**
     * @return BelongsTo<ProductCatalog, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    /**
     * The user who consumed or discarded the item.
     *
     * @return BelongsTo<User, $this>
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
