@extends('layouts.app')

@section('title', 'Record Transaction')

@section('content')

    <div class="pb-2 mb-3 border-bottom">
        <h2 class="mb-0">Record Inventory Transaction</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('transactions.store') }}" id="transactionForm">
                @csrf

                <div class="mb-3">
                    <label for="product_id" class="form-label">Product</label>
                    {{-- data-quantity carries each product's current stock so the
                         JS below can show it instantly, with no AJAX call needed. --}}
                    <select name="product_id" id="product_id"
                            class="form-select @error('product_id') is-invalid @enderror" required>
                        <option value="">Select a product...</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}"
                                    data-quantity="{{ $product->quantity }}"
                                    data-name="{{ $product->name }}"
                                    @selected(old('product_id') == $product->id)>
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text" id="currentStockDisplay"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Transaction Type</label>
                    <div class="btn-group" role="group" aria-label="Transaction type">
                        @foreach ($types as $type)
                            <input type="radio" class="btn-check" name="type" id="type_{{ $type }}"
                                   value="{{ $type }}" autocomplete="off"
                                   @checked(old('type', 'stock_in') === $type) required>
                            <label class="btn btn-outline-primary" for="type_{{ $type }}">
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </label>
                        @endforeach
                    </div>
                    @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity"
                               class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity') }}" required>
                        <div class="form-text" id="quantityHint">
                            For Stock In / Stock Out, enter a positive number. For Adjustment, enter the signed difference (e.g. -3 or 5).
                        </div>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="reference_number" class="form-label">Reference Number <span class="text-muted">(optional)</span></label>
                        <input type="text" name="reference_number" id="reference_number"
                               class="form-control @error('reference_number') is-invalid @enderror"
                               value="{{ old('reference_number') }}" placeholder="e.g. PO-1042, SO-2208">
                        @error('reference_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes <span class="text-muted">(optional)</span></label>
                    <textarea name="notes" id="notes" rows="3"
                              class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/transaction-form.js') }}"></script>
@endpush