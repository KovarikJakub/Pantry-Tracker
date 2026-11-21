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
use Illuminate\Support\Facades\Validator;

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

        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'default_size_g_ml' => ['required', 'numeric', 'min:0.01'],
            'measurement_unit' => ['required', 'in:g,ml'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'default_expiry_days' => ['required', 'integer', 'min:1'],
            'calories_per_100g' => ['required', 'numeric', 'min:0', 'max:900'],
            'protein_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'fat_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'carbs_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'fiber_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $macroSum = (float) $request->input('protein_per_100g')
                + (float) $request->input('fat_per_100g')
                + (float) $request->input('carbs_per_100g')
                + (float) $request->input('fiber_per_100g');

            if ($macroSum > 100.5) {
                $validator->errors()->add(
                    'protein_per_100g',
                    'Protein, fat, carbohydrates, and fibre combined cannot exceed 100 g per 100 g.'
                );
            }
        });

        $validated = $validator->validateWithBag('product');

        $category = Category::query()
            ->where('household_id', $household->catalogueOwnerId())
            ->findOrFail($request->input('category_id'));

        $copiedCategoryIds = $catalogCopier->copyDefaultTo($household)['categoryIds'];
        $categoryId = $copiedCategoryIds[$category->id] ?? $category->id;

        $household->products()->create([
            ...$validated,
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

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'default_size_g_ml' => ['required', 'numeric', 'min:0.01'],
            'measurement_unit' => ['required', 'in:g,ml'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'default_expiry_days' => ['required', 'integer', 'min:1'],
            'calories_per_100g' => ['required', 'numeric', 'min:0', 'max:900'],
            'protein_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'fat_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'carbs_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'fiber_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $macroSum = (float) $request->input('protein_per_100g')
                + (float) $request->input('fat_per_100g')
                + (float) $request->input('carbs_per_100g')
                + (float) $request->input('fiber_per_100g');

            if ($macroSum > 100.5) {
                $validator->errors()->add(
                    'protein_per_100g',
                    'Protein, fat, carbohydrates, and fibre combined cannot exceed 100 g per 100 g.'
                );
            }
        });

        $validated = $validator->validateWithBag('editProduct');

        $product = ProductCatalog::query()->where('household_id', $household->catalogueOwnerId())->findOrFail($productId);

        $categoryId = DB::transaction(function () use ($household, $catalogCopier, $product, $validated): string {
            $copiedProductIds = $catalogCopier->copyDefaultTo($household)['productIds'];
            $householdProduct = $household->products()->findOrFail($copiedProductIds[$product->id] ?? $product->id);

            $householdProduct->update($validated);

            return $householdProduct->category_id;
        });

        return redirect()->route('households.catalogue', $household->id)->with('opened_category_id', $categoryId);
    }
}
