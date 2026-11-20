@extends('layouts.app')

@section('title', 'Transactions')

@section('content')

    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom flex-wrap gap-2">
        <h2 class="mb-0">Inventory Transactions</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Record Transaction
            </a>
            <a href="{{ route('adjustments.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-sliders"></i> Adjust Stock
            </a>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('transactions.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select name="product_id" id="product_id" class="form-select">
                        <option value="">All Products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="stock_in" @selected(request('type') === 'stock_in')>Stock In</option>
                        <option value="stock_out" @selected(request('type') === 'stock_out')>Stock Out</option>
                        <option value="adjustment" @selected(request('type') === 'adjustment')>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reference #</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('products.show', $transaction->product) }}">
                                        {{ $transaction->product->name ?? 'Deleted Product' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type_color }}">{{ $transaction->type_label }}</span>
                                </td>
                                <td>
                                    @if ($transaction->quantity_changed > 0)
                                        <span class="text-success fw-bold">+{{ $transaction->quantity_changed }}</span>
                                    @else
                                        <span class="text-danger fw-bold">{{ $transaction->quantity_changed }}</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->reference_number ?: '—' }}</td>
                                <td>{{ $transaction->user->name ?? 'Unknown' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if (auth()->user()?->is_admin)
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                                                  onsubmit="return confirm('Delete this transaction? This will reverse its stock change.');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No transactions found. <a href="{{ route('transactions.create') }}">Record your first one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

@endsection