@extends('layouts.app')

@section('title', 'Products')

@section('content')

    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom flex-wrap gap-2">
        <h2 class="mb-0">Products</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Product
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="@if($product->isOutOfStock()) table-danger @elseif($product->isLowStock()) table-warning @endif">
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category }}</td>
                                <td>{{ $product->quantity }}</td>
                                <td>${{ number_format($product->unit_price, 2) }}</td>
                                <td>${{ number_format($product->totalValue(), 2) }}</td>
                                <td>
                                    @if ($product->isOutOfStock())
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif ($product->isLowStock())
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST"
                                              onsubmit="return confirm('Delete {{ $product->name }}? This cannot be undone.');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No products yet. <a href="{{ route('products.create') }}">Add your first product</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection