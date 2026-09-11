@extends('layouts.app')

@section('title', 'Create GRN')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Goods Received Note (GRN)</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route(($routePrefix ?? 'admin') . '.grns.store') }}" method="POST" id="grnForm">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier *</label>
                    <select name="supplier_id" id="supplier_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="grn_date" class="block text-sm font-medium text-gray-700">GRN Date *</label>
                    <input type="date" name="grn_date" id="grn_date" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                           value="{{ old('grn_date', now()->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="mb-4">
                <label for="reference_document" class="block text-sm font-medium text-gray-700">Reference Document</label>
                <input type="text" name="reference_document" id="reference_document"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                       value="{{ old('reference_document') }}">
            </div>

            <div class="mb-4">
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch *</label>
                <select name="branch_id" id="branch_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Items</h3>

                <div id="itemsContainer" class="space-y-4">
                    <div class="item-row border p-4 rounded-lg bg-gray-50">
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Item *</label>
                                <select name="items[0][item_id]" required onchange="handleItemChange(0)"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->item_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantity (PCS) *</label>
                                <input type="number" name="items[0][quantity]" min="1" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                                <input type="hidden" name="items[0][quantity_unit]" value="pcs">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit Cost *</label>
                                <input type="number" step="0.01" name="items[0][unit_cost]" min="0" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                <input type="date" name="items[0][expiry_date]"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div class="mt-4" id="serial_number_container_0" style="display:none;">
                            <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                            <input type="text" name="items[0][serial_number]"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addItemRow()"
                        class="mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    + Add Item
                </button>
            </div>

            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route(($routePrefix ?? 'admin') . '.grns.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Create GRN
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let itemIndex = 1;

    const itemsData = @json($items->mapWithKeys(fn($item) => [
        $item->id => ['requires_serial_number' => $item->requires_serial_number]
    ]));

    function handleItemChange(index) {
        const itemSelect = document.querySelector(`select[name="items[${index}][item_id]"]`);
        const serialContainer = document.getElementById(`serial_number_container_${index}`);
        const itemId = itemSelect.value;

        if (itemId && itemsData[itemId] && itemsData[itemId].requires_serial_number) {
            serialContainer.style.display = 'block';
        } else {
            serialContainer.style.display = 'none';
        }
    }

    function addItemRow() {
        const container = document.getElementById('itemsContainer');

        const row = document.createElement('div');
        row.className = 'item-row border p-4 rounded-lg bg-gray-50';

        row.innerHTML = `
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Item *</label>
                    <select name="items[${itemIndex}][item_id]" required onchange="handleItemChange(${itemIndex})"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <option value="">Select Item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->item_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity (PCS) *</label>
                    <input type="number" name="items[${itemIndex}][quantity]" min="1" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <input type="hidden" name="items[${itemIndex}][quantity_unit]" value="pcs">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit Cost *</label>
                    <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" min="0" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                    <input type="date" name="items[${itemIndex}][expiry_date]"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <button type="button" onclick="this.closest('.item-row').remove()"
                            class="mt-2 text-red-600 text-sm">Remove</button>
                </div>
            </div>

            <div class="mt-4" id="serial_number_container_${itemIndex}" style="display:none;">
                <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                <input type="text" name="items[${itemIndex}][serial_number]"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
        `;

        container.appendChild(row);
        itemIndex++;
    }
</script>
@endsection

