@extends('layouts.app')

@section('title', $product->name)

@section('content')

    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom flex-wrap gap-2">
        <h2 class="mb-0">{{ $product->name }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('transactions.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left-right"></i> Record Transaction
            </a>
            <a href="{{ route('adjustments.create', ['product_id' => $product->id]) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-sliders"></i> Adjust Stock
            </a>
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
                            <span class="badge bg-{{ $product->stock_status_color }}">{{ $product->stock_status }}</span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction history with running balance --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transaction History</h5>
            <a href="{{ route('transactions.index', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-secondary">
                View Full History
            </a>
        </div>
        <div class="card-body">
            @php
                // Last 10 here on the product page; the "View Full History"
                // link above goes to the paginated, filterable transactions
                // index for everything beyond that.
                $transactions = $product->transactions()->with('user')->recent()->take(10)->get();
            @endphp

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
                                <th>Running Balance</th>
                                <th>Reference</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $t)
                                <tr>
                                    <td>
                                        <a href="{{ route('transactions.show', $t) }}">{{ $t->created_at->format('Y-m-d H:i') }}</a>
                                    </td>
                                    <td><span class="badge bg-{{ $t->type_color }}">{{ $t->type_label }}</span></td>
                                    <td class="{{ $t->quantity_changed > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                        {{ $t->quantity_changed > 0 ? '+' : '' }}{{ $t->quantity_changed }}
                                    </td>
                                    {{-- resulting_quantity was stored at the moment this
                                         transaction was created, so this is a true running
                                         balance with no recalculation needed. --}}
                                    <td>{{ $t->resulting_quantity }}</td>
                                    <td>{{ $t->reference_number ?: '—' }}</td>
                                    <td>{{ $t->user->name ?? 'Unknown' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection