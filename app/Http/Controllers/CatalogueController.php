<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Services\CategoryTree;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class CatalogueController extends Controller
{
    /**
     * Shows the household's category tree with its products. Managers only.
     */
    public function show(string $id): View
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        $categories = $household->catalogueCategories()->orderBy('name')->get();
        $productsByCategory = $household->catalogueProducts()->orderBy('name')->get()->groupBy('category_id');

        // After adding a subcategory (or failing to), reopen the branch the manager was working in.
        $focusedCategoryId = session('opened_category_id') ?? old('parent_id');

        return view('household.catalogue', [
            'household' => $household,
            'categoriesByParent' => $categories->groupBy('parent_id'),
            'productsByCategory' => $productsByCategory,
            'openCategoryIds' => CategoryTree::ancestorIds($categories->keyBy('id'), $focusedCategoryId),
        ]);
    }
}
