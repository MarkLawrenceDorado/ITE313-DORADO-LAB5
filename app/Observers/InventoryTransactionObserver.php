<?php

namespace App\Observers;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryTransactionObserver
{
    /**
     * Handle the InventoryTransaction "creating" event.
     *
     * Using "creating" (before save) rather than "created" (after save)
     * is deliberate: it lets us reject an invalid transaction (e.g. not
     * enough stock) BEFORE anything is written to the database, instead
     * of having to delete an already-saved row to undo a mistake.
     */
    public function creating(InventoryTransaction $transaction): void
    {
        // DB::transaction() here is nested-safe (Laravel uses savepoints),
        // so this stays atomic whether or not the controller already
        // wrapped this in its own outer transaction.
        DB::transaction(function () use ($transaction) {

            // lockForUpdate() prevents a race condition: if two stock-out
            // transactions for the same product happen at nearly the same
            // moment, this ensures they can't both read the same stale
            // quantity and both succeed when only one should.
            $product = Product::where('id', $transaction->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $newQuantity = $product->quantity + $transaction->quantity_changed;

            if ($newQuantity < 0) {
                throw new InsufficientStockException(
                    $product->name,
                    $product->quantity,
                    abs($transaction->quantity_changed)
                );
            }

            $product->quantity = $newQuantity;
            $product->save();

            // Record the product's quantity AFTER this change, directly
            // on the transaction row, so transaction history can show a
            // running balance without recalculating it every time.
            $transaction->resulting_quantity = $newQuantity;
        });
    }

    /**
     * Handle the InventoryTransaction "created" event.
     * After the transaction (and the stock update above) both succeeded,
     * check whether the product just crossed into low-stock territory
     * and fire a notification if so. This is implemented in Part 6.
     */
    public function created(InventoryTransaction $transaction): void
    {
        $product = $transaction->product;

        if ($product && ($product->isLowStock() || $product->isOutOfStock())) {
            \App\Services\LowStockAlertService::maybeNotify($product, $transaction);
        }
    }

    /**
     * Handle the InventoryTransaction "deleted" event.
     * Reverses the stock change if a transaction is deleted, so the
     * product quantity stays consistent with its transaction history.
     */
    public function deleted(InventoryTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $product = Product::where('id', $transaction->product_id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                return; // product itself was already removed; nothing to reverse
            }

            // Subtract back out whatever this transaction had added.
            $reversedQuantity = $product->quantity - $transaction->quantity_changed;
            $product->quantity = max(0, $reversedQuantity);
            $product->save();
        });
    }
}