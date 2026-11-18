<?php

use App\Models\Appliance;
use App\Models\Category;
use App\Models\Shelf;
use Livewire\Livewire;

it('lets a manager add an appliance', function () {
    [$manager, $household] = householdWithMember('manager');

    $this->withSession(['user_id' => $manager->id])
        ->post(route('households.appliances.store', $household->id), ['name' => 'Kitchen Fridge', 'appliance_type' => 'fridge'])
        ->assertRedirect(route('households.appliances.index', $household->id));

    expect($household->appliances()->sole())
        ->name->toBe('Kitchen Fridge')
        ->appliance_type->toBe('fridge');

    $this->withSession(['user_id' => $manager->id])
        ->get(route('households.show', $household->id))
        ->assertOk()
        ->assertSee('Kitchen Fridge');
});

it('rejects an unknown appliance type', function () {
    [$manager, $household] = householdWithMember('manager');

    $this->withSession(['user_id' => $manager->id])
        ->post(route('households.appliances.store', $household->id), ['name' => 'Toaster', 'appliance_type' => 'toaster'])
        ->assertSessionHasErrors('appliance_type');

    expect($household->appliances()->exists())->toBeFalse();
});

it('forbids a regular member from adding an appliance', function () {
    [$member, $household] = householdWithMember('member');

    $this->withSession(['user_id' => $member->id])
        ->post(route('households.appliances.store', $household->id), ['name' => 'Kitchen Fridge', 'appliance_type' => 'fridge'])
        ->assertForbidden();

    expect($household->appliances()->exists())->toBeFalse();
});

it('lets a manager remove an empty appliance with its shelves', function () {
    [$manager, $household] = householdWithMember('manager');
    $appliance = $household->appliances()->create(['name' => 'Old Fridge', 'appliance_type' => 'fridge']);
    $appliance->shelves()->create(['label' => 'Top', 'shelf_order' => 1, 'max_load_capacity_g' => 5000]);

    $this->withSession(['user_id' => $manager->id]);

    Livewire::test('appliance.manage-list', ['household' => $household])
        ->call('remove', $appliance->id)
        ->assertSet('status', 'Old Fridge was removed.')
        ->assertSee('There are no appliances in this household.');

    expect(Appliance::query()->exists())->toBeFalse()
        ->and(Shelf::query()->exists())->toBeFalse();
});

it('keeps an appliance that still stores food', function () {
    [$manager, $household] = householdWithMember('manager');
    $appliance = $household->appliances()->create(['name' => 'Fridge', 'appliance_type' => 'fridge']);
    $foodItem = $appliance->shelves()->create(['label' => 'Top', 'shelf_order' => 1, 'max_load_capacity_g' => 5000])
        ->foodItems()->create([
            'product_id' => catalogueProduct(Category::query()->create(['name' => 'Dairy']), 'Cheddar')->id,
            'weight_volume' => 500,
            'purchase_price' => 50,
            'expiration_date' => now()->addWeek(),
            'status' => 'stored',
            'added_at' => now(),
        ]);

    $this->withSession(['user_id' => $manager->id]);

    Livewire::test('appliance.manage-list', ['household' => $household])
        ->call('remove', $appliance->id)
        ->assertSet('status', 'Fridge still contains food. Remove the food first.');

    expect($foodItem->fresh())->not->toBeNull();
});

it('forbids a regular member from removing an appliance', function () {
    [$member, $household] = householdWithMember('member');
    $appliance = $household->appliances()->create(['name' => 'Fridge', 'appliance_type' => 'fridge']);

    $this->withSession(['user_id' => $member->id]);

    Livewire::test('appliance.manage-list', ['household' => $household])
        ->call('remove', $appliance->id)
        ->assertForbidden();

    expect($appliance->fresh())->not->toBeNull();
});
