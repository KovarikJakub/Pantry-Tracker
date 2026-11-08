<?php

use App\Models\Category;
use App\Models\User;

it('shows the manager tools to a manager', function () {
    [$manager, $household] = householdWithMember('manager');

    $this->withSession(['user_id' => $manager->id])
        ->get(route('households.show', $household->id))
        ->assertOk()
        ->assertSee(route('households.catalogue', $household->id));
});

it('hides the manager tools from a regular member', function () {
    [$member, $household] = householdWithMember('member');

    $this->withSession(['user_id' => $member->id])
        ->get(route('households.show', $household->id))
        ->assertOk()
        ->assertDontSee(route('households.catalogue', $household->id));
});

it('forbids a non-member from viewing the household', function () {
    [, $household] = householdWithMember('manager');
    $outsider = User::factory()->create();

    $this->withSession(['user_id' => $outsider->id])
        ->get(route('households.show', $household->id))
        ->assertForbidden()
        ->assertSee('You are not a member of this household.');
});

it('offers the default catalogue in the add food modal to a household without its own catalogue', function () {
    [$member, $household] = householdWithMember('member');
    $dairy = Category::query()->create(['name' => 'Dairy']);
    $yoghurts = Category::query()->create(['name' => 'Yoghurts', 'parent_id' => $dairy->id]);
    catalogueProduct($yoghurts, 'Greek yoghurt');

    $this->withSession(['user_id' => $member->id])
        ->get(route('households.show', $household->id))
        ->assertOk()
        ->assertSee('Yoghurts')
        ->assertSee('Greek yoghurt');
});

it('opens the manager tabs only for a manager', function (string $routeName) {
    [$manager, $household] = householdWithMember('manager');
    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    $this->withSession(['user_id' => $manager->id])->get(route($routeName, $household->id))->assertOk();
    $this->withSession(['user_id' => $member->id])->get(route($routeName, $household->id))->assertForbidden();
})->with(['households.members.index', 'households.appliances.index', 'households.catalogue']);

it('lists the household members with their roles on the members tab', function () {
    [$manager, $household] = householdWithMember('manager');

    $this->withSession(['user_id' => $manager->id])
        ->get(route('households.members.index', $household->id))
        ->assertOk()
        ->assertSee($manager->fullName())
        ->assertSee($manager->email)
        ->assertSee('Manager');
});

it('lets a manager remove a regular member but not a manager', function () {
    [$manager, $household] = householdWithMember('manager');
    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    $this->withSession(['user_id' => $manager->id])
        ->delete(route('households.members.destroy', [$household->id, $member->id]))
        ->assertRedirect(route('households.members.index', $household->id));

    $this->withSession(['user_id' => $manager->id])
        ->delete(route('households.members.destroy', [$household->id, $manager->id]))
        ->assertSessionHasErrors('member', null, 'memberRemove');

    expect($household->memberUsers()->pluck('users.id')->all())->toBe([$manager->id]);
});

it('forbids a regular member from removing members', function () {
    [$manager, $household] = householdWithMember('manager');
    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    $this->withSession(['user_id' => $member->id])
        ->delete(route('households.members.destroy', [$household->id, $member->id]))
        ->assertForbidden();
});
