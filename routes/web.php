<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HouseholdController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware(Authenticate::class)->group(function () {
    Route::view('/', 'home')->name('home');
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::view('/admin', 'admin')->middleware(AuthenticateAdmin::class)->name('admin');
    Route::get('/households', [HouseholdController::class, 'index'])->name('households.index');
    Route::get('/households/{id}', [HouseholdController::class, 'show'])->whereUuid('id')->name('households.show');
    Route::get('/households/{id}/catalogue', [HouseholdController::class, 'catalogue'])->whereUuid('id')->name('households.catalogue');
    Route::post('/households/{id}/categories', [HouseholdController::class, 'addCategory'])->whereUuid('id')->name('households.categories.store');
    Route::post('/households', [HouseholdController::class, 'store'])->name('households.store');
    Route::post('/households/{id}/members', [HouseholdController::class, 'addMember'])->whereUuid('id')->name('households.members.store');
    Route::post('/households/{id}/appliances', [HouseholdController::class, 'addAppliance'])->whereUuid('id')->name('households.appliances.store');
    Route::post('/households/{id}/appliances/{applianceId}/shelves', [HouseholdController::class, 'addShelf'])->whereUuid(['id', 'applianceId'])->name('households.shelves.store');
    Route::post('/households/{id}/food-items', [HouseholdController::class, 'addFoodItem'])->whereUuid('id')->name('households.food-items.store');
    Route::post('/households/{id}/food-items/remove/{foodItemId}', [HouseholdController::class, 'removeFoodItem'])->whereUuid(['id', 'foodItemId'])->name('household.food-items.remove');
});
