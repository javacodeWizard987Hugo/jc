@extends('layouts.app')

@section('title', 'Create Stock Adjustment')

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Stock Adjustment</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route($routePrefix . '.stock-adjustments.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="item_id" class="block text-sm font-medium text-gray-700">Item *</label>
                <select name="item_id" id="item_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('item_id') border-red-500 @enderror">
                    <option value="">Select an item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" data-current-stock="{{ $item->current_stock }}" data-unit="{{ $item->unit_of_measure }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} (Current Stock: {{ $item->formatted_stock }})
                        </option>
                    @endforeach
                </select>
                @error('item_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="type" class="block text-sm font-medium text-gray-700">Adjustment Type *</label>
                <select name="type" id="type" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('type') border-red-500 @enderror">
                    <option value="">Select type</option>
                    <option value="adjustment" {{ old('type') === 'adjustment' ? 'selected' : '' }}>General Adjustment</option>
                    <option value="expire" {{ old('type') === 'expire' ? 'selected' : '' }}>Expire</option>
                    <option value="loss" {{ old('type') === 'loss' ? 'selected' : '' }}>Loss/Theft</option>
                    <option value="stock_take" {{ old('type') === 'stock_take' ? 'selected' : '' }}>Stock Take Variance</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">
                    Note: Expire, Loss, and Stock Take will reduce stock. General Adjustment can increase or decrease.
                </p>
            </div>

            <div class="mb-4">
                <label for="quantity_unit" class="block text-sm font-medium text-gray-700 mb-1">Quantity Unit *</label>
                <select name="quantity_unit" id="quantity_unit" required onchange="toggleQuantityInput()"
                        class="mb-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('quantity_unit') border-red-500 @enderror">
                    <option value="kg_g">kg g (like 1kg 400g)</option>
                </select>
                <input type="hidden" name="quantity" id="quantity_hidden" value="0">
                <div id="quantity_input_container">
                    <div id="quantity_kg_g_input" style="display: block;">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-gray-600 mb-1 block">Kilograms</label>
                                <input type="number" step="1" name="quantity_kg" id="quantity_kg" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" placeholder="kg" min="0" oninput="updateQuantityCombined()">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600 mb-1 block">Grams</label>
                                <input type="number" step="1" name="quantity_g" id="quantity_g" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" placeholder="g" min="0" max="999" oninput="updateQuantityCombined()">
                            </div>
                        </div>
                    </div>
                </div>
                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500" id="quantityHelp">
                    For expire/loss/stock_take: Enter the amount to reduce. For adjustment: Use positive to increase, negative to decrease.
                </p>
            </div>

            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes/Reason *</label>
                <textarea name="notes" id="notes" rows="4" required minlength="10"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('notes') border-red-500 @enderror"
                          placeholder="Enter detailed reason for this adjustment (minimum 10 characters)">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route($routePrefix . '.stock-adjustments.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Create Adjustment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleQuantityInput() {
    const unit = document.getElementById('quantity_unit').value;
    const kgGInput = document.getElementById('quantity_kg_g_input');
    
    // Show appropriate input
    if (unit === 'kg_g') {
        kgGInput.style.display = 'block';
    }
}

function updateQuantityCombined() {
    const unit = document.getElementById('quantity_unit').value;
    const hiddenInput = document.getElementById('quantity_hidden');
    let quantity = 0;
    
    if (unit === 'kg_g') {
        const kg = parseFloat(document.getElementById('quantity_kg').value) || 0;
        const g = parseFloat(document.getElementById('quantity_g').value) || 0;
        quantity = kg + (g / 1000); // Convert to kg for calculation
    }
    
    hiddenInput.value = quantity;
}

document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const quantityHelp = document.getElementById('quantityHelp');
    
    if (['expire', 'loss', 'stock_take'].includes(type)) {
        quantityHelp.textContent = 'Enter the amount to reduce from stock (will be subtracted).';
        // Set min for all quantity inputs
        document.getElementById('quantity_kg').min = '0';
        document.getElementById('quantity_g').min = '0';
    } else if (type === 'adjustment') {
        quantityHelp.textContent = 'Use positive number to increase stock, negative to decrease.';
        // Remove min for all quantity inputs
        document.getElementById('quantity_kg').removeAttribute('min');
        document.getElementById('quantity_g').removeAttribute('min');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleQuantityInput();
});
</script>
@endsection

