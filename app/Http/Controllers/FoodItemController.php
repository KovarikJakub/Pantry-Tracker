<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\Household;
use App\Models\ProductCatalog;
use App\Models\Shelf;
use App\Services\Authenticator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FoodItemController extends Controller
{
    /**
     * Puts one item of a catalogue product on one of the household's shelves,
     * unless it would exceed the shelf's capacity. Members only.
     */
    public function store(Request $request, Authenticator $authenticator, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);
        $userId = $authenticator->user()->id;

        abort_unless($household->members()->where('user_id', $userId)->exists(), 403);

        $validated = $request->validateWithBag('foodItem', [
            'shelf_id' => ['required', 'uuid'],
            'product_id' => ['required', 'uuid'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'expiration_date' => ['required', 'date'],
        ], [
            'shelf_id.required' => 'Select a shelf first.',
        ]);

        $product = ProductCatalog::query()
            ->where(fn (Builder $query) => $query->whereNull('household_id')->orWhere('household_id', $household->id))
            ->findOrFail($validated['product_id']);

        DB::transaction(function () use ($household, $validated, $product, $userId): void {
            // Locking the shelf row serialises concurrent additions to the same shelf.
            $shelf = Shelf::query()
                ->whereHas('appliance', fn (Builder $appliances) => $appliances->where('household_id', $household->id))
                ->lockForUpdate()
                ->findOrFail($validated['shelf_id']);

            $currentLoad = (float) $shelf->foodItems()->where('status', 'stored')->sum('weight_volume');

            if ($currentLoad + (float) $product->default_size_g_ml > (float) $shelf->max_load_capacity_g) {
                throw ValidationException::withMessages([
                    'shelf_id' => "{$product->name} does not fit on {$shelf->label}.",
                ])->errorBag('foodItem');
            }

            $shelf->foodItems()->create([
                'product_id' => $product->id,
                'registered_by_user_id' => $userId,
                'weight_volume' => $product->default_size_g_ml,
                'purchase_price' => $validated['purchase_price'],
                'expiration_date' => $validated['expiration_date'],
                'status' => 'stored',
                'added_at' => now(),
            ]);
        });

        return redirect()->route('households.show', $household->id)
            ->with('food_item_status', "{$product->name} was added.");
    }

    /**
     * Marks a stored food item of the household as consumed or thrown out by the current user,
     * keeping it for the consumption history. Members only.
     */
    public function update(Request $request, Authenticator $authenticator, string $id, string $foodItemId): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', Rule::in(['consumed', 'thrown_out'])],
        ])['status'];

        $household = Household::query()->findOrFail($id);
        $userId = $authenticator->user()->id;

        abort_unless($household->members()->where('user_id', $userId)->exists(), 403);

        $this->storedFoodItemOf($household, $foodItemId)->update([
            'status' => $status,
            'removed_at' => now(),
            'resolved_by_user_id' => $userId,
        ]);

        return redirect()->route('households.show', $household->id);
    }

    /**
     * Deletes a stored food item of the household without a trace, e.g. one added by mistake. Members only.
     */
    public function destroy(Authenticator $authenticator, string $id, string $foodItemId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->members()->where('user_id', $authenticator->user()->id)->exists(), 403);

        $this->storedFoodItemOf($household, $foodItemId)->delete();

        return redirect()->route('households.show', $household->id);
    }

    /**
     * Finds a food item that is still stored on one of the household's shelves.
     */
    private function storedFoodItemOf(Household $household, string $foodItemId): FoodItem
    {
        return FoodItem::query()
            ->whereHas('shelf.appliance', fn (Builder $appliances) => $appliances->where('household_id', $household->id))
            ->where('status', 'stored')
            ->findOrFail($foodItemId);
    }
}
