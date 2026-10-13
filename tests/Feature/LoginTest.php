<?php

use App\Models\User;

it('shows the login page to guests', function () {
    $this->get(route('login'))->assertOk()->assertSee('Log in');
});

it('logs a user in with valid credentials', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    $this->post(route('login.store'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('user_id', $user->id);
});

it('rejects a wrong password', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $this->post(route('login.store'), ['email' => 'jane@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors('email')
        ->assertSessionMissing('user_id');
});

it('rejects a deactivated user', function () {
    User::factory()->deactivated()->create(['email' => 'jane@example.com']);

    $this->post(route('login.store'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertSessionHasErrors('email')
        ->assertSessionMissing('user_id');
});

it('redirects logged-in users away from the login page', function () {
    $user = User::factory()->create();

    $this->withSession(['user_id' => $user->id])
        ->get(route('login'))
        ->assertRedirect(route('home'));
});

it('logs a user out', function () {
    $user = User::factory()->create();

    $this->withSession(['user_id' => $user->id])
        ->post(route('logout'))
        ->assertRedirect(route('login'))
        ->assertSessionMissing('user_id');
});
