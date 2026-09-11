@extends('layouts.app')

@section('title', 'Stock Report')

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp

<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Stock Report</h1>

            {{-- 🔧 SHOW SELECTED BRANCH CLEARLY --}}
            @if($branchId)
                <p class="text-sm text-gray-500 mt-1">
                    Branch:
                    <strong>{{ $branches->firstWhere('id', $branchId)->name }}</strong>
                </p>
            @endif
        </div>

        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                🖨️ Print
            </button>
            <a href="{{ route($routePrefix . '.reports.export', 'stock') }}?format=csv&as_of_date={{ $asOfDate }}"
               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                📥 Export CSV
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route($routePrefix . '.reports.stock') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="as_of_date" class="block text-sm font-medium text-gray-700">As of Date</label>
                    <input type="date" name="as_of_date" id="as_of_date" value="{{ $asOfDate }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                    <select name="branch_id" id="branch_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" id="category_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="item_id" class="block text-sm font-medium text-gray-700">Item</label>
                    <select name="item_id" id="item_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <option value="">All Items</option>
                        @foreach($allItems as $item)
                            <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->item_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="view_type" class="block text-sm font-medium text-gray-700">View Type</label>
                    <select name="view_type" id="view_type"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <option value="item" {{ $viewType == 'item' ? 'selected' : '' }}>Item-wise</option>
                        <option value="category" {{ $viewType == 'category' ? 'selected' : '' }}>Category-wise</option>
                    </select>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Filter
                </button>
            </div>
        </form>
    </div>
     <!-- ================= SUMMARY CARDS ================= -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-600">
                <p class="text-sm font-medium text-gray-500">Total Items</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $items->count() }}
                </p>
            </div>

            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-green-600">
                <p class="text-sm font-medium text-gray-500">Total Categories</p>
                <p class="text-2xl font-semibold text-green-600">
                    {{ $itemsByCategory->count() }}
                </p>
            </div>

            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-purple-600">
                <p class="text-sm font-medium text-gray-500">Total Stock Quantity</p>
                <p class="text-2xl font-semibold text-purple-600">
                    {{ number_format($items->sum('current_stock'), 2) }}
                </p>
            </div>

            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-red-600">
                <p class="text-sm font-medium text-gray-500">Total Valuation</p>
                <p class="text-2xl font-semibold text-red-600">
                    Rs. {{ number_format($totalValuation, 2) }}
                </p>
            </div>
        </div>

    {{-- ================= ITEM VIEW ================= --}}
    @if($viewType == 'item')
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valuation</th>

                    {{-- 🔧 NEW --}}
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($items as $itemData)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono">{{ $itemData['item']->item_code }}</td>
                    <td class="px-6 py-4 text-sm font-medium">{{ $itemData['item']->name }}</td>
                    <td class="px-6 py-4 text-sm">{{ $itemData['item']->category->name }}</td>
                    <td class="px-6 py-4 text-sm">{{ number_format($itemData['current_stock'],2) }}</td>
                    <td class="px-6 py-4 text-sm">{{ $itemData['item']->unit_of_measure }}</td>
                    <td class="px-6 py-4 text-sm">Rs. {{ number_format($itemData['item']->cost_price,2) }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-red-600">
                        Rs. {{ number_format($itemData['valuation'],2) }}
                    </td>

                    {{-- 🔧 REORDER LEVEL --}}
                    <td class="px-6 py-4 text-sm">
                        {{ $branchId ? $itemData['reorder_level'] : '-' }}
                    </td>

                    {{-- 🔧 LOW STOCK STATUS --}}
                    <td class="px-6 py-4 text-sm">
                        @if($branchId && $itemData['is_low_stock'])
                            <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Low Stock</span>
                        @elseif($branchId)
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">OK</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<script>
    const allItems = @json($itemsForJs);

    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const itemSelect = document.getElementById('item_id');
        itemSelect.innerHTML = '<option value="">All Items</option>';

        allItems.forEach(item => {
            if (!categoryId || item.category_id == categoryId) {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name + ' (' + item.item_code + ')';
                itemSelect.appendChild(opt);
            }
        });
    });
</script>
@endsection
