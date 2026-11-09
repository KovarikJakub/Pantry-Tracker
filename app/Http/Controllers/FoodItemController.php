<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\Household;
use App\Models\ProductCatalog;
use App\Services\FoodItemPlacer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class FoodItemController extends Controller
{
    /**
     * Puts one item of a catalogue product on one of the household's shelves,
     * unless it would exceed the shelf's capacity. Without a price, the product's default price is used. Members only.
     */
    public function store(Request $request, FoodItemPlacer $foodItemPlacer, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);
        $userId = $request->user()->id;

        Gate::authorize('view', $household);

        $validated = $request->validateWithBag('foodItem', [
            'shelf_id' => ['required', 'uuid'],
            'product_id' => ['required', 'uuid'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'expiration_date' => ['required', 'date'],
        ], [
            'shelf_id.required' => 'Select a shelf first.',
        ]);

        $product = ProductCatalog::query()
            ->where(fn (Builder $query) => $query->whereNull('household_id')->orWhere('household_id', $household->id))
            ->findOrFail($validated['product_id']);

        $foodItemPlacer->place(
            $household,
            $validated['shelf_id'],
            $product,
            $userId,
            $validated['purchase_price'] ?? $product->default_price,
            $validated['expiration_date'],
        );

        return redirect()->route('households.show', $household->id)
            ->with('food_item_status', "{$product->name} was added.");
    }

    /**
     * Marks a stored food item of the household as consumed or thrown out by the current user,
     * keeping it for the consumption history. Members only.
     */
    public function update(Request $request, string $id, string $foodItemId): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', Rule::in(['consumed', 'thrown_out'])],
        ])['status'];

        $household = Household::query()->findOrFail($id);
        $userId = $request->user()->id;

        Gate::authorize('view', $household);

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
    public function destroy(string $id, string $foodItemId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('view', $household);

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
