<?php

use App\Models\Category;
use App\Models\Household;
use App\Models\User;
use Livewire\Livewire;

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

/**
 * Adds a user with the given role to the household.
 */
function addHouseholdMember(Household $household, string $role): User
{
    $user = User::factory()->create();
    $household->members()->create(['user_id' => $user->id, 'role' => $role, 'joined_at' => now()]);

    return $user;
}

function roleOf(Household $household, User $user): ?string
{
    return $household->members()->where('user_id', $user->id)->value('role');
}

it('lets a manager remove and promote a regular member', function () {
    [$manager, $household] = householdWithMember('manager');
    $member = addHouseholdMember($household, 'member');
    $other = addHouseholdMember($household, 'member');

    $this->withSession(['user_id' => $manager->id]);

    Livewire::test('household.member-list', ['household' => $household])
        ->call('promote', $member->id)
        ->assertSet('status', 'User is now a manager.')
        ->call('remove', $other->id)
        ->assertSet('status', 'User was removed.')
        ->assertDontSee($other->email);

    expect(roleOf($household, $member))->toBe('manager')
        ->and(roleOf($household, $other))->toBeNull();
});

it('stops a manager from demoting or removing another manager or the owner', function () {
    [$manager, $household] = householdWithMember('manager');
    $otherManager = addHouseholdMember($household, 'manager');
    $owner = addHouseholdMember($household, 'owner');

    $this->withSession(['user_id' => $manager->id]);

    Livewire::test('household.member-list', ['household' => $household])
        ->call('demote', $otherManager->id)
        ->assertSet('status', 'You cannot demote this user.')
        ->call('remove', $otherManager->id)
        ->assertSet('status', 'You cannot remove this user.')
        ->call('demote', $owner->id)
        ->assertSet('status', 'You cannot demote this user.')
        ->call('remove', $owner->id)
        ->assertSet('status', 'You cannot remove this user.');

    expect(roleOf($household, $otherManager))->toBe('manager')
        ->and(roleOf($household, $owner))->toBe('owner');
});

it('lets the owner demote and remove managers', function () {
    [$owner, $household] = householdWithMember('owner');
    $demoted = addHouseholdMember($household, 'manager');
    $removed = addHouseholdMember($household, 'manager');

    $this->withSession(['user_id' => $owner->id]);

    Livewire::test('household.member-list', ['household' => $household])
        ->call('demote', $demoted->id)
        ->assertSet('status', 'User is now a member.')
        ->call('remove', $removed->id)
        ->assertSet('status', 'User was removed.');

    expect(roleOf($household, $demoted))->toBe('member')
        ->and(roleOf($household, $removed))->toBeNull();
});

it('stops anyone from removing or demoting themselves', function (string $role) {
    [$user, $household] = householdWithMember($role);

    $this->withSession(['user_id' => $user->id]);

    Livewire::test('household.member-list', ['household' => $household])
        ->call('remove', $user->id)
        ->assertSet('status', 'You cannot remove this user.')
        ->call('demote', $user->id)
        ->assertSet('status', 'You cannot demote this user.');

    expect(roleOf($household, $user))->toBe($role);
})->with(['owner', 'manager']);

it('shows only the actions the current user may take', function () {
    [$manager, $household] = householdWithMember('manager');
    addHouseholdMember($household, 'owner');
    addHouseholdMember($household, 'member');

    $this->withSession(['user_id' => $manager->id]);

    Livewire::test('household.member-list', ['household' => $household])
        ->assertSeeHtml('wire:click="promote(')
        ->assertDontSeeHtml('wire:click="demote(')
        ->assertSeeHtmlInOrder(['Owner', 'Manager', 'Member']);
});

it('makes the creator the owner of a new household', function () {
    $user = User::factory()->create();

    $this->withSession(['user_id' => $user->id]);

    Livewire::test('household.households-list')
        ->set('name', 'New home')
        ->call('add');

    $household = Household::query()->where('name', 'New home')->sole();

    expect(roleOf($household, $user))->toBe('owner')
        ->and($household->isManagedBy($user->id))->toBeTrue();
});

it('puts a user added by email on the list right away', function () {
    [$manager, $household] = householdWithMember('manager');
    $newcomer = User::factory()->create();

    $this->withSession(['user_id' => $manager->id]);

    Livewire::test('household.member-list', ['household' => $household])
        ->assertDontSee($newcomer->email)
        ->set('email', $newcomer->email)
        ->call('add')
        ->assertSet('status', 'User was added.')
        ->assertSet('email', '')
        ->assertSee($newcomer->email)
        ->assertSee('(you)');

    expect($household->memberUsers()->whereKey($newcomer->id)->sole()->pivot->role)->toBe('member');
});

it('reports an unknown or already added email without changing the members', function () {
    [$manager, $household] = householdWithMember('manager');

    $this->withSession(['user_id' => $manager->id]);

    Livewire::test('household.member-list', ['household' => $household])
        ->set('email', 'nobody@example.com')
        ->call('add')
        ->assertSet('status', 'User does not exist.')
        ->assertSet('email', 'nobody@example.com')
        ->set('email', $manager->email)
        ->call('add')
        ->assertSet('status', 'User is already in the household.');

    expect($household->members()->count())->toBe(1);
});

it('forbids a regular member from removing members', function () {
    [$manager, $household] = householdWithMember('manager');
    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    $this->withSession(['user_id' => $member->id]);

    Livewire::test('household.member-list', ['household' => $household])
        ->call('remove', $manager->id)
        ->assertForbidden();

    expect($household->members()->count())->toBe(2);
});
