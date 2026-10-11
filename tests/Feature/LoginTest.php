<?php

use Modules\Identity\Infrastructure\Persistence\UserModel;

it('shows the login page to guests', function () {
    $this->get(route('login'))->assertOk()->assertSee('Log in');
});

it('logs a user in with valid credentials', function () {
    $user = UserModel::factory()->create(['email' => 'jane@example.com']);

    $this->post(route('login.store'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('identity.user_id', $user->id);
});

it('rejects a wrong password', function () {
    UserModel::factory()->create(['email' => 'jane@example.com']);

    $this->post(route('login.store'), ['email' => 'jane@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors('email')
        ->assertSessionMissing('identity.user_id');
});

it('rejects a deactivated user', function () {
    UserModel::factory()->deactivated()->create(['email' => 'jane@example.com']);

    $this->post(route('login.store'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertSessionHasErrors('email')
        ->assertSessionMissing('identity.user_id');
});

it('redirects logged-in users away from the login page', function () {
    $user = UserModel::factory()->create();

    $this->withSession(['identity.user_id' => $user->id])
        ->get(route('login'))
        ->assertRedirect(route('home'));
});

it('logs a user out', function () {
    $user = UserModel::factory()->create();

    $this->withSession(['identity.user_id' => $user->id])
        ->post(route('logout'))
        ->assertRedirect(route('login'))
        ->assertSessionMissing('identity.user_id');
});
