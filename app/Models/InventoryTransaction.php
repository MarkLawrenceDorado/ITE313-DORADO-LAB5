<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    // Transaction type constants, used throughout the app instead of
    // typing raw strings ('stock_in', etc.) and risking a typo.
    const TYPE_STOCK_IN = 'stock_in';
    const TYPE_STOCK_OUT = 'stock_out';
    const TYPE_ADJUSTMENT = 'adjustment';

    const TYPES = [
        self::TYPE_STOCK_IN,
        self::TYPE_STOCK_OUT,
        self::TYPE_ADJUSTMENT,
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity_changed',
        'resulting_quantity',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'quantity_changed' => 'integer',
        'resulting_quantity' => 'integer',
    ];

    /**
     * The product this transaction moved stock for.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The user who performed/authorized this transaction.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scope: only stock-in transactions.
     * Usage: InventoryTransaction::stockIn()->get();
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', self::TYPE_STOCK_IN);
    }

    /**
     * Scope: only stock-out transactions.
     * Usage: InventoryTransaction::stockOut()->get();
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', self::TYPE_STOCK_OUT);
    }

    /**
     * Scope: only adjustment transactions.
     */
    public function scopeAdjustment($query)
    {
        return $query->where('type', self::TYPE_ADJUSTMENT);
    }

    /**
     * Scope: newest first (the order transaction history is almost
     * always displayed in).
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Human-readable label for the type, used in views/badges.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_STOCK_IN => 'Stock In',
            self::TYPE_STOCK_OUT => 'Stock Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    /**
     * Bootstrap badge color class for the type, used in views.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_STOCK_IN => 'success',
            self::TYPE_STOCK_OUT => 'danger',
            self::TYPE_ADJUSTMENT => 'warning',
            default => 'secondary',
        };
    }
}