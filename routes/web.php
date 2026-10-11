<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Presentation\Http\Middleware\Authenticate;

Route::view('/', 'home')->middleware(Authenticate::class)->name('home');
