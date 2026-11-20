<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to admin users when a product crosses INTO low-stock or
 * out-of-stock territory (not re-sent on every subsequent transaction
 * while it stays low — see LowStockAlertService for that logic).
 *
 * NOTE: this class does NOT implement ShouldQueue, so it sends
 * synchronously (the request waits for it to finish). For a busier
 * real-world app you'd add "implements ShouldQueue" and a queue
 * worker (php artisan queue:work) so the user isn't kept waiting on
 * an email going out. Left synchronous here to keep local testing
 * simple — no queue worker process required to see this work.
 */
class LowStockAlert extends Notification
{
    public function __construct(public Product $product)
    {
    }

    /**
     * Delivery channels: visible in the in-app notification bell (database)
     * and also emailed to the admin (mail).
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * What gets stored in the notifications table, and read by the
     * navbar bell dropdown in layouts/app.blade.php.
     */
    public function toDatabase(object $notifiable): array
    {
        $isOutOfStock = $this->product->isOutOfStock();

        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => $this->product->quantity,
            'reorder_level' => $this->product->reorder_level,
            'severity' => $isOutOfStock ? 'out-of-stock' : 'low-stock',
            'message' => $isOutOfStock
                ? "\"{$this->product->name}\" is now OUT OF STOCK."
                : "\"{$this->product->name}\" is low on stock ({$this->product->quantity} left, reorder at {$this->product->reorder_level}).",
            'url' => route('products.show', $this->product),
        ];
    }

    /**
     * The emailed version of this alert.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isOutOfStock = $this->product->isOutOfStock();

        return (new MailMessage)
            ->subject($isOutOfStock ? 'Product Out of Stock' : 'Low Stock Alert')
            ->greeting($isOutOfStock ? 'Out of Stock Alert' : 'Low Stock Alert')
            ->line("\"{$this->product->name}\" (SKU: {$this->product->sku}) needs attention.")
            ->line("Current quantity: {$this->product->quantity}")
            ->line("Reorder level: {$this->product->reorder_level}")
            ->action('View Product', route('products.show', $this->product))
            ->line('Please restock this item soon.');
    }
}