<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard: real statistics from the database (Part 6/7)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Product CRUD routes, protected by auth middleware.
// Route::resource auto-generates all 7 routes:
//   GET    /products             products.index
//   GET    /products/create      products.create
//   POST   /products             products.store
//   GET    /products/{product}   products.show
//   GET    /products/{product}/edit  products.edit
//   PUT    /products/{product}   products.update
//   DELETE /products/{product}   products.destroy
Route::middleware('auth')->group(function () {
    Route::resource('products', ProductController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Breeze's authentication routes (login, logout, register, password reset)
require __DIR__ . '/auth.php';