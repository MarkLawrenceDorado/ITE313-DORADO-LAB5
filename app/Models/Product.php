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
     */
    public function stockStatus(): string
    {
        if ($this->isOutOfStock()) return 'out-of-stock';
        if ($this->isLowStock()) return 'low-stock';
        return 'in-stock';
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
}