<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            // Who/what this transaction is about.
            // restrictOnDelete(): you cannot delete a product or user that
            // still has transaction history — protects the audit trail.
            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            // 'stock_in', 'stock_out', or 'adjustment'. Kept as a string
            // (not a native DB enum) so adding a new type later never
            // requires a migration to alter the column definition.
            $table->string('type', 20);

            // Signed change applied to the product's quantity.
            // Positive = stock added, negative = stock removed.
            // (An adjustment can be either sign.)
            $table->integer('quantity_changed');

            // The product's quantity AFTER this transaction was applied —
            // a cheap way to show "running balance" history without
            // recalculating by summing every prior transaction each time.
            $table->integer('resulting_quantity');

            // e.g. a PO number, sales order number, or adjustment reference.
            $table->string('reference_number')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            // Speeds up "all transactions of type X" filtering.
            $table->index('type');

            // Speeds up "transaction history for this product, newest first"
            // which is the most common query this table will serve.
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};