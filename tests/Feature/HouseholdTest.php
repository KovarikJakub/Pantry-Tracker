<?php

use App\Models\User;

it('shows the manager tools to a manager', function () {
    [$manager, $household] = householdWithMember('manager');

    $this->withSession(['user_id' => $manager->id])
        ->get(route('households.show', $household->id))
        ->assertOk()
        ->assertSee('Manage category catalogue');
});

it('hides the manager tools from a regular member', function () {
    [$member, $household] = householdWithMember('member');

    $this->withSession(['user_id' => $member->id])
        ->get(route('households.show', $household->id))
        ->assertOk()
        ->assertDontSee('Manage category catalogue');
});

it('forbids a non-member from viewing the household', function () {
    [, $household] = householdWithMember('manager');
    $outsider = User::factory()->create();

    $this->withSession(['user_id' => $outsider->id])
        ->get(route('households.show', $household->id))
        ->assertForbidden()
        ->assertSee('You are not a member of this household.');
});
