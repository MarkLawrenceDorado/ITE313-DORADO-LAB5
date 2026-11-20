<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes.
     * Required so create()/update() work with request data
     * without Laravel blocking it for security.
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'quantity',
        'reorder_level',
        'unit_price',
        'supplier',
    ];

    /**
     * Cast attributes to native types automatically.
     */
    protected $casts = [
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * A product can have many inventory transactions
     * (stock in / stock out movements).
     */
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * The single most recent transaction for this product.
     * ofMany('created_at', 'max') tells Eloquent which row of the
     * hasMany relationship above counts as "latest" when eager loading.
     */
    public function latestTransaction()
    {
        return $this->hasOne(InventoryTransaction::class)->ofMany('created_at', 'max');
    }

    /**
     * Business logic: is this product at or below its reorder level?
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level && $this->quantity > 0;
    }

    /**
     * Business logic: is this product completely out of stock?
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Business logic: human-readable stock status, used for badges in views.
     * (Kept as a plain method from Lab 5 for backward compatibility with
     * existing Blade views that already call $product->stockStatus().)
     */
    public function stockStatus(): string
    {
        if ($this->isOutOfStock()) return 'out-of-stock';
        if ($this->isLowStock()) return 'low-stock';
        return 'in-stock';
    }

    /**
     * Accessor version of the above, as the lab asks for:
     * $product->stock_status (not stockStatus()) — a human-readable label.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) return 'Out of Stock';
        if ($this->isLowStock()) return 'Low Stock';
        return 'In Stock';
    }

    /**
     * Accessor: Bootstrap color class matching the current stock status,
     * e.g. for <span class="badge bg-{{ $product->stock_status_color }}">
     */
    public function getStockStatusColorAttribute(): string
    {
        if ($this->isOutOfStock()) return 'danger';
        if ($this->isLowStock()) return 'warning';
        return 'success';
    }

    /**
     * Business logic: total value of this product's current stock.
     */
    public function totalValue(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Query scope: only products at or below reorder level (and > 0).
     * Usage: Product::lowStock()->get();
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level')->where('quantity', '>', 0);
    }

    /**
     * Query scope: only products with zero quantity.
     * Usage: Product::outOfStock()->get();
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Query scope: only products in a specific category.
     * Usage: Product::inCategory('Lumber')->get();
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}