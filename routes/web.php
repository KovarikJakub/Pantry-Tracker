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
use App\Http\Controllers\ConsumptionController;
use App\Http\Controllers\HouseholdShareController;
use App\Http\Controllers\AdminUserController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

// Public route
Route::get('/shared/{token}', [HouseholdShareController::class, 'show'])->name('households.share-token');

Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware(Authenticate::class)->group(function () {
    Route::get('/', [HouseholdController::class, 'home'])->name('home');
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::middleware(AuthenticateAdmin::class)->prefix('admin')->group(function () {
        Route::view('/', 'admin')->name('admin');
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::patch('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('admin.users.activate');
    });

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
    Route::patch('/households/{id}/products/{productId}', [ProductController::class, 'update'])->whereUuid(['id', 'productId'])->name('households.products.update');

    Route::post('households/{id}/share-token', [HouseholdShareController::class, 'store'])->whereUuid('id')->name('households.share-token.store');
    Route::delete('households/{id}/share-token', [HouseholdShareController::class, 'destroy'])->whereUuid('id')->name('households.share-token.destroy');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/deactivate', [ProfileController::class, 'deactivate'])->name('profile.deactivate');

    Route::get('/consumption', [ConsumptionController::class, 'index'])->name('consumption.index');
});
