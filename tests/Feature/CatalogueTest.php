<?php

use App\Models\Category;
use App\Models\Household;

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

it('hides a deleted product but keeps it for food items made from it', function () {
    [$manager, $household] = householdWithMember('manager');
    $dairy = ownCategory($household, 'Dairy');
    $product = catalogueProduct($dairy, 'Cheddar', $household);
    $shelf = $household->appliances()->create(['name' => 'Fridge', 'appliance_type' => 'fridge'])
        ->shelves()->create(['label' => 'Top', 'shelf_order' => 1, 'max_load_capacity_g' => 5000]);
    $foodItem = $shelf->foodItems()->create([
        'product_id' => $product->id,
        'registered_by_user_id' => $manager->id,
        'weight_volume' => 500,
        'purchase_price' => 50,
        'expiration_date' => now()->addWeek(),
        'status' => 'consumed',
        'added_at' => now(),
    ]);

    $this->withSession(['user_id' => $manager->id])
        ->delete(route('households.products.destroy', [$household->id, $product->id]))
        ->assertRedirect(route('households.catalogue', $household->id))
        ->assertSessionHas('opened_category_id', $dairy->id);

    expect($product->fresh()->trashed())->toBeTrue()
        ->and($foodItem->fresh()->product->name)->toBe('Cheddar');

    $this->withSession(['user_id' => $manager->id])
        ->get(route('households.catalogue', $household->id))
        ->assertDontSee('Cheddar');
});

it('deletes the household copy of a default product, leaving the global product untouched', function () {
    [$manager, $household] = householdWithMember('manager');
    $globalProduct = catalogueProduct(Category::query()->create(['name' => 'Dairy']), 'Cheddar');

    $this->withSession(['user_id' => $manager->id])
        ->delete(route('households.products.destroy', [$household->id, $globalProduct->id]))
        ->assertRedirect(route('households.catalogue', $household->id));

    expect($globalProduct->fresh()->trashed())->toBeFalse()
        ->and($household->products()->withTrashed()->sole()->trashed())->toBeTrue();
});

it('rejects deleting a product from another household', function () {
    [$manager, $household] = householdWithMember('manager');
    $household->forceFill(['has_custom_categories' => true])->save();
    [, $otherHousehold] = householdWithMember('manager');
    $foreignProduct = catalogueProduct(ownCategory($otherHousehold, 'Foreign'), 'Cheddar', $otherHousehold);

    $this->withSession(['user_id' => $manager->id])
        ->delete(route('households.products.destroy', [$household->id, $foreignProduct->id]))
        ->assertNotFound();

    expect($foreignProduct->fresh()->trashed())->toBeFalse();
});

it('forbids a regular member from deleting a product', function () {
    [$member, $household] = householdWithMember('member');
    $product = catalogueProduct(ownCategory($household, 'Dairy'), 'Cheddar', $household);

    $this->withSession(['user_id' => $member->id])
        ->delete(route('households.products.destroy', [$household->id, $product->id]))
        ->assertForbidden();

    expect($product->fresh()->trashed())->toBeFalse();
});

it('stores the default price of a new product and keeps it when the default catalogue is copied', function () {
    [$manager, $household] = householdWithMember('manager');
    $dairy = Category::query()->create(['name' => 'Dairy']);
    catalogueProduct($dairy, 'Cheddar')->update(['default_price' => 65, 'default_expiry_days' => 30]);

    $this->withSession(['user_id' => $manager->id])
        ->post(route('households.products.store', $household->id), [
            'category_id' => $dairy->id,
            'name' => 'Gouda',
            'default_size_g_ml' => 200,
            'measurement_unit' => 'g',
            'default_price' => 49.5,
            'default_expiry_days' => 14,
            'calories_per_100g' => 350,
            'protein_per_100g' => 25,
            'fat_per_100g' => 27,
            'carbs_per_100g' => 2,
            'fiber_per_100g' => 0,
        ])
        ->assertRedirect(route('households.catalogue', $household->id));

    expect($household->products()->orderBy('name')->pluck('default_price', 'name')->all())
        ->toBe(['Cheddar' => '65.00', 'Gouda' => '49.50']);

    expect($household->products()->orderBy('name')->pluck('default_expiry_days', 'name')->all())
        ->toBe(['Cheddar' => 30, 'Gouda' => 14]);
});
