@extends('layouts.app')

@section('title', 'Stock Adjustment')

@section('content')

    <div class="pb-2 mb-3 border-bottom">
        <h2 class="mb-0">Record Stock Adjustment</h2>
        <p class="text-muted mb-0">Reconcile the physical count on the shelf against what the system has recorded.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('adjustments.store') }}" id="adjustmentForm">
                @csrf

                <div class="mb-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select name="product_id" id="product_id"
                            class="form-select @error('product_id') is-invalid @enderror" required>
                        <option value="">Select a product...</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}"
                                    data-quantity="{{ $product->quantity }}"
                                    @selected(old('product_id', $selectedProductId) == $product->id)>
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">System Count</label>
                        <input type="text" class="form-control" id="systemCountDisplay" value="—" disabled>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="physical_count" class="form-label">Physical Count</label>
                        <input type="number" name="physical_count" id="physical_count" min="0"
                               class="form-control @error('physical_count') is-invalid @enderror"
                               value="{{ old('physical_count') }}" required>
                        @error('physical_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Difference</label>
                        <input type="text" class="form-control" id="differenceDisplay" value="—" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <select name="reason" id="reason" class="form-select @error('reason') is-invalid @enderror" required>
                        <option value="">Select a reason...</option>
                        @foreach ($reasons as $value => $label)
                            <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Additional Notes <span class="text-muted">(optional)</span></label>
                    <textarea name="notes" id="notes" rows="3"
                              class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Save Adjustment</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/adjustment-form.js') }}"></script>
@endpush