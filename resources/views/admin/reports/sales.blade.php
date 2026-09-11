@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <h1 class="text-3xl font-bold text-black">Sales Report</h1>
        <div class="flex flex-col gap-2">
            <div class="flex gap-2 justify-end">
                <a href="{{ route('admin.reports.export', 'sales') }}?format=pdf&report_type=detailed&start_date={{ now()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}&print=1" target="_blank" class="bg-red-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700 text-xs font-bold shadow-sm">
                    📅 Daily Report
                </a>
            </div>
            <div class="flex gap-2 justify-end">
                <a href="{{ route('admin.reports.export', 'sales') }}?format=csv&start_date={{ $startDate }}&end_date={{ $endDate }}{{ $categoryId ? '&category_id=' . $categoryId : '' }}{{ $itemId ? '&item_id=' . $itemId : '' }}{{ $itemSearch ? '&item_search=' . $itemSearch : '' }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-bold shadow-sm">
                    📥 Export Excel (CSV)
                </a>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-red-50 to-red-100 shadow-xl rounded-lg p-6 mb-6 border-2 border-red-200 print:hidden">
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <label for="start_date" class="block text-sm font-bold text-gray-700 mb-2">📅 Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                           class="block w-full px-3 py-2 border-2 border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 text-black font-semibold">
                </div>
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <label for="end_date" class="block text-sm font-bold text-gray-700 mb-2">📅 End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                           class="block w-full px-3 py-2 border-2 border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 text-black font-semibold">
                </div>
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <label for="category_id" class="block text-sm font-bold text-gray-700 mb-2">📁 Category</label>
                    <select name="category_id" id="category_id" 
                            class="block w-full px-3 py-2 border-2 border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 text-black font-semibold">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $categoryId ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <label for="item_id" class="block text-sm font-bold text-gray-700 mb-2">🛍️ Filter by Item</label>
                    <select name="item_id" id="item_id" 
                            class="block w-full px-3 py-2 border-2 border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 text-black font-semibold">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ old('item_id', $itemId ?? '') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->item_code ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <label for="item_search" class="block text-sm font-bold text-gray-700 mb-2">🔍 Item Keyword</label>
                    <input type="text" name="item_search" id="item_search" value="{{ $itemSearch }}"
                             placeholder="e.g. Samsung, Honor... (comma separated)"
                           class="block w-full px-3 py-2 border-2 border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 text-black font-semibold">
                </div>
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <label for="group_by" class="block text-sm font-bold text-gray-700 mb-2">📅 Group By</label>
                    <select name="group_by" id="group_by" 
                            class="block w-full px-3 py-2 border-2 border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 text-black font-semibold">
                        <option value="date" {{ $groupBy == 'date' ? 'selected' : '' }}>Daily</option>
                        <option value="week" {{ $groupBy == 'week' ? 'selected' : '' }}>Weekly</option>
                        <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                 <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-red-600 text-center">
            <p class="text-xs text-gray-500 font-bold uppercase">Total Sales (Net)</p>
            <p class="text-xl font-bold text-black">Rs. {{ number_format($summary['total_sales'], 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-orange-600 text-center">
            <p class="text-xs text-gray-500 font-bold uppercase">Total MRP Value</p>
            <p class="text-xl font-bold text-black">Rs. {{ number_format($summary['total_mrp'], 2) }}</p>
        </div>
      <!--  <div class="bg-white shadow rounded-lg p-4 border-l-4 border-pink-600 text-center">
            <p class="text-xs text-gray-500 font-bold uppercase">Loan Outstanding</p>
            <p class="text-xl font-bold text-black">Rs. {{ number_format($summary['total_loan_outstanding'], 2) }}</p>
        </div>-->
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-indigo-600 text-center">
            <p class="text-xs text-gray-500 font-bold uppercase">Total COGS</p>
            <p class="text-xl font-bold text-black">Rs. {{ number_format($summary['total_cogs'], 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-green-600 text-center">
            <p class="text-xs text-gray-500 font-bold uppercase">Gross Profit</p>
            <p class="text-xl font-bold text-black">Rs. {{ number_format($summary['total_profit'], 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-blue-600 text-center">
            <p class="text-xs text-gray-500 font-bold uppercase">Total Bills</p>
            <p class="text-xl font-bold text-black">{{ $summary['total_bills'] }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-purple-600 text-center">
            <p class="text-xs text-gray-500 font-bold uppercase">Items Sold</p>
            <p class="text-xl font-bold text-black">{{ $summary['total_items'] }}</p>
        </div>
       <!-- <div class="bg-white shadow rounded-lg p-4 border-l-4 border-yellow-600 text-center">
            <p class="text-xs text-gray-500 font-bold uppercase">Avg Bill</p>
            <p class="text-xl font-bold text-black">Rs. {{ number_format($summary['total_bills'] > 0 ? $summary['total_sales'] / $summary['total_bills'] : 0, 2) }}</p>
        </div>-->
    </div>

    <!-- Category-wise Sales Report -->
    @if(isset($categoryWiseReport) && $categoryWiseReport->count() > 0)
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-semibold text-black">Category-wise Sales Report</h3>
                <a href="{{ route('admin.reports.sales-category-print') }}?start_date={{ $startDate }}&end_date={{ $endDate }}{{ $categoryId ? '&category_id=' . $categoryId : '' }}{{ $itemId ? '&item_id=' . $itemId : '' }}{{ $itemSearch ? '&item_search=' . $itemSearch : '' }}" 
                   target="_blank"
                   class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">
                    🖨️ Print Category Report
                </a>
            </div>
            <span class="text-sm text-black font-bold">Total Categories: {{ $categoryWiseReport->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Category Name</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Items Count</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Total Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Total Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Total COGS</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Profit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categoryWiseReport as $index => $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-black">{{ $category->category_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">{{ $category->items_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">{{ number_format($category->total_quantity, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-bold">Rs. {{ number_format($category->total_revenue, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">Rs. {{ number_format($category->total_cogs ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-700">Rs. {{ number_format($category->total_revenue - ($category->total_cogs ?? 0), 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-black font-bold">
                            <p>No category sales found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($categoryWiseReport->count() > 0)
                <tfoot class="bg-gray-100 font-bold">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-sm text-black">TOTAL</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">{{ $categoryWiseReport->sum('items_count') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">{{ number_format($categoryWiseReport->sum('total_quantity'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">Rs. {{ number_format($categoryWiseReport->sum('total_revenue'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">Rs. {{ number_format($categoryWiseReport->sum('total_cogs'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold text-green-700">Rs. {{ number_format($categoryWiseReport->sum('total_revenue') - $categoryWiseReport->sum('total_cogs'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @endif

    <!-- Item-wise Sales Report -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-semibold text-black">Item-wise Sales Report</h3>
                <a href="{{ route('admin.reports.sales-item-print') }}?start_date={{ $startDate }}&end_date={{ $endDate }}{{ $categoryId ? '&category_id=' . $categoryId : '' }}{{ $itemId ? '&item_id=' . $itemId : '' }}{{ $itemSearch ? '&item_search=' . $itemSearch : '' }}" 
                   target="_blank"
                   class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">
                    🖨️ Print Item Report
                </a>
            </div>
            <span class="text-sm text-black font-bold">Total Items: {{ $itemWiseReport->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Item Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Item Code</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Performance</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Quantity Sold</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Times Sold</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Avg Unit Price</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Total Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Total COGS</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Profit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $itemCount = 1; @endphp
                    @forelse($itemWiseReport->groupBy(fn($i) => $i->item?->category?->name ?? 'Uncategorized') as $categoryName => $items)
                    <tr class="bg-gray-100">
                        <td colspan="11" class="px-6 py-2 text-sm font-bold text-black uppercase tracking-wider">{{ $categoryName }}</td>
                    </tr>
                    @foreach($items as $item)
                    @if($item->item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $itemCount++ }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black">{{ $item->item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $item->item->item_code ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $item->label_class }}">
                                {{ $item->performance_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">{{ number_format($item->total_quantity, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">{{ $item->item->unit_of_measure }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">{{ $item->times_sold }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">Rs. {{ number_format($item->avg_unit_price ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-bold">Rs. {{ number_format($item->total_revenue, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">Rs. {{ number_format($item->total_cogs ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-700">Rs. {{ number_format($item->total_revenue - ($item->total_cogs ?? 0), 2) }}</td>
                    </tr>
                    @endif
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-8 text-center text-black font-bold">
                            <p>No item sales found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($itemWiseReport->count() > 0)
                <tfoot class="bg-gray-100 font-bold">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-sm text-black">TOTAL</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">{{ number_format($itemWiseReport->sum('total_quantity'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">-</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">{{ $itemWiseReport->sum('times_sold') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">-</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">Rs. {{ number_format($itemWiseReport->sum('total_revenue'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold">Rs. {{ number_format($itemWiseReport->sum('total_cogs'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black font-bold text-green-700">Rs. {{ number_format($itemWiseReport->sum('total_revenue') - $itemWiseReport->sum('total_cogs'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Payment & Cashier Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 print:hidden">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-black border-b pb-2">Sales by Payment Method</h3>
            <div class="space-y-2">
                @foreach($summary['by_payment_method'] as $method => $data)
                <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                    <span class="text-sm font-bold text-gray-700 uppercase">{{ $method }}</span>
                    <span class="text-sm font-bold text-black">Rs. {{ number_format($data['amount'], 2) }} ({{ $data['count'] }} bills)</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-black border-b pb-2">Sales by Cashier</h3>
            <div class="space-y-2">
                @foreach($summary['by_cashier'] as $data)
                <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                    <span class="text-sm font-bold text-gray-700">{{ $data['cashier'] }}</span>
                    <span class="text-sm font-bold text-black">Rs. {{ number_format($data['amount'], 2) }} ({{ $data['count'] }} bills)</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Detailed Sales Report -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-black">Detailed Transaction Log</h3>
            <span class="text-sm text-black font-bold">Total Bills: {{ $sales->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">COGS</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Profit</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Payment</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    @php
                        $saleRevenue = 0;
                        $saleCogs = 0;
                          $filteredItems = $sale->items->filter(function($item) use ($categoryId, $itemId, $keywords) {
                            if ($categoryId && (!$item->item || $item->item->category_id != $categoryId)) return false;
                            if ($itemId && $item->item_id != $itemId) return false;
                          if (!empty($keywords) && $item->item) {
                                $found = false;
                                foreach ($keywords as $keyword) {
                                    if (stripos($item->item->name, $keyword) !== false) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) return false;
                            }
                            return true;
                        });
                        
                        foreach ($filteredItems as $item) {
                            $saleRevenue += $item->total_price;
                            if ($item->item && $item->item->cost_price !== null) {
                                $saleCogs += $item->quantity * $item->item->cost_price;
                            }
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-black">#{{ $sale->invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black font-semibold">{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                        <td class="px-6 py-4 text-xs text-black">
                            <ul class="list-disc list-inside">
                                @foreach($filteredItems as $item)
                                <li>{{ $item->item->name ?? 'N/A' }} ({{ (float)$item->quantity }} x {{ number_format($item->unit_price, 2) }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-black">Rs. {{ number_format($saleRevenue, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">Rs. {{ number_format($saleCogs, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-700">Rs. {{ number_format($saleRevenue - $saleCogs, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black font-bold uppercase">{{ $sale->payment_method }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-black font-bold">
                            <p>No transactions found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('print')) {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    });
</script>
@endsection
