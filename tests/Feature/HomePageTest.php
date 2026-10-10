<?php

it('renders the static home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Keep track of the food in your household.');
});
