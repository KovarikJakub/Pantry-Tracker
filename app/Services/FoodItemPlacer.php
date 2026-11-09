<?php

namespace App\Services;

use App\Models\FoodItem;
use App\Models\Household;
use App\Models\ProductCatalog;
use App\Models\Shelf;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FoodItemPlacer
{
    /**
     * Puts one item of a product on one of the household's shelves, unless it would exceed the shelf's capacity.
     *
     * @throws ValidationException when the product does not fit
     */
    public function place(
        Household $household,
        string $shelfId,
        ProductCatalog $product,
        string $userId,
        float|string $purchasePrice,
        DateTimeInterface|string $expirationDate,
    ): FoodItem {
        return DB::transaction(function () use ($household, $shelfId, $product, $userId, $purchasePrice, $expirationDate): FoodItem {
            // Locking the shelf row serialises concurrent additions to the same shelf.
            $shelf = Shelf::query()
                ->whereHas('appliance', fn (Builder $appliances) => $appliances->where('household_id', $household->id))
                ->lockForUpdate()
                ->findOrFail($shelfId);

            $currentLoad = (float) $shelf->foodItems()->where('status', 'stored')->sum('weight_volume');

            if ($currentLoad + (float) $product->default_size_g_ml > (float) $shelf->max_load_capacity_g) {
                throw ValidationException::withMessages([
                    'shelf_id' => "{$product->name} does not fit on {$shelf->label}.",
                ])->errorBag('foodItem');
            }

            return $shelf->foodItems()->create([
                'product_id' => $product->id,
                'registered_by_user_id' => $userId,
                'weight_volume' => $product->default_size_g_ml,
                'purchase_price' => $purchasePrice,
                'expiration_date' => $expirationDate,
                'status' => 'stored',
                'added_at' => now(),
            ]);
        });
    }
}
