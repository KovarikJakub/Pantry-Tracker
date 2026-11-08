<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use App\Models\ProductCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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
}
