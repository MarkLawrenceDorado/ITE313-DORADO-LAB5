@extends('layouts.app')

@section('title', $product->name)

@section('content')

    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom flex-wrap gap-2">
        <h2 class="mb-0">{{ $product->name }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <form action="{{ route('products.destroy', $product) }}" method="POST"
                  onsubmit="return confirm('Delete {{ $product->name }}? This cannot be undone.');" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card stat-card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">SKU</dt>
                        <dd class="col-sm-8">{{ $product->sku }}</dd>

                        <dt class="col-sm-4">Category</dt>
                        <dd class="col-sm-8">{{ $product->category }}</dd>

                        <dt class="col-sm-4">Supplier</dt>
                        <dd class="col-sm-8">{{ $product->supplier ?: '—' }}</dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $product->description ?: 'No description provided.' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Quantity in Stock</dt>
                        <dd class="col-sm-7">{{ $product->quantity }}</dd>

                        <dt class="col-sm-5">Reorder Level</dt>
                        <dd class="col-sm-7">{{ $product->reorder_level }}</dd>

                        <dt class="col-sm-5">Unit Price</dt>
                        <dd class="col-sm-7">${{ number_format($product->unit_price, 2) }}</dd>

                        <dt class="col-sm-5">Total Value</dt>
                        <dd class="col-sm-7">${{ number_format($product->totalValue(), 2) }}</dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            @if ($product->isOutOfStock())
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif ($product->isLowStock())
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Related inventory transactions (only shows if that table/model exists) --}}
    @if (class_exists(\App\Models\InventoryTransaction::class))
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Stock Movements</h5>
            </div>
            <div class="card-body">
                @php $transactions = $product->transactions()->latest()->take(10)->get(); @endphp
                @if ($transactions->isEmpty())
                    <p class="text-muted mb-0">No stock movement history recorded yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Quantity Changed</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $t)
                                    <tr>
                                        <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ ucfirst($t->transaction_type) }}</td>
                                        <td>{{ $t->quantity_changed }}</td>
                                        <td>{{ $t->reference ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

@endsection