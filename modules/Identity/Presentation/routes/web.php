<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Presentation\Http\Controllers\LoginController;
use Modules\Identity\Presentation\Http\Controllers\LogoutController;
use Modules\Identity\Presentation\Http\Middleware\Authenticate;
use Modules\Identity\Presentation\Http\Middleware\RedirectIfAuthenticated;

Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', LogoutController::class)->middleware(Authenticate::class)->name('logout');
