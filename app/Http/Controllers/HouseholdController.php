<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Services\CategoryTree;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class HouseholdController extends Controller
{
    /**
     * Lists the households the current user is a member of.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $households = Household::query()
            ->whereHas('members', fn (Builder $members) => $members->where('user_id', $userId))
            ->orderBy('name')
            ->get();

        return view('household.household', ['households' => $households]);
    }

    /**
     * Creates a household and makes the current user its manager.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $household = Household::query()->create(['name' => $validated['name']]);

            $household->members()->create([
                'user_id' => $request->user()->id,
                'role' => 'manager',
                'joined_at' => now(),
            ]);
        });

        return redirect()->route('households.index');
    }

    /**
     * Shows the household's appliances and shelves, plus its product catalogue for the "Add food" modal.
     * Members only.
     */
    public function show(string $id): View
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('view', $household);

        $household->load([
            'appliances.shelves.foodItems' => fn ($items) => $items->where('status', 'stored'),
            'appliances.shelves.foodItems.product',
        ]);

        $categories = $household->catalogueCategories()->orderBy('name')->get();
        $categoriesById = $categories->keyBy('id');
        $products = $household->catalogueProducts()->with('category')->orderBy('name')->get();

        return view('household.show', [
            'household' => $household,
            'catalogueCategoriesByParent' => $categories->groupBy('parent_id'),
            'catalogueProducts' => $products,
            'productCategoryIds' => $products->mapWithKeys(fn ($product) => [
                $product->id => CategoryTree::ancestorIds($categoriesById, $product->category_id),
            ]),
        ]);
    }
}
