<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

it('loads routes and views from the modules directory', function () {
    expect(Route::has('login'))->toBeTrue()
        ->and(View::exists('identity::login'))->toBeTrue();
});
