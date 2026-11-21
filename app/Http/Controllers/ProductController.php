<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use App\Models\ProductCatalog;
use App\Services\CatalogCopier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Hides a product from the household's catalogue (soft delete). Food items already made from it keep
     * referring to it. A household still using the default tree gets its own copy first. Managers only.
     */
    public function destroy(CatalogCopier $catalogCopier, string $id, string $productId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

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
     * Adds a product to one of the household's categories. A household still using the default tree
     * gets its own copy first. Managers only.
     */
    public function store(Request $request, CatalogCopier $catalogCopier, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        $category = Category::query()
            ->where('household_id', $household->catalogueOwnerId())
            ->findOrFail($request->input('category_id'));

        $copiedCategoryIds = $catalogCopier->copyDefaultTo($household)['categoryIds'];
        $categoryId = $copiedCategoryIds[$category->id] ?? $category->id;

        $household->products()->create([
            ...$request->only([
                'name',
                'barcode',
                'default_size_g_ml',
                'measurement_unit',
                'default_price',
                'calories_per_100g',
                'protein_per_100g',
                'fat_per_100g',
                'carbs_per_100g',
                'fiber_per_100g',
            ]),
            'category_id' => $categoryId,
        ]);

        return redirect()->route('households.catalogue', $household->id)
            ->with('opened_category_id', $categoryId);
    }

    /**
     * Updates a product in the household's catalogue. A household still using the default tree
     * gets its own copy first. Managers only.
     */
    public function update(Request $request, CatalogCopier $catalogCopier, string $id, string $productId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);
        Gate::authorize('manage', $household);

        $product = ProductCatalog::query()->where('household_id', $household->catalogueOwnerId())->findOrFail($productId);

        $categoryId = DB::transaction(function () use ($household, $catalogCopier, $product, $request): string {
            $copiedProductIds = $catalogCopier->copyDefaultTo($household)['productIds'];
            $householdProduct = $household->products()->findOrFail($copiedProductIds[$product->id] ?? $product->id);

            $householdProduct->update($request->only([
                'name',
                'barcode',
                'default_size_g_ml',
                'measurement_unit',
                'default_price',
                'calories_per_100g',
                'protein_per_100g',
                'fat_per_100g',
                'carbs_per_100g',
                'fiber_per_100g',
            ]));

            return $householdProduct->category_id;
        });

        return redirect()->route('households.catalogue', $household->id)->with('opened_category_id', $categoryId);
    }
}
