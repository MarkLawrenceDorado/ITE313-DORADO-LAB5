<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Single tabbed page covering all 3 required reports:
     * Stock Movement, Low Stock, and Inventory Valuation.
     * Kept as one route/controller method since all three are read-only
     * views over data we already have — no reason to force 3 separate
     * page loads for what's really one "Reports" screen.
     */
    public function index(Request $request)
    {
        // --- Stock Movement Report ---
        // Defaults to the last 30 days if no range is given.
        $dateFrom = $request->filled('date_from') ? $request->date_from : now()->subDays(30)->toDateString();
        $dateTo = $request->filled('date_to') ? $request->date_to : now()->toDateString();

        $movements = InventoryTransaction::with(['product', 'user'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->recent()
            ->get();

        $movementTotals = [
            'stock_in' => $movements->where('type', InventoryTransaction::TYPE_STOCK_IN)->sum('quantity_changed'),
            'stock_out' => abs($movements->where('type', InventoryTransaction::TYPE_STOCK_OUT)->sum('quantity_changed')),
            'adjustment_net' => $movements->where('type', InventoryTransaction::TYPE_ADJUSTMENT)->sum('quantity_changed'),
            'total_transactions' => $movements->count(),
        ];

        // --- Low Stock Report ---
        // Unlike the dashboard's take(10) preview, this shows every
        // product that currently needs attention — this is the full report.
        $lowStockProducts = Product::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->get();

        // --- Inventory Valuation Report ---
        $categorySummary = Product::query()
            ->selectRaw('category, SUM(quantity * unit_price) as total_value, SUM(quantity) as total_quantity, COUNT(*) as item_count')
            ->groupBy('category')
            ->orderByDesc('total_value')
            ->get();

        $grandTotalValue = $categorySummary->sum('total_value');

        return view('reports.index', compact(
            'movements',
            'movementTotals',
            'dateFrom',
            'dateTo',
            'lowStockProducts',
            'categorySummary',
            'grandTotalValue'
        ));
    }
}