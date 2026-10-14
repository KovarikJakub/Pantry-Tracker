<?php

use App\Models\Household;
use App\Models\User;

/**
 * @return array{0: User, 1: Household}
 */
function householdWithMember(string $role): array
{
    $user = User::factory()->create();
    $household = Household::query()->create(['name' => 'Home']);
    $household->members()->create(['user_id' => $user->id, 'role' => $role, 'joined_at' => now()]);

    return [$user, $household];
}

it('lets a manager add an appliance', function () {
    [$manager, $household] = householdWithMember('manager');

    $this->withSession(['user_id' => $manager->id])
        ->post(route('households.appliances.store', $household->id), ['name' => 'Kitchen Fridge', 'appliance_type' => 'fridge'])
        ->assertRedirect(route('households.show', $household->id));

    expect($household->appliances()->sole())
        ->name->toBe('Kitchen Fridge')
        ->appliance_type->toBe('fridge');

    $this->withSession(['user_id' => $manager->id])
        ->get(route('households.show', $household->id))
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
