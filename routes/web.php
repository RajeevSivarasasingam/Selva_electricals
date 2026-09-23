<?php

use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProductController,QuotationController};

Route::get('/', StorefrontController::class)->name('home');
Route::get('/about', [StorefrontController::class, 'about'])->name('about');
Route::get('/login', [StorefrontController::class, 'auth'])->name('login');
Route::get('/register', [StorefrontController::class, 'auth'])->name('register');
Route::prefix('api')->group(function () { Route::get('/products', [ProductController::class, 'index']); Route::get('/products/{product}', [ProductController::class, 'show']); Route::post('/quotations', [QuotationController::class, 'store']); });
