<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;

class LowStockAlertService
{
    /**
     * Notify admins ONLY when this transaction causes the product to
     * cross INTO low-stock/out-of-stock territory — not on every
     * subsequent transaction while it remains low. Otherwise, selling
     * one more unit of an already-low item would re-spam every admin
     * with a near-identical alert.
     *
     * How the crossing is detected: the transaction already knows both
     * its signed change (quantity_changed) and the quantity that
     * resulted from it (resulting_quantity), so the quantity BEFORE
     * this transaction is just resulting_quantity - quantity_changed —
     * no extra query needed.
     */
    public static function maybeNotify(Product $product, InventoryTransaction $transaction): void
    {
        $oldQuantity = $transaction->resulting_quantity - $transaction->quantity_changed;
        $wasAlreadyLowOrOut = $oldQuantity <= $product->reorder_level;

        // Not a new crossing — it was already low/out before this
        // transaction too, so admins were already alerted once.
        if ($wasAlreadyLowOrOut) {
            return;
        }

        // It just crossed from "fine" into "needs attention" — notify.
        $admins = User::where('is_admin', true)->get();

        if ($admins->isEmpty()) {
            return; // nobody to notify; fail silently rather than error
        }

        foreach ($admins as $admin) {
            $admin->notify(new LowStockAlert($product));
        }
    }
}
