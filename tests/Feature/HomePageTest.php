<?php

use App\Models\User;

it('redirects guests to the login page', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
});

it('renders the home page for logged-in users', function () {
    $user = User::factory()->create();

    $this->withSession(['user_id' => $user->id])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Keep track of the food in your household.');
});
