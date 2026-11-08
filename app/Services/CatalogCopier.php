<?php

namespace App\Services;

use App\Models\Category;
use App\Models\FoodItem;
use App\Models\Household;
use App\Models\ProductCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Copy-on-write for the catalogue: gives a household its own copy of the default category tree
 * and global products, then points the household's products and food items at the copies.
 */
class CatalogCopier
{
    /**
     * Does nothing (and returns empty maps) if the household already has its own catalogue.
     *
     * @return array{categoryIds: array<string, string>, productIds: array<string, string>} default id => copied id
     */
    public function copyDefaultTo(Household $household): array
    {
        if ($household->has_custom_categories) {
            return ['categoryIds' => [], 'productIds' => []];
        }

        return DB::transaction(function () use ($household): array {
            $defaultCategories = Category::query()->whereNull('household_id')->get()->groupBy('parent_id');

            /** @var array<string, string> $categoryIds default category id => copied category id */
            $categoryIds = [];
            $this->copyCategories($household, $defaultCategories, '', null, $categoryIds);

            /** @var array<string, string> $productIds global product id => copied product id */
            $productIds = [];

            foreach (ProductCatalog::query()->whereNull('household_id')->get() as $product) {
                $copy = $household->products()->create([
                    ...$product->only($product->getFillable()),
                    'category_id' => $categoryIds[$product->category_id],
                ]);

                $productIds[$product->id] = $copy->id;
            }

            foreach ($categoryIds as $defaultId => $copyId) {
                $household->products()->where('category_id', $defaultId)->update(['category_id' => $copyId]);
            }

            foreach ($productIds as $globalId => $copyId) {
                FoodItem::query()
                    ->where('product_id', $globalId)
                    ->whereHas('shelf.appliance', fn (Builder $appliances) => $appliances->where('household_id', $household->id))
                    ->update(['product_id' => $copyId]);
            }

            $household->has_custom_categories = true;
            $household->save();

            return ['categoryIds' => $categoryIds, 'productIds' => $productIds];
        });
    }

    /**
     * @param  Collection<string, Collection<int, Category>>  $defaultCategories  grouped by parent_id ('' = root)
     * @param  array<string, string>  $categoryIds
     */
    private function copyCategories(Household $household, Collection $defaultCategories, string $parentKey, ?string $copiedParentId, array &$categoryIds): void
    {
        foreach ($defaultCategories->get($parentKey, collect()) as $category) {
            $copy = $household->categories()->create([
                'name' => $category->name,
                'parent_id' => $copiedParentId,
            ]);

            $categoryIds[$category->id] = $copy->id;

            $this->copyCategories($household, $defaultCategories, $category->id, $copy->id, $categoryIds);
        }
    }
}
