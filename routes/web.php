<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Presentation\Http\Middleware\Authenticate;
use Modules\Identity\Presentation\Http\Middleware\AuthenticateAdmin;

Route::view('/', 'home')->middleware(Authenticate::class)->name('home');

Route::view('/admin', 'admin')->middleware([Authenticate::class, AuthenticateAdmin::class])->name('admin');
