@extends('layouts.app')

@section('title', 'Create Item')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>Create New Item</h1>
            <p>Add a new product to your inventory</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="content-card p-6">
        <form action="{{ route(($routePrefix ?? 'admin') . '.items.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div class="md:col-span-2">
                    <label for="item_code" class="form-label">Item Code *</label>
                    <input type="text" name="item_code" id="item_code" required
                               class="form-input" value="{{ old('item_code', $nextItemCode) }}">
                    @error('item_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


            </div>

            <div class="mb-6">
                <label for="name" class="form-label">Item Name *</label>
                <input type="text" name="name" id="name" required
                       class="form-input" value="{{ old('name') }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="category_id" class="form-label">Category *</label>
                    <select name="category_id" id="category_id" required class="form-input">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="supplier_id" class="form-label">Default Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-input">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="cost_price" class="form-label">Cost Price (Per PCS) *</label>
                    <input type="number" step="0.01" name="cost_price" id="cost_price" required
                           class="form-input" value="{{ old('cost_price') }}">
                    @error('cost_price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="selling_price" class="form-label">Selling Price (Per PCS) *</label>
                    <input type="number" step="0.01" name="selling_price" id="selling_price" required
                           class="form-input" value="{{ old('selling_price') }}">
                    @error('selling_price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="unit_of_measure" class="form-label">Unit of Measure *</label>
                    <select name="unit_of_measure" id="unit_of_measure" required class="form-input">
                        <option value="pcs" selected>PCS (Pieces)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
               <div>
                    <label for="current_stock" class="form-label">Initial Stock (PCS) *</label>
                    <input type="number" min="0" step="1"
                           name="current_stock" id="current_stock"
                           class="form-input"
                           value="{{ old('current_stock', 1) }}" required>
                    @error('current_stock')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reorder_level" class="form-label">Reorder Level (PCS)</label>
                    <input type="number" min="0" step="1"
                           name="reorder_level" id="reorder_level"
                           class="form-input"
                           value="{{ old('reorder_level', 0) }}" required>
                    @error('reorder_level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="emi_lock_mode" class="form-label">EMI Lock Mode</label>
                    <input type="text" name="emi_lock_mode" id="emi_lock_mode"
                           class="form-input" value="{{ old('emi_lock_mode') }}">
                </div>

                <div>
                    <label for="emi_number" class="form-label">EMI Number</label>
                    <input type="number" name="emi_number" id="emi_number"
                           class="form-input" value="{{ old('emi_number') }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                 <div class="md:col-span-2">
               
                    <label for="is_active" class="form-label">Status</label>
                    <select name="is_active" id="is_active" class="form-input">
                         <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

              <!--  <div class="flex items-center">
                    <input type="checkbox" name="requires_serial_number"
                           id="requires_serial_number" value="1"
                           {{ old('requires_serial_number') ? 'checked' : '' }}
                           class="h-4 w-4 text-green-700 focus:ring-green-600 border-gray-300 rounded">
                    <label for="requires_serial_number"
                           class="ml-2 block text-sm text-gray-900">
                        Requires Serial Number
                    </label>
                </div>

                <div>
                    <label for="warranty_duration_months" class="form-label">
                        Warranty (Months)
                    </label>
                    <input type="number" min="0"
                           name="warranty_duration_months"
                           id="warranty_duration_months"
                           class="form-input"
                           value="{{ old('warranty_duration_months') }}">
                </div>
            </div>-->

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route(($routePrefix ?? 'admin') . '.items.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Create Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
