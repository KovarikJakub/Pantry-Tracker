<?php

use App\Models\Category;
use App\Models\FoodItem;
use App\Models\Household;
use App\Models\Shelf;
use App\Models\User;

/**
 * Puts one stored food item on a new shelf of the household.
 */
function storedFoodItem(Household $household, User $user): FoodItem
{
    $shelf = $household->appliances()->create(['name' => 'Fridge', 'appliance_type' => 'fridge'])
        ->shelves()->create(['label' => 'Top', 'shelf_order' => 1, 'max_load_capacity_g' => 5000]);

    return $shelf->foodItems()->create([
        'product_id' => catalogueProduct(Category::query()->create(['name' => 'Dairy']), 'Cheddar')->id,
        'registered_by_user_id' => $user->id,
        'weight_volume' => 500,
        'purchase_price' => 50,
        'expiration_date' => now()->addWeek(),
        'status' => 'stored',
        'added_at' => now(),
    ]);
}

it('marks a food item as consumed by the current member', function () {
    [$member, $household] = householdWithMember('member');
    $foodItem = storedFoodItem($household, $member);

    $this->withSession(['user_id' => $member->id])
        ->patch(route('households.food-items.update', [$household->id, $foodItem->id]), ['status' => 'consumed'])
        ->assertRedirect(route('households.show', $household->id));

    expect($foodItem->fresh())
        ->status->toBe('consumed')
        ->resolved_by_user_id->toBe($member->id)
        ->removed_at->not->toBeNull();
});

it('rejects an unknown food item status', function () {
    [$member, $household] = householdWithMember('member');
    $foodItem = storedFoodItem($household, $member);

    $this->withSession(['user_id' => $member->id])
        ->patch(route('households.food-items.update', [$household->id, $foodItem->id]), ['status' => 'removed'])
        ->assertSessionHasErrors('status');

    expect($foodItem->fresh()->status)->toBe('stored');
});

it('deletes a food item without a trace', function () {
    [$member, $household] = householdWithMember('member');
    $foodItem = storedFoodItem($household, $member);

    $this->withSession(['user_id' => $member->id])
        ->delete(route('households.food-items.destroy', [$household->id, $foodItem->id]))
        ->assertRedirect(route('households.show', $household->id));

    expect(FoodItem::query()->find($foodItem->id))->toBeNull();
});

it('forbids a non-member from changing a food item', function () {
    [$member, $household] = householdWithMember('member');
    $foodItem = storedFoodItem($household, $member);
    $outsider = User::factory()->create();

    $this->withSession(['user_id' => $outsider->id])
        ->delete(route('households.food-items.destroy', [$household->id, $foodItem->id]))
        ->assertForbidden();

    expect($foodItem->fresh())->not->toBeNull();
});

/**
 * Puts food expiring soon and food expiring later on one shelf, the later one first.
 *
 * @return array{0: Household, 1: Shelf}
 */
function shelfWithSoonAndLaterFood(): array
{
    [$member, $household] = householdWithMember('member');
    $shelf = $household->appliances()->create(['name' => 'Fridge', 'appliance_type' => 'fridge'])
        ->shelves()->create(['label' => 'Top', 'shelf_order' => 1, 'max_load_capacity_g' => 5000]);
    $dairy = Category::query()->create(['name' => 'Dairy']);

    foreach (['Later milk' => 30, 'Soon milk' => 3] as $name => $daysLeft) {
        $shelf->foodItems()->create([
            'product_id' => catalogueProduct($dairy, $name)->id,
            'registered_by_user_id' => $member->id,
            'weight_volume' => 500,
            'purchase_price' => 50,
            'expiration_date' => today()->addDays($daysLeft),
            'status' => 'stored',
            'added_at' => now(),
        ]);
    }

    return [$household, $shelf];
}

it('lists the food on a shelf by soonest expiry', function () {
    [$household, $shelf] = shelfWithSoonAndLaterFood();

    $this->blade('<x-shelf.card :household="$household" :shelf="$shelf" />', ['household' => $household, 'shelf' => $shelf])
        ->assertSeeInOrder(['Soon milk', 'Later milk']);
});

it('summarises only the food expiring within a week', function () {
    [$household] = shelfWithSoonAndLaterFood();

    $this->blade('<x-household.expiring-soon :household="$household" />', ['household' => $household])
        ->assertSee('Expiring soon')
        ->assertSee('Soon milk')
        ->assertDontSee('Later milk');
});
