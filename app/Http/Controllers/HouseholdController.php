<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FoodItem;
use App\Models\Household;
use App\Models\ProductCatalog;
use App\Models\Shelf;
use App\Models\User;
use App\Services\Authenticator;
use App\Services\CatalogCopier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HouseholdController extends Controller
{
    /**
     * Lists the households the current user is a member of.
     */
    public function index(Authenticator $authenticator): View
    {
        $userId = $authenticator->user()->id;

        $households = Household::query()
            ->whereHas('members', fn (Builder $members) => $members->where('user_id', $userId))
            ->orderBy('name')
            ->get();

        return view('household.household', ['households' => $households]);
    }

    /**
     * Creates a household and makes the current user its manager.
     */
    public function store(Request $request, Authenticator $authenticator): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $authenticator): void {
            $household = Household::query()->create(['name' => $validated['name']]);

            $household->members()->create([
                'user_id' => $authenticator->user()->id,
                'role' => 'manager',
                'joined_at' => now(),
            ]);
        });

        return redirect()->route('households.index');
    }

    /**
     * Shows the household's appliances and shelves. Members only.
     */
    public function show(string $id, Authenticator $authenticator): View|Response
    {
        $household = Household::query()->with([
            'appliances.shelves.foodItems' => fn ($items) => $items->where('status', 'stored'),
            'appliances.shelves.foodItems.product',
        ])->findOrFail($id);

        $userId = $authenticator->user()->id;

        if (! $household->members()->where('user_id', $userId)->exists()) {
            return response('You are not a member of this household.', 403);
        }

        return view('household.show', [
            'household' => $household,
            'isManager' => $household->isManagedBy($userId),
        ]);
    }

    /**
     * Shows the household's category tree with its products. Managers only.
     */
    public function catalogue(string $id, Authenticator $authenticator): View
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $ownerId = $household->catalogueOwnerId();

        $categories = Category::query()
            ->where('household_id', $ownerId)
            ->orderBy('name')
            ->get();

        $productsByCategory = ProductCatalog::query()
            ->where('household_id', $ownerId)
            ->orderBy('name')
            ->get()
            ->groupBy('category_id');

        // After adding a subcategory (or failing to), reopen the branch the manager was working in.
        $focusedCategoryId = session('opened_category_id') ?? old('parent_id');

        return view('household.catalogue', [
            'household' => $household,
            'categoriesByParent' => $categories->groupBy('parent_id'),
            'productsByCategory' => $productsByCategory,
            'openCategoryIds' => $this->categoryWithAncestorIds($categories->keyBy('id'), $focusedCategoryId),
        ]);
    }

    /**
     * Adds a subcategory under one of the household's categories. Managers only.
     * A household still using the default tree gets its own copy first, so the default tree is never changed.
     */
    public function storeCategory(Request $request, Authenticator $authenticator, CatalogCopier $catalogCopier, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $validated = $request->validateWithBag('category', [
            'parent_id' => ['required', 'uuid'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where('parent_id', (string) $request->input('parent_id')),
            ],
        ], [
            'name.unique' => 'This category already has a subcategory with that name.',
        ]);

        $parent = Category::query()
            ->where('household_id', $household->catalogueOwnerId())
            ->findOrFail($validated['parent_id']);

        $parentId = DB::transaction(function () use ($household, $catalogCopier, $parent, $validated): string {
            $copiedCategoryIds = $catalogCopier->copyDefaultTo($household)['categoryIds'];
            $parentId = $copiedCategoryIds[$parent->id] ?? $parent->id;

            $household->categories()->create([
                'name' => $validated['name'],
                'parent_id' => $parentId,
            ]);

            return $parentId;
        });

        return redirect()->route('households.catalogue', $household->id)
            ->with('opened_category_id', $parentId);
    }

    /**
     * Hides a product from the household's catalogue (soft delete). Food items already made from it keep
     * referring to it. A household still using the default tree gets its own copy first. Managers only.
     */
    public function destroyProduct(Authenticator $authenticator, CatalogCopier $catalogCopier, string $id, string $productId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $product = ProductCatalog::query()
            ->where('household_id', $household->catalogueOwnerId())
            ->findOrFail($productId);

        $categoryId = DB::transaction(function () use ($household, $catalogCopier, $product): string {
            $copiedProductIds = $catalogCopier->copyDefaultTo($household)['productIds'];
            $householdProduct = $household->products()->findOrFail($copiedProductIds[$product->id] ?? $product->id);

            $householdProduct->delete();

            return $householdProduct->category_id;
        });

        return redirect()->route('households.catalogue', $household->id)
            ->with('opened_category_id', $categoryId);
    }

    /**
     * @param  Collection<string, Category>  $categoriesById
     * @return list<string> the category's id followed by the ids of all its ancestors; empty if not found
     */
    private function categoryWithAncestorIds(Collection $categoriesById, ?string $categoryId): array
    {
        $ids = [];
        $category = $categoryId === null ? null : $categoriesById->get($categoryId);

        while ($category !== null) {
            $ids[] = $category->id;
            $category = $category->parent_id === null ? null : $categoriesById->get($category->parent_id);
        }

        return $ids;
    }

    /**
     * Adds an appliance to the household. Managers only.
     */
    public function storeAppliance(Request $request, Authenticator $authenticator, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'appliance_type' => ['required', Rule::in(['fridge', 'freezer', 'shelf', 'pantry', 'cabinet'])],
        ]);

        $household->appliances()->create([
            'name' => $validated['name'],
            'appliance_type' => $validated['appliance_type'],
        ]);

        return redirect()->route('households.show', $household->id);
    }

    /**
     * Adds a shelf at the end of one of the household's appliances. Managers only.
     */
    public function storeShelf(Request $request, Authenticator $authenticator, string $id, string $applianceId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $appliance = $household->appliances()->findOrFail($applianceId);

        // A separate error bag per appliance keeps errors under the form that was submitted.
        $validated = $request->validateWithBag('shelf_'.$appliance->id, [
            'label' => ['required', 'string', 'max:255'],
            'max_load_capacity_g' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ]);

        $appliance->shelves()->create([
            'label' => $validated['label'],
            'shelf_order' => ($appliance->shelves()->max('shelf_order') ?? 0) + 1,
            'max_load_capacity_g' => $validated['max_load_capacity_g'],
        ]);

        return redirect()->route('households.show', $household->id);
    }

    /**
     * Puts one item of a catalogue product on one of the household's shelves,
     * unless it would exceed the shelf's capacity. Members only.
     */
    public function storeFoodItem(Request $request, Authenticator $authenticator, string $id): RedirectResponse
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
    public function updateFoodItem(Request $request, Authenticator $authenticator, string $id, string $foodItemId): RedirectResponse
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
    public function destroyFoodItem(Authenticator $authenticator, string $id, string $foodItemId): RedirectResponse
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

    /**
     * Adds an existing user, found by email, to the household. Managers only.
     */
    public function storeMember(Request $request, Authenticator $authenticator, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $user = User::query()->where('email', (string) $request->input('email'))->first();

        if ($user === null) {
            $status = 'User does not exist.';
        } elseif ($household->members()->where('user_id', $user->id)->exists()) {
            $status = 'User is already in the household.';
        } else {
            $household->members()->create([
                'user_id' => $user->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
            $status = 'User was added.';
        }

        return redirect()->route('households.show', $household->id)->with('status', $status);
    }
}
