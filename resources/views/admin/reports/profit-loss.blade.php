@extends('layouts.app')

@section('title', 'Profit & Loss Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <h1 class="text-3xl font-bold text-gray-900">Profit & Loss Report</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.reports.profit-loss') }}?start_date={{ now()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" class="bg-indigo-600 text-red px-4 py-2 rounded-md hover:bg-indigo-700">
                📅 Print  Daily Profit & Loss Report
            </a>
            
        
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6 print:hidden">
        <form method="GET" action="{{ route('admin.reports.profit-loss') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-black mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-black mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-black mb-1">📁 Category</label>
                    <select name="category_id" id="category_id" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="item_id" class="block text-sm font-medium text-black mb-1">🛒 Filter by Item</label>
                    <select name="item_id" id="item_id" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->item_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        Filter
                    </button>
                </div>
            </div>
            <div class="flex gap-3 pt-2 border-t border-gray-200">
                <a href="{{ route('admin.reports.export', 'profit-loss') }}?format=csv&start_date={{ $startDate }}&end_date={{ $endDate }}&report_type=all{{ $categoryId ? '&category_id=' . $categoryId : '' }}{{ $itemId ? '&item_id=' . $itemId : '' }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                    📥 Download Summary
                </a>
                <a href="{{ route('admin.reports.export', 'profit-loss') }}?format=csv&start_date={{ $startDate }}&end_date={{ $endDate }}&report_type=item{{ $categoryId ? '&category_id=' . $categoryId : '' }}{{ $itemId ? '&item_id=' . $itemId : '' }}" 
                   class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                    📥 Download Item Wise
                </a>
                <a href="{{ route('admin.reports.export', 'profit-loss') }}?format=csv&start_date={{ $startDate }}&end_date={{ $endDate }}&report_type=detailed{{ $categoryId ? '&category_id=' . $categoryId : '' }}{{ $itemId ? '&item_id=' . $itemId : '' }}" 
                   class="bg-orange-600 text-red px-4 py-2 rounded-md hover:bg-orange-700 text-sm">
                    📥 Download Sale Wise
                </a>
            </div>
        </form>
    </div>

    <!-- Financial Summary (always shown) -->
    <div class="bg-white shadow rounded-lg p-6 mb-6 border-2 border-red-200">
        <h3 class="text-lg font-semibold mb-6 text-black">Financial Summary</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-lg font-medium text-black">Revenue</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['revenue'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-lg font-medium text-black">Cost of Goods Sold (COGS)</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['cogs'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-blue-50 rounded border-l-4 border-blue-600">
                <span class="text-lg font-semibold text-black">Gross Profit</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['gross_profit'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-sm text-black">Gross Margin</span>
                <span class="text-sm font-semibold text-black">{{ number_format($summary['gross_margin'], 2) }}%</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-lg font-medium text-black">Other Expenses</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['expenses'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-lg font-medium text-black">Stock Losses (Expired/Lost)</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['stock_losses'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 {{ $summary['net_profit'] >= 0 ? 'bg-green-50 border-green-600' : 'bg-red-50 border-red-600' }} rounded border-l-4">
                <span class="text-xl font-bold text-black">Net Profit / Loss</span>
                <span class="text-xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    Rs. {{ number_format($summary['net_profit'], 2) }}
                </span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-sm text-black">Net Margin</span>
                <span class="text-sm font-semibold text-black">{{ number_format($summary['net_margin'], 2) }}%</span>
            </div>
        </div>
    </div>

    <!-- Daily Profit & Loss Breakdown -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4 text-black">Daily Profit & Loss Breakdown</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">COGS</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Gross Profit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Expenses</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Stock Loss</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Net Profit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($dailyProfitLoss as $day)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $day['date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($day['revenue'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($day['cogs'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-black">Rs. {{ number_format($day['gross_profit'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">Rs. {{ number_format($day['expenses'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">Rs. {{ number_format($day['stock_losses'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $day['net_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            Rs. {{ number_format($day['net_profit'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Profit & Loss by Sale -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-black">Detailed Profit & Loss by Sale</h3>
            <span class="text-sm text-black">Total Bills: {{ $sales->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">COGS</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Profit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Margin</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    @php
                        $saleRevenue = 0;
                        $saleCogs = 0;
                        $filteredItems = $sale->items->filter(function($item) use ($categoryId, $itemId) {
                            if ($categoryId && (!$item->item || $item->item->category_id != $categoryId)) return false;
                            if ($itemId && $item->item_id != $itemId) return false;
                            return true;
                        });
                        
                        foreach ($filteredItems as $item) {
                            $saleRevenue += $item->total_price;
                            if ($item->item && $item->item->cost_price !== null) {
                                $saleCogs += $item->quantity * $item->item->cost_price;
                            }
                        }
                        
                        if ($filteredItems->isEmpty()) continue;

                        $saleProfit = $saleRevenue - $saleCogs;
                        $saleMargin = $saleRevenue > 0 ? ($saleProfit / $saleRevenue) * 100 : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black">#{{ $sale->invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($saleRevenue, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($saleCogs, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $saleProfit >= 0 ? 'text-black' : 'text-red-600' }}">
                            Rs. {{ number_format($saleProfit, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">{{ number_format($saleMargin, 2) }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-black">
                            <p>No sales found in the selected date range.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Item-wise Profit & Loss Report (always shown) -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-black">Item-wise Profit & Loss Report</h3>
            <span class="text-sm text-black">Total Items: {{ $itemWiseReport->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Item Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Item Code</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Quantity Sold</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Times Sold</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Avg Selling Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Total Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Total COGS</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Total Profit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Profit Margin %</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($itemWiseReport as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black">{{ $item->item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $item->item->item_code ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-semibold">{{ number_format($item->total_quantity, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">{{ $item->item->unit_of_measure }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">{{ $item->times_sold }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($item->avg_selling_price ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($item->total_revenue, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($item->total_cogs ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ ($item->total_profit ?? 0) >= 0 ? 'text-black' : 'text-red-600' }}">
                            Rs. {{ number_format($item->total_profit ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ ($item->profit_margin ?? 0) >= 0 ? 'text-black' : 'text-red-600' }}">
                            {{ number_format($item->profit_margin ?? 0, 2) }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-8 text-center text-black">
                            <div class="text-4xl mb-2">📊</div>
                            <p>No items sold in the selected date range.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($itemWiseReport->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold">
                        <td colspan="3" class="px-6 py-4 text-sm text-black">Total</td>
                        <td class="px-6 py-4 text-sm text-right text-black">{{ number_format($itemWiseReport->sum('total_quantity'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">-</td>
                        <td class="px-6 py-4 text-sm text-right text-black">{{ $itemWiseReport->sum('times_sold') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">-</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($itemWiseReport->sum('total_revenue'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($itemWiseReport->sum('total_cogs'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($itemWiseReport->sum('total_profit'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">
                            {{ $itemWiseReport->sum('total_revenue') > 0 ? number_format(($itemWiseReport->sum('total_profit') / $itemWiseReport->sum('total_revenue')) * 100, 2) : 0 }}%
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Overall Summary (always shown at bottom) -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-6 text-black">Overall Summary</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-lg font-medium text-black">Total Revenue</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['revenue'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-lg font-medium text-black">Total COGS</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['cogs'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-blue-50 rounded border-l-4 border-blue-600">
                <span class="text-lg font-semibold text-black">Gross Profit</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['gross_profit'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-sm text-black">Gross Margin</span>
                <span class="text-sm font-semibold text-black">{{ number_format($summary['gross_margin'], 2) }}%</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-lg font-medium text-black">Total Expenses</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['expenses'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-lg font-medium text-black">Total Stock Losses</span>
                <span class="text-lg font-bold text-black">Rs. {{ number_format($summary['stock_losses'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 {{ $summary['net_profit'] >= 0 ? 'bg-green-50 border-green-600' : 'bg-red-50 border-red-600' }} rounded border-l-4">
                <span class="text-xl font-bold text-black">Net Profit / Loss</span>
                <span class="text-xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    Rs. {{ number_format($summary['net_profit'], 2) }}
                </span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                <span class="text-sm text-black">Net Margin</span>
                <span class="text-sm font-semibold text-black">{{ number_format($summary['net_margin'], 2) }}%</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Store all items data for filtering
    const allItems = @json($itemsForJs);

    // Update item dropdown when category changes
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const itemSelect = document.getElementById('item_id');
        const currentItemId = itemSelect.value;
        
        // Clear existing options except "All Items"
        itemSelect.innerHTML = '<option value="">All Items</option>';
        
        // Filter and add items based on selected category
        allItems.forEach(function(item) {
            if (categoryId === '' || item.category_id == categoryId) {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name + ' (' + item.item_code + ')';
                if (item.id == currentItemId) {
                    option.selected = true;
                }
                itemSelect.appendChild(option);
            }
        });
    });
</script>
@endsection

