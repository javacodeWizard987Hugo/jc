@extends('layouts.app')

@section('title', 'Stock Adjustments')

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Stock Adjustments</h1>
        <a href="{{ route($routePrefix . '.stock-adjustments.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
            New Adjustment
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance After</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="stockAdjustmentsTableBody">
                @forelse($adjustments as $adjustment)
                    <tr data-adjustment-id="{{ $adjustment->id }}" data-adjustment-timestamp="{{ $adjustment->created_at->timestamp }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->item->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($adjustment->type === 'expire') bg-red-100 text-red-800
                                @elseif($adjustment->type === 'loss') bg-orange-100 text-orange-800
                                @elseif($adjustment->type === 'stock_take') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $adjustment->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 quantity-cell" 
                            data-quantity="{{ $adjustment->quantity }}" 
                            data-item-unit="{{ $adjustment->item->unit_of_measure }}">
                            <span class="{{ $adjustment->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $adjustment->quantity > 0 ? '+' : '' }}{{ \App\Models\Item::formatStock(abs($adjustment->quantity), $adjustment->item->unit_of_measure) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 balance-cell" 
                            data-balance="{{ $adjustment->balance_after }}" 
                            data-item-unit="{{ $adjustment->item->unit_of_measure }}">
                            {{ \App\Models\Item::formatStock($adjustment->balance_after, $adjustment->item->unit_of_measure) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->creator->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route($routePrefix . '.stock-adjustments.show', $adjustment) }}" class="text-red-600 hover:text-red-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No stock adjustments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $adjustments->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const routePrefix = '{{ $routePrefix }}';
    const apiUrl = `/${routePrefix}/stock-adjustments/api`;
    let lastUpdateTimestamp = Math.max(...Array.from(document.querySelectorAll('[data-adjustment-timestamp]')).map(el => parseInt(el.getAttribute('data-adjustment-timestamp')) || 0), 0);
    let updateInterval = null;
    
    // Format stock for display
    function formatStockForDisplay(stock, unit) {
        let wholeKg = 0;
        let remainingGrams = 0;
        
        if (unit === 'kg') {
            wholeKg = Math.floor(stock);
            remainingGrams = Math.round((stock - wholeKg) * 1000);
        } else if (unit === 'g') {
            wholeKg = Math.floor(stock / 1000);
            remainingGrams = Math.round(stock % 1000);
        } else {
            wholeKg = Math.floor(stock);
            remainingGrams = Math.round((stock - wholeKg) * 1000);
        }
        
        const gFormatted = String(remainingGrams).padStart(3, '0');
        if (wholeKg === 0 && remainingGrams === 0) {
            return '0kg 000g';
        } else {
            return wholeKg + 'kg ' + gFormatted + 'g';
        }
    }
    
    // Get type badge class
    function getTypeBadgeClass(type) {
        if (type === 'expire') {
            return 'bg-red-100 text-red-800';
        } else if (type === 'loss') {
            return 'bg-orange-100 text-orange-800';
        } else if (type === 'stock_take') {
            return 'bg-blue-100 text-blue-800';
        } else {
            return 'bg-gray-100 text-gray-800';
        }
    }
    
    // Add new adjustment row
    function addAdjustmentRow(adjustment) {
        const tbody = document.getElementById('stockAdjustmentsTableBody');
        const existingEmptyRow = tbody.querySelector('td[colspan="7"]');
        if (existingEmptyRow) {
            existingEmptyRow.closest('tr').remove();
        }
        
        const row = document.createElement('tr');
        row.setAttribute('data-adjustment-id', adjustment.id);
        row.setAttribute('data-adjustment-timestamp', adjustment.created_at_timestamp);
        
        const quantityFormatted = formatStockForDisplay(Math.abs(adjustment.quantity), adjustment.item_unit);
        const balanceFormatted = formatStockForDisplay(adjustment.balance_after, adjustment.item_unit);
        const quantityColor = adjustment.quantity < 0 ? 'text-red-600' : 'text-green-600';
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${adjustment.created_at}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${adjustment.item_name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${getTypeBadgeClass(adjustment.type)}">
                    ${adjustment.type.charAt(0).toUpperCase() + adjustment.type.slice(1).replace('_', ' ')}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 quantity-cell" 
                data-quantity="${adjustment.quantity}" 
                data-item-unit="${adjustment.item_unit}">
                <span class="${quantityColor}">
                    ${adjustment.quantity > 0 ? '+' : ''}${quantityFormatted}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 balance-cell" 
                data-balance="${adjustment.balance_after}" 
                data-item-unit="${adjustment.item_unit}">
                ${balanceFormatted}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${adjustment.creator_name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="/${routePrefix}/stock-adjustments/${adjustment.id}" class="text-red-600 hover:text-red-900">View</a>
            </td>
        `;
        
        tbody.insertBefore(row, tbody.firstChild);
        
        // Highlight animation
        row.style.backgroundColor = '#fef3c7';
        setTimeout(() => {
            row.style.transition = 'background-color 2s';
            row.style.backgroundColor = '';
        }, 100);
    }
    
    // Update existing row
    function updateAdjustmentRow(row, adjustment) {
        const quantityFormatted = formatStockForDisplay(Math.abs(adjustment.quantity), adjustment.item_unit);
        const balanceFormatted = formatStockForDisplay(adjustment.balance_after, adjustment.item_unit);
        const quantityColor = adjustment.quantity < 0 ? 'text-red-600' : 'text-green-600';
        
        // Update quantity cell
        const quantityCell = row.querySelector('.quantity-cell');
        if (quantityCell) {
            quantityCell.setAttribute('data-quantity', adjustment.quantity);
            quantityCell.setAttribute('data-item-unit', adjustment.item_unit);
            quantityCell.innerHTML = `<span class="${quantityColor}">${adjustment.quantity > 0 ? '+' : ''}${quantityFormatted}</span>`;
        }
        
        // Update balance cell
        const balanceCell = row.querySelector('.balance-cell');
        if (balanceCell) {
            balanceCell.setAttribute('data-balance', adjustment.balance_after);
            balanceCell.setAttribute('data-item-unit', adjustment.item_unit);
            balanceCell.textContent = balanceFormatted;
        }
        
        row.setAttribute('data-adjustment-timestamp', adjustment.created_at_timestamp);
    }
    
    // Fetch latest adjustments
    async function fetchAdjustments() {
        try {
            const response = await fetch(apiUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch adjustments');
            }
            
            const data = await response.json();
            
            // Create map of existing rows
            const existingRows = new Map();
            document.querySelectorAll('[data-adjustment-id]').forEach(row => {
                const id = row.getAttribute('data-adjustment-id');
                existingRows.set(parseInt(id), row);
            });
            
            // Process adjustments
            const tbody = document.getElementById('stockAdjustmentsTableBody');
            const existingEmptyRow = tbody.querySelector('td[colspan="7"]');
            if (existingEmptyRow && data.adjustments.length > 0) {
                existingEmptyRow.closest('tr').remove();
            }
            
            const sortedAdjustments = [...data.adjustments].sort((a, b) => b.created_at_timestamp - a.created_at_timestamp);
            
            sortedAdjustments.forEach(adjustment => {
                const existingRow = existingRows.get(adjustment.id);
                
                if (existingRow) {
                    updateAdjustmentRow(existingRow, adjustment);
                } else {
                    addAdjustmentRow(adjustment);
                }
            });
            
            if (sortedAdjustments.length > 0) {
                lastUpdateTimestamp = Math.max(...sortedAdjustments.map(a => a.created_at_timestamp));
            }
        } catch (error) {
            console.error('Error fetching adjustments:', error);
        }
    }
    
    // Start polling every 3 seconds
    updateInterval = setInterval(fetchAdjustments, 3000);
    fetchAdjustments();
    
    // Clean up when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }
        } else {
            if (!updateInterval) {
                updateInterval = setInterval(fetchAdjustments, 3000);
                fetchAdjustments();
            }
        }
    });
});
</script>
@endsection

