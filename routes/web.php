<?php

use App\Http\Controllers\ApplianceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FoodItemController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\HouseholdMemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShelfController;
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

    Route::get('/households/{id}/members', [HouseholdMemberController::class, 'index'])->whereUuid('id')->name('households.members.index');

    Route::get('/households/{id}/appliances', [ApplianceController::class, 'index'])->whereUuid('id')->name('households.appliances.index');
    Route::post('/households/{id}/appliances', [ApplianceController::class, 'store'])->whereUuid('id')->name('households.appliances.store');
    Route::post('/households/{id}/appliances/{applianceId}/shelves', [ShelfController::class, 'store'])->whereUuid(['id', 'applianceId'])->name('households.appliances.shelves.store');

    Route::post('/households/{id}/food-items', [FoodItemController::class, 'store'])->whereUuid('id')->name('households.food-items.store');
    Route::patch('/households/{id}/food-items/{foodItemId}', [FoodItemController::class, 'update'])->whereUuid(['id', 'foodItemId'])->name('households.food-items.update');
    Route::delete('/households/{id}/food-items/{foodItemId}', [FoodItemController::class, 'destroy'])->whereUuid(['id', 'foodItemId'])->name('households.food-items.destroy');

    Route::get('/households/{id}/catalogue', [CatalogueController::class, 'show'])->whereUuid('id')->name('households.catalogue');
    Route::post('/households/{id}/categories', [CategoryController::class, 'store'])->whereUuid('id')->name('households.categories.store');
    Route::delete('/households/{id}/categories/{categoryId}', [CategoryController::class, 'destroy'])->whereUuid(['id', 'categoryId'])->name('households.categories.destroy');

    Route::delete('/households/{id}/products/{productId}', [ProductController::class, 'destroy'])->whereUuid(['id', 'productId'])->name('households.products.destroy');
    Route::post('/households/{id}/products', [ProductController::class, 'store'])->whereUuid('id')->name('households.products.store');

    // --- Profile Routes ---
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'show')->name('profile.show');
        Route::get('/profile/edit', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
    });
});
