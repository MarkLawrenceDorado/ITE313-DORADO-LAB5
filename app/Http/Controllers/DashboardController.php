<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real statistics pulled from the database.
     */
    public function index()
    {
        $totalProducts = Product::count();

        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();

        // Sum of (quantity * unit_price) across every product.
        // Using DB-level sum keeps this fast even with many rows.
        $totalInventoryValue = Product::query()
            ->selectRaw('SUM(quantity * unit_price) as total')
            ->value('total') ?? 0;

        $recentProducts = Product::orderBy('created_at', 'desc')->take(5)->get();

        // Products needing attention: quantity at or below reorder level
        // (this naturally includes out-of-stock items too, since 0 is
        // always <= reorder_level).
        $lowStockProducts = Product::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->take(10)
            ->get();

        $categorySummary = Product::query()
            ->selectRaw('category, SUM(quantity * unit_price) as total_value, SUM(quantity) as total_quantity, COUNT(*) as item_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'totalInventoryValue',
            'recentProducts',
            'lowStockProducts',
            'categorySummary'
        ));
    }

    /**
     * GET /api/dashboard/stats
     * The 4 headline numbers, polled every 30s so they update live
     * without the user refreshing the page.
     */
    public function apiStats()
    {
        $totalInventoryValue = Product::query()
            ->selectRaw('SUM(quantity * unit_price) as total')
            ->value('total') ?? 0;

        return response()->json([
            'total_products' => Product::count(),
            'total_inventory_value' => round($totalInventoryValue, 2),
            'low_stock_count' => Product::lowStock()->count(),
            'out_of_stock_count' => Product::outOfStock()->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/products/low-stock
     * Products currently at or below their reorder level.
     */
    public function apiLowStock()
    {
        $products = Product::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->take(10)
            ->get(['id', 'name', 'quantity', 'reorder_level']);

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $product->quantity,
                    'reorder_level' => $product->reorder_level,
                    'status' => $product->stock_status,
                    'status_color' => $product->stock_status_color,
                    'url' => route('products.show', $product),
                ];
            }),
        ]);
    }

    /**
     * GET /api/transactions/recent
     * The latest stock movements across all products.
     */
    public function apiRecentTransactions()
    {
        $transactions = InventoryTransaction::with(['product', 'user'])
            ->recent()
            ->take(10)
            ->get();

        return response()->json([
            'transactions' => $transactions->map(function ($t) {
                return [
                    'id' => $t->id,
                    'product_name' => $t->product->name ?? 'Deleted Product',
                    'type_label' => $t->type_label,
                    'type_color' => $t->type_color,
                    'quantity_changed' => $t->quantity_changed,
                    'user_name' => $t->user->name ?? 'Unknown',
                    'created_at' => $t->created_at->diffForHumans(),
                    'url' => route('transactions.show', $t),
                ];
            }),
        ]);
    }
}