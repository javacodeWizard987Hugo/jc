@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-3xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Item</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route(($routePrefix ?? 'admin') . '.items.update', $item) }}" method="POST">
            @csrf
            @method('PUT')
    {{-- Item Code --}}
            <div class="mb-4">
          
                    <label class="block text-sm font-medium text-gray-700">Item Code *</label>
                    <input type="text" name="item_code" required
                           class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('item_code', $item->item_code) }}">
                </div>

             
            </div>

            {{-- Name --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Item Name *</label>
                <input type="text" name="name" required
                       class="mt-1 w-full border rounded px-3 py-2"
                       value="{{ old('name', $item->name) }}">
            </div>

            {{-- Category & Unit --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category *</label>
                    <select name="category_id" required class="mt-1 w-full border rounded px-3 py-2">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit of Measure *</label>
                    <input type="text" readonly
                           class="mt-1 w-full border rounded px-3 py-2 bg-gray-100"
                           value="PCS">
                    <input type="hidden" name="unit_of_measure" value="pcs">
                </div>
            </div>

            {{-- Prices --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cost Price *</label>
                    <input type="number" step="0.01" min="0" name="cost_price" required
                           class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('cost_price', $item->cost_price) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Selling Price *</label>
                    <input type="number" step="0.01" min="0" name="selling_price" required
                           class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('selling_price', $item->selling_price) }}">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Stock (PCS)</label>
                    <input type="number" min="0" name="current_stock"
                           class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('current_stock', $item->getBranchStock(Auth::user()->branch_id)) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Reorder Level (PCS)</label>
                    <input type="number" min="0" name="reorder_level"
                           class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('reorder_level', $item->reorder_level) }}">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">EMI Lock Mode</label>
                    <input type="text" name="emi_lock_mode"
                           class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('emi_lock_mode', $item->emi_lock_mode) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">EMI Number</label>
                    <input type="number" name="emi_number"
                           class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('emi_number', $item->emi_number) }}">
                </div>
            </div>

            {{-- Supplier --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Default Supplier</label>
                <select name="supplier_id" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Expiry --}}
          

            {{-- Flags --}}
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                    <span class="ml-2">Active</span>
                </label>
            </div>

           <!-- <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="requires_serial_number" value="1"
                        {{ old('requires_serial_number', $item->requires_serial_number) ? 'checked' : '' }}>
                    <span class="ml-2">Requires Serial Number</span>
                </label>
            </div>

            {{-- Warranty --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Warranty (Months)</label>
                <input type="number" min="0" name="warranty_duration_months"
                       class="mt-1 w-full border rounded px-3 py-2"
                       value="{{ old('warranty_duration_months', $item->warranty_duration_months) }}">
            </div>-->

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route(($routePrefix ?? 'admin') . '.items.index') }}"
                   class="px-4 py-2 border rounded">Cancel</a>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded">
                    Update Item
                </button>
            </div>
        </form>
    </div>

    {{-- Stock Adjustment (PCS only) --}}
    <div class="bg-white shadow rounded-lg p-6 mt-6">
        <h2 class="text-xl font-bold mb-4">Stock Adjustment</h2>
        <p class="mb-4">
            Current Stock:
            <span class="font-bold text-red-600">{{ $item->stock }} PCS</span>
        </p>

        <form action="{{ route(($routePrefix ?? 'admin') . '.items.stock-adjustment', $item) }}" method="POST">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium">Adjustment Type *</label>
                    <select name="adjustment_type" required class="mt-1 w-full border rounded px-3 py-2">
                        <option value="adjustment">General Adjustment</option>
                        <option value="expire">Expire</option>
                        <option value="loss">Loss / Theft</option>
                        <option value="stock_take">Stock Take</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium">Quantity (PCS) *</label>
                    <input type="number" name="adjustment_quantity" required
                           class="mt-1 w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Notes *</label>
                <textarea name="adjustment_notes" rows="3" required minlength="10"
                          class="mt-1 w-full border rounded px-3 py-2"></textarea>
            </div>

            <div class="flex justify-end">
                <button class="px-4 py-2 bg-blue-600 text-white rounded">
                    Adjust Stock
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
