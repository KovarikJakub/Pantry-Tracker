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
    Route::post('/households', [HouseholdController::class, 'store'])->name('households.store');
    Route::get('/households/{id}', [HouseholdController::class, 'show'])->whereUuid('id')->name('households.show');

    Route::post('/households/{id}/members', [HouseholdController::class, 'storeMember'])->whereUuid('id')->name('households.members.store');

    Route::post('/households/{id}/appliances', [HouseholdController::class, 'storeAppliance'])->whereUuid('id')->name('households.appliances.store');
    Route::post('/households/{id}/appliances/{applianceId}/shelves', [HouseholdController::class, 'storeShelf'])->whereUuid(['id', 'applianceId'])->name('households.appliances.shelves.store');

    Route::post('/households/{id}/food-items', [HouseholdController::class, 'storeFoodItem'])->whereUuid('id')->name('households.food-items.store');
    Route::patch('/households/{id}/food-items/{foodItemId}', [HouseholdController::class, 'updateFoodItem'])->whereUuid(['id', 'foodItemId'])->name('households.food-items.update');
    Route::delete('/households/{id}/food-items/{foodItemId}', [HouseholdController::class, 'destroyFoodItem'])->whereUuid(['id', 'foodItemId'])->name('households.food-items.destroy');

    Route::get('/households/{id}/catalogue', [HouseholdController::class, 'catalogue'])->whereUuid('id')->name('households.catalogue');
    Route::post('/households/{id}/categories', [HouseholdController::class, 'storeCategory'])->whereUuid('id')->name('households.categories.store');
    Route::delete('/households/{id}/products/{productId}', [HouseholdController::class, 'destroyProduct'])->whereUuid(['id', 'productId'])->name('households.products.destroy');
});
