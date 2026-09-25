<?php

use App\Http\Controllers\AccountStateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;
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
    Route::post('/quotations', [QuotationController::class, 'store']);
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
