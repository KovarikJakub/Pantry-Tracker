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
        return view('household.household');
    }

    public function home(Request $request): View|RedirectResponse
    {
        if (! $request->user()) {
            return view('welcome');
        }

        $firstHousehold = $request->user()->households()->orderBy('name')->first();

        if ($firstHousehold) {
            return redirect()->route('households.show', $firstHousehold->id);
        }

        return view('household.show', [
            'household' => null,
            'households' => collect(),
        ]);
    }

    /**
     * Shows the household's appliances and shelves, plus its product catalogue for the "Add food" modal.
     * Members only.
     */
    public function show(string $id, Request $request): View
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('view', $household);

        $household->load([
            'appliances.shelves.foodItems' => fn ($items) => $items->where('status', 'stored'),
            'appliances.shelves.foodItems.product',
            'memberUsers',
        ]);

        $categories = $household->catalogueCategories()->orderBy('name')->get();
        $categoriesById = $categories->keyBy('id');
        $products = $household->catalogueProducts()->with('category')->orderBy('name')->get();

        return view('household.show', [
            'household' => $household,
            'households' => $request->user()->households()->orderBy('name')->get(),
            'catalogueCategoriesByParent' => $categories->groupBy('parent_id'),
            'catalogueProducts' => $products,
            'productCategoryIds' => $products->mapWithKeys(fn ($product) => [
                $product->id => CategoryTree::ancestorIds($categoriesById, $product->category_id),
            ]),
        ]);
    }

}
