<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
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

    // Transaction routes (Lab 6). destroy() itself still gates on
    // is_admin inside the controller — auth middleware here just
    // ensures you're logged in at all to reach any of these.
    Route::resource('transactions', InventoryTransactionController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    // Deliberately a separate "/adjustments" prefix, NOT "/transactions/adjust":
    // the transactions resource above already owns GET /transactions/{transaction}
    // for its show route, so "/transactions/adjust" would get swallowed by that
    // wildcard (Laravel would try to route-model-bind "adjust" as an id and 404).
    Route::get('/adjustments/create', [InventoryTransactionController::class, 'adjustmentForm'])->name('adjustments.create');
    Route::post('/adjustments', [InventoryTransactionController::class, 'storeAdjustment'])->name('adjustments.store');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllRead');

    // JSON endpoints polled by dashboard-realtime.js every 30s.
    // Kept under routes/web.php (not routes/api.php) so they share the
    // same session-based auth as the rest of the app — no separate
    // token/Sanctum setup needed for this.
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'apiStats'])->name('dashboard.stats');
        Route::get('/products/low-stock', [DashboardController::class, 'apiLowStock'])->name('products.lowStock');
        Route::get('/transactions/recent', [DashboardController::class, 'apiRecentTransactions'])->name('transactions.recent');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Breeze's authentication routes (login, logout, register, password reset)
require __DIR__ . '/auth.php';