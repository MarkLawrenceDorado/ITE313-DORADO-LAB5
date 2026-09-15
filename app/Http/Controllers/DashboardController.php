<?php

namespace App\Http\Controllers;

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
}