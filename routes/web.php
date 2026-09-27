<?php

use App\Http\Controllers\AccountStateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', StorefrontController::class)->name('home');
Route::get('/about', [StorefrontController::class, 'about'])->name('about');
Route::get('/login', [StorefrontController::class, 'auth'])->name('login');
Route::get('/register', [StorefrontController::class, 'auth'])->name('register');
Route::prefix('api')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/quotations', [QuotationController::class, 'store'])->middleware('throttle:10,1')->name('quotations.store');
});

Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
Route::post('/register', [SessionController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureAdmin::class])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->defaults('page', 'dashboard')->name('dashboard');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->name('products.destroy');
    Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.destroy');
    Route::delete('/orders/{quotation}', [AdminController::class, 'deleteOrder'])->name('orders.destroy');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');
    foreach (['products', 'categories', 'orders', 'users'] as $page) {
        Route::get('/'.$page, [AdminController::class, 'index'])->defaults('page', $page)->name($page);
    }
});

Route::put('/account/state', [AccountStateController::class, 'update'])->middleware('auth')->name('account.state');

Route::get('/forgot-password', [PasswordController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [PasswordController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
Route::get('/profile', [ProfileController::class, 'edit'])->middleware('auth')->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->middleware(['auth', 'throttle:10,1'])->name('profile.update');
