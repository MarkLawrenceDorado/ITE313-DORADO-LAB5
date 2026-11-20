<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of all products.
     */
    public function index()
    {
        $products = Product::orderBy('name')->get();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product added successfully.');
    }

    /**
     * Display a single product's details.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing an existing product.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update an existing product in the database.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate($this->rules($product->id), $this->messages());

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove a product from the database.
     *
     * Note: as of Lab 6, inventory_transactions.product_id uses
     * restrictOnDelete(), so MySQL itself will refuse to delete a
     * product that has any transaction history. We catch that here
     * and show a friendly message instead of a raw 500 error.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // SQLSTATE 23000 = integrity constraint violation (FK restrict)
            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('products.index')
                    ->with('error', "Cannot delete \"{$product->name}\" — it has transaction history. Products with recorded stock movements can't be deleted, to preserve the audit trail.");
            }

            throw $e; // re-throw anything else; this handler is only for the FK case
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Shared validation rules for store() and update().
     * $ignoreId lets the SKU-uniqueness check skip the current
     * product's own row when editing (so it doesn't flag itself).
     */
    private function rules(?int $ignoreId = null): array
    {
        $skuRule = 'required|string|max:50|unique:products,sku';
        if ($ignoreId) {
            $skuRule .= ',' . $ignoreId;
        }

        return [
            'name' => 'required|string|max:255',
            'sku' => $skuRule,
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom validation error messages.
     */
    private function messages(): array
    {
        return [
            'name.required' => 'Please enter a product name.',
            'sku.required' => 'A SKU is required to identify this product.',
            'sku.unique' => 'This SKU is already in use by another product.',
            'category.required' => 'Please select or enter a category.',
            'quantity.required' => 'Please enter the current stock quantity.',
            'quantity.min' => 'Quantity cannot be negative.',
            'reorder_level.min' => 'Reorder level cannot be negative.',
            'unit_price.required' => 'Please enter a unit price.',
            'unit_price.min' => 'Unit price cannot be negative.',
        ];
    }
}