<?php

use App\Models\Category;
use App\Models\Household;
use App\Models\ProductCatalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Creates a household with one member of the given role.
 *
 * @return array{0: User, 1: Household}
 */
function householdWithMember(string $role): array
{
    $user = User::factory()->create();
    $household = Household::query()->create(['name' => 'Home']);
    $household->members()->create(['user_id' => $user->id, 'role' => $role, 'joined_at' => now()]);

    return [$user, $household];
}

/**
 * Creates a product with placeholder nutrition values. Without a household it is a global product.
 */
function catalogueProduct(Category $category, string $name, ?Household $household = null): ProductCatalog
{
    $product = new ProductCatalog([
        'category_id' => $category->id,
        'barcode' => fake()->unique()->ean13(),
        'name' => $name,
        'default_size_g_ml' => 500,
        'measurement_unit' => 'g',
        'calories_per_100g' => 100,
        'protein_per_100g' => 1,
        'fat_per_100g' => 1,
        'carbs_per_100g' => 1,
        'fiber_per_100g' => 0,
    ]);
    $product->household_id = $household?->id;
    $product->save();

    return $product;
}
