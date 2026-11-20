<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    /**
     * Display a paginated, filterable list of all transactions.
     */
    public function index(Request $request)
    {
        $query = InventoryTransaction::with(['product', 'user'])->recent();

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'products'));
    }

    /**
     * Show the form for recording a new transaction.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();
        $types = InventoryTransaction::TYPES;

        return view('transactions.create', compact('products', 'types'));
    }

    /**
     * Validate and store a new transaction.
     * The actual stock update happens automatically via
     * InventoryTransactionObserver::creating().
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:' . implode(',', InventoryTransaction::TYPES),
            'quantity' => 'required|integer|not_in:0',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ], [
            'quantity.not_in' => 'Quantity cannot be zero.',
        ]);

        // stock_in / stock_out require a positive magnitude from the user
        // (the sign is then applied automatically below); adjustments can
        // be entered as a signed value directly (e.g. -3 or +5).
        if (in_array($validated['type'], [InventoryTransaction::TYPE_STOCK_IN, InventoryTransaction::TYPE_STOCK_OUT])
            && $validated['quantity'] < 1) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => 'Quantity must be a positive number for stock in/out transactions.']);
        }

        // Custom validation (the lab's "after" hook approach): for a
        // stock-out, confirm there's actually enough stock BEFORE even
        // attempting to create the transaction. This gives a clean error
        // message; the Observer's own check is the final, race-condition-safe
        // backstop in case stock changed between this check and the save.
        if ($validated['type'] === InventoryTransaction::TYPE_STOCK_OUT) {
            $product = Product::findOrFail($validated['product_id']);
            if ($product->quantity < $validated['quantity']) {
                return back()
                    ->withInput()
                    ->withErrors(['quantity' => "Insufficient stock: only {$product->quantity} units of \"{$product->name}\" available."]);
            }
        }

        // Convert the user-entered magnitude into the signed value the
        // Observer expects: positive for stock in, negative for stock out,
        // and pass adjustments through as-is (already signed by the form).
        $quantityChanged = match ($validated['type']) {
            InventoryTransaction::TYPE_STOCK_IN => abs($validated['quantity']),
            InventoryTransaction::TYPE_STOCK_OUT => -abs($validated['quantity']),
            InventoryTransaction::TYPE_ADJUSTMENT => $validated['quantity'],
        };

        try {
            DB::transaction(function () use ($validated, $quantityChanged) {
                InventoryTransaction::create([
                    'product_id' => $validated['product_id'],
                    'user_id' => auth()->id(),
                    'type' => $validated['type'],
                    'quantity_changed' => $quantityChanged,
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    // resulting_quantity is set automatically by the Observer
                ]);
            });
        } catch (InsufficientStockException $e) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    /**
     * Show the stock adjustment form.
     * Unlike the generic transaction form, this asks for the PHYSICAL
     * COUNT staff just took (what's actually on the shelf), not a raw
     * signed difference — the system calculates the difference itself,
     * matching how a real inventory reconciliation is actually done.
     */
    public function adjustmentForm(Request $request)
    {
        $products = Product::orderBy('name')->get();
        $selectedProductId = $request->query('product_id');

        $reasons = [
            'damaged' => 'Damaged / Unsellable',
            'theft_loss' => 'Theft or Loss',
            'miscount_found_more' => 'Miscount — Found More Than Recorded',
            'miscount_found_less' => 'Miscount — Found Less Than Recorded',
            'other' => 'Other',
        ];

        return view('transactions.adjust', compact('products', 'reasons', 'selectedProductId'));
    }

    /**
     * Validate and store a stock adjustment.
     * Computes the signed difference from the physical count, then
     * creates the transaction the same way store() does — the Observer
     * handles the actual stock update and audit trail either way.
     */
    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'physical_count' => 'required|integer|min:0',
            'reason' => 'required|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $difference = $validated['physical_count'] - $product->quantity;

        // Counts already match — nothing to record. Deliberately NOT
        // creating a zero-quantity_changed transaction here, since that
        // would violate the Observer/validation rule that every
        // transaction must represent an actual change.
        if ($difference === 0) {
            return back()
                ->withInput()
                ->with('info', "No adjustment needed — the physical count matches the system count ({$product->quantity}) for \"{$product->name}\".");
        }

        $reasonLabels = [
            'damaged' => 'Damaged / Unsellable',
            'theft_loss' => 'Theft or Loss',
            'miscount_found_more' => 'Miscount — Found More Than Recorded',
            'miscount_found_less' => 'Miscount — Found Less Than Recorded',
            'other' => 'Other',
        ];
        $reasonLabel = $reasonLabels[$validated['reason']] ?? ucfirst($validated['reason']);

        $notes = "Reason: {$reasonLabel}. Physical count: {$validated['physical_count']} (was {$product->quantity}).";
        if (!empty($validated['notes'])) {
            $notes .= ' ' . $validated['notes'];
        }

        try {
            DB::transaction(function () use ($validated, $difference, $notes) {
                InventoryTransaction::create([
                    'product_id' => $validated['product_id'],
                    'user_id' => auth()->id(),
                    'type' => InventoryTransaction::TYPE_ADJUSTMENT,
                    'quantity_changed' => $difference,
                    'reference_number' => null,
                    'notes' => $notes,
                ]);
            });
        } catch (InsufficientStockException $e) {
            // Shouldn't normally happen for an adjustment (physical_count
            // is validated as >= 0), but kept as a defensive backstop.
            return back()->withInput()->withErrors(['physical_count' => $e->getMessage()]);
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', "Stock adjustment recorded for \"{$product->name}\" (difference: " . ($difference > 0 ? '+' : '') . "{$difference}).");
    }

    /**
     * Display a single transaction's full details.
     */
    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['product', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Delete a transaction. Admin-only.
     * The Observer's deleted() hook automatically reverses the stock
     * change this transaction had applied.
     */
    public function destroy(InventoryTransaction $transaction)
    {
        abort_unless(auth()->user()?->is_admin, 403, 'Only administrators can delete transactions.');

        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction deleted and stock change reversed.');
    }
}