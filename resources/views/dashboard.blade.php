@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="pb-2 mb-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="mb-0">
            @php
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
            @endphp
            {{ $greeting }}, {{ Auth::user()->name }}!
        </h2>
        <div class="text-muted small">
            <span class="badge bg-success"><i class="bi bi-broadcast"></i> Live</span>
            Updated <span id="lastUpdated">just now</span>
        </div>
    </div>

    {{-- Low Stock Alert Banner --}}
    @if ($lowStockProducts->isNotEmpty())
        <div class="alert alert-warning border-accent alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ $lowStockProducts->count() }} product(s) need restocking:
            {{ $lowStockProducts->take(3)->pluck('name')->join(', ') }}
            @if ($lowStockProducts->count() > 3)
                and {{ $lowStockProducts->count() - 3 }} more.
            @endif
            <a href="{{ route('products.index') }}" class="alert-link">View all products</a>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Statistic Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">📦 Total Products</h5>
                    <h2 class="card-text fw-bold text-primary-custom" id="statTotalProducts">{{ $totalProducts }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">💰 Inventory Value</h5>
                    <h2 class="card-text fw-bold text-success" id="statInventoryValue">${{ number_format($totalInventoryValue, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">⚠️ Low Stock Items</h5>
                    <h2 class="card-text fw-bold text-warning" id="statLowStock">{{ $lowStockCount }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">🚫 Out of Stock</h5>
                    <h2 class="card-text fw-bold text-danger" id="statOutOfStock">{{ $outOfStockCount }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart: Inventory Value by Category --}}
    @if ($categorySummary->isNotEmpty())
        <div class="card stat-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Inventory Value by Category</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        {{-- Recent Products --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recently Added Products</h5>
                </div>
                <div class="card-body">
                    @if ($recentProducts->isEmpty())
                        <p class="text-muted mb-0">No products yet. <a href="{{ route('products.create') }}">Add one</a>.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Qty</th>
                                        <th>Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentProducts as $product)
                                        <tr>
                                            <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                                            <td>{{ $product->category }}</td>
                                            <td>{{ $product->quantity }}</td>
                                            <td>{{ $product->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Low Stock / Out of Stock Products --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Needs Restocking</h5>
                </div>
                <div class="card-body">
                    @if ($lowStockProducts->isEmpty())
                        <p class="text-muted mb-0">Everything is well stocked. 🎉</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Qty</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="lowStockTableBody">
                                    @foreach ($lowStockProducts as $product)
                                        <tr class="{{ $product->isOutOfStock() ? 'table-danger' : 'table-warning' }}">
                                            <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                                            <td>{{ $product->quantity }}</td>
                                            <td>{{ $product->reorder_level }}</td>
                                            <td>
                                                @if ($product->isOutOfStock())
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions (live, refreshed via AJAX polling) --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>By</th>
                                    <th>When</th>
                                </tr>
                            </thead>
                            <tbody id="recentTransactionsBody">
                                {{-- Populated entirely by JS on load and every 30s after.
                                     Starts empty; dashboard-realtime.js fills it immediately. --}}
                                <tr><td colspan="5" class="text-center text-muted py-3">Loading recent activity...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@if ($categorySummary->isNotEmpty())
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        const categoryLabels = @json($categorySummary->pluck('category'));
        const categoryValues = @json($categorySummary->pluck('total_value')->map(fn($v) => round($v, 2)));

        const ctx = document.getElementById('categoryChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Inventory Value ($)',
                    data: categoryValues,
                    backgroundColor: '#122A59',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
    @endpush
@endif

@push('scripts')
    {{-- Route URLs passed via data attributes since dashboard-realtime.js
         is a plain static file and can't call Blade's route() helper. --}}
    <script
        src="{{ asset('js/dashboard-realtime.js') }}"
        data-stats-url="{{ route('api.dashboard.stats') }}"
        data-low-stock-url="{{ route('api.products.lowStock') }}"
        data-recent-transactions-url="{{ route('api.transactions.recent') }}"
        defer>
    </script>
@endpush