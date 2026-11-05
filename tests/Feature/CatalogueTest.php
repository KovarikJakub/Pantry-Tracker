<?php

use App\Models\Category;
use App\Models\Household;
use App\Models\ProductCatalog;

/**
 * Creates a product with placeholder nutrition values.
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

it('shows the default category tree with nested subcategories and products to a manager', function () {
    [$manager, $household] = householdWithMember('manager');

    $dairy = Category::query()->create(['name' => 'Dairy']);
    $yoghurts = Category::query()->create(['name' => 'Yoghurts', 'parent_id' => $dairy->id]);
    catalogueProduct($yoghurts, 'Plain yoghurt');

    $this->withSession(['user_id' => $manager->id])
        ->get(route('households.catalogue', $household->id))
        ->assertOk()
        ->assertSeeInOrder(['Dairy', 'Yoghurts', 'Plain yoghurt']);
});

it('shows only the household\'s own tree once it has a custom catalogue', function () {
    [$manager, $household] = householdWithMember('manager');
    $household->forceFill(['has_custom_categories' => true])->save();

    Category::query()->create(['name' => 'Default only']);
    $own = new Category(['name' => 'Our pantry']);
    $own->household_id = $household->id;
    $own->save();

    $this->withSession(['user_id' => $manager->id])
        ->get(route('households.catalogue', $household->id))
        ->assertOk()
        ->assertSee('Our pantry')
        ->assertDontSee('Default only');
});

it('forbids a regular member from viewing the catalogue', function () {
    [$member, $household] = householdWithMember('member');

    $this->withSession(['user_id' => $member->id])
        ->get(route('households.catalogue', $household->id))
        ->assertForbidden();
});

it('redirects a guest to the login page', function () {
    [, $household] = householdWithMember('manager');

    $this->get(route('households.catalogue', $household->id))
        ->assertRedirect(route('login'));
});

/**
 * Gives the household a custom catalogue containing one category.
 */
function ownCategory(Household $household, string $name, ?Category $parent = null): Category
{
    $household->forceFill(['has_custom_categories' => true])->save();

    return $household->categories()->create(['name' => $name, 'parent_id' => $parent?->id]);
}

it('lets a manager add a subcategory and reopens its parent', function () {
    [$manager, $household] = householdWithMember('manager');
    $dairy = ownCategory($household, 'Dairy');

    $this->withSession(['user_id' => $manager->id])
        ->post(route('households.categories.store', $household->id), ['parent_id' => $dairy->id, 'name' => 'Cheese'])
        ->assertRedirect(route('households.catalogue', $household->id))
        ->assertSessionHas('opened_category_id', $dairy->id);

    expect($dairy->children()->sole())
        ->name->toBe('Cheese')
        ->household_id->toBe($household->id);
});

it('copies the default tree before adding a subcategory, leaving the default tree untouched', function () {
    [$manager, $household] = householdWithMember('manager');
    $defaultDairy = Category::query()->create(['name' => 'Dairy']);

    $this->withSession(['user_id' => $manager->id])
        ->post(route('households.categories.store', $household->id), ['parent_id' => $defaultDairy->id, 'name' => 'Cheese'])
        ->assertRedirect(route('households.catalogue', $household->id));

    $copiedDairy = $household->categories()->where('name', 'Dairy')->sole();

    expect($household->fresh()->has_custom_categories)->toBeTrue()
        ->and($defaultDairy->children()->exists())->toBeFalse()
        ->and($copiedDairy->children()->sole()->name)->toBe('Cheese');
});

it('rejects a subcategory whose name is already used by a sibling', function () {
    [$manager, $household] = householdWithMember('manager');
    $dairy = ownCategory($household, 'Dairy');
    ownCategory($household, 'Cheese', $dairy);

    $this->withSession(['user_id' => $manager->id])
        ->post(route('households.categories.store', $household->id), ['parent_id' => $dairy->id, 'name' => 'Cheese'])
        ->assertSessionHasErrors('name', errorBag: 'category');

    expect($dairy->children()->count())->toBe(1);
});

it('rejects a parent category from another household', function () {
    [$manager, $household] = householdWithMember('manager');
    $household->forceFill(['has_custom_categories' => true])->save();
    [, $otherHousehold] = householdWithMember('manager');
    $foreignCategory = ownCategory($otherHousehold, 'Foreign');

    $this->withSession(['user_id' => $manager->id])
        ->post(route('households.categories.store', $household->id), ['parent_id' => $foreignCategory->id, 'name' => 'Cheese'])
        ->assertNotFound();

    expect($foreignCategory->children()->exists())->toBeFalse();
});

it('forbids a regular member from adding a subcategory', function () {
    [$member, $household] = householdWithMember('member');
    $dairy = ownCategory($household, 'Dairy');

    $this->withSession(['user_id' => $member->id])
        ->post(route('households.categories.store', $household->id), ['parent_id' => $dairy->id, 'name' => 'Cheese'])
        ->assertForbidden();

    expect($dairy->children()->exists())->toBeFalse();
});
