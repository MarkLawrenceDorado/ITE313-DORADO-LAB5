@extends('layouts.app')

@section('title', 'Reports')

@section('content')

    <div class="pb-2 mb-3 border-bottom">
        <h2 class="mb-0">Reports</h2>
    </div>

    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="movement-tab" data-bs-toggle="tab" data-bs-target="#movement-pane" type="button" role="tab">
                Stock Movement
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="lowstock-tab" data-bs-toggle="tab" data-bs-target="#lowstock-pane" type="button" role="tab">
                Low Stock ({{ $lowStockProducts->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="valuation-tab" data-bs-toggle="tab" data-bs-target="#valuation-pane" type="button" role="tab">
                Inventory Valuation
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reportTabsContent">

        {{-- Stock Movement Report --}}
        <div class="tab-pane fade show active" id="movement-pane" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Apply Range</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="card stat-card text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Total Stock In</h6>
                            <h3 class="text-success fw-bold">+{{ $movementTotals['stock_in'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card stat-card text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Total Stock Out</h6>
                            <h3 class="text-danger fw-bold">-{{ $movementTotals['stock_out'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card stat-card text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Net Adjustment</h6>
                            <h3 class="fw-bold {{ $movementTotals['adjustment_net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $movementTotals['adjustment_net'] > 0 ? '+' : '' }}{{ $movementTotals['adjustment_net'] }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $movementTotals['total_transactions'] }} Transaction(s) from {{ $dateFrom }} to {{ $dateTo }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movements as $m)
                                    <tr>
                                        <td>{{ $m->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $m->product->name ?? 'Deleted Product' }}</td>
                                        <td><span class="badge bg-{{ $m->type_color }}">{{ $m->type_label }}</span></td>
                                        <td class="{{ $m->quantity_changed > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                            {{ $m->quantity_changed > 0 ? '+' : '' }}{{ $m->quantity_changed }}
                                        </td>
                                        <td>{{ $m->user->name ?? 'Unknown' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No transactions in this date range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Low Stock Report --}}
        <div class="tab-pane fade" id="lowstock-pane" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Products Needing Attention</h5>
                </div>
                <div class="card-body">
                    @if ($lowStockProducts->isEmpty())
                        <p class="text-muted mb-0">Everything is well stocked. 🎉</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lowStockProducts as $product)
                                        <tr class="table-{{ $product->stock_status_color }}">
                                            <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                                            <td>{{ $product->sku }}</td>
                                            <td>{{ $product->category }}</td>
                                            <td>{{ $product->quantity }}</td>
                                            <td>{{ $product->reorder_level }}</td>
                                            <td><span class="badge bg-{{ $product->stock_status_color }}">{{ $product->stock_status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Inventory Valuation Report --}}
        <div class="tab-pane fade" id="valuation-pane" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Inventory Value</h6>
                    <h2 class="fw-bold text-primary-custom">${{ number_format($grandTotalValue, 2) }}</h2>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Value by Category</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Item Count</th>
                                    <th>Total Quantity</th>
                                    <th>Total Value</th>
                                    <th>% of Inventory</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categorySummary as $row)
                                    <tr>
                                        <td>{{ $row->category }}</td>
                                        <td>{{ $row->item_count }}</td>
                                        <td>{{ $row->total_quantity }}</td>
                                        <td>${{ number_format($row->total_value, 2) }}</td>
                                        <td>{{ $grandTotalValue > 0 ? number_format(($row->total_value / $grandTotalValue) * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection