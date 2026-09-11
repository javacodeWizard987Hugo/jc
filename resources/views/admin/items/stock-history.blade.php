@extends('layouts.app')

@section('title', 'Stock History - ' . $item->name)

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route($routePrefix . '.items.index') }}" class="text-red-600 hover:text-red-900 mb-4 inline-block">← Back to Items</a>
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Stock History: {{ $item->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Current Stock: 
                    <span class="font-semibold" id="currentStockDisplay"></span>
                    <span class="ml-2 text-xs text-gray-400" id="lastUpdateTime"></span>
                </p>
            </div>
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                <select name="branch_id" id="branch_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    @foreach(\App\Models\Branch::all() as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Display unit is always kg_g format -->
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="stockHistoryTableBody">
                @forelse($movements as $movement)
                <tr data-movement-id="{{ $movement->id }}" data-movement-timestamp="{{ $movement->created_at->timestamp }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $movement->type === 'grn' ? 'bg-green-100 text-green-800' : 
                               ($movement->type === 'sale' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($movement->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm quantity-cell {{ $movement->quantity > 0 ? 'text-green-600' : 'text-red-600' }}" 
                        data-quantity="{{ $movement->quantity }}" 
                        data-item-unit="{{ $item->unit_of_measure }}">
                        {{ $movement->quantity > 0 ? '+' : '' }}{{ \App\Models\Item::formatStock(abs($movement->quantity), $item->unit_of_measure) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold balance-cell" 
                        data-balance="{{ $movement->balance_after }}" 
                        data-item-unit="{{ $item->unit_of_measure }}">
                        {{ \App\Models\Item::formatStock($movement->balance_after, $item->unit_of_measure) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $movement->creator->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $movement->notes }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No stock movements found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $movements->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityCells = document.querySelectorAll('.quantity-cell');
    const balanceCells = document.querySelectorAll('.balance-cell');
    const itemId = {{ $item->id }};
    const branchSelect = document.getElementById('branch_id');
    const routePrefix = '{{ request()->route()->getName() }}'.includes('cashier.') ? 'cashier' : 'admin';
    let apiUrl = `/${routePrefix}/items/${itemId}/stock-history/api?branch_id=${branchSelect.value}`;
    let lastUpdateTimestamp = Math.max(...Array.from(document.querySelectorAll('[data-movement-timestamp]')).map(el => parseInt(el.getAttribute('data-movement-timestamp')) || 0), 0);
    let updateInterval = null;

    branchSelect.addEventListener('change', function() {
        apiUrl = `/${routePrefix}/items/${itemId}/stock-history/api?branch_id=${this.value}`;
        fetchStockHistory();
    });
    
    // Format stock for display
    function formatStockForDisplay(stock, unit) {
        return parseInt(stock) + ' PCS';
    }
    
    // Get type badge class
    function getTypeBadgeClass(type) {
        if (type === 'grn') {
            return 'bg-green-100 text-green-800';
        } else if (type === 'sale') {
            return 'bg-red-100 text-red-800';
        } else {
            return 'bg-blue-100 text-blue-800';
        }
    }
    
    // Add new movement row to table
    function addMovementRow(movement, itemUnit) {
        const tbody = document.getElementById('stockHistoryTableBody');
        const existingEmptyRow = tbody.querySelector('td[colspan="6"]');
        if (existingEmptyRow) {
            existingEmptyRow.closest('tr').remove();
        }
        
        const row = document.createElement('tr');
        row.setAttribute('data-movement-id', movement.id);
        row.setAttribute('data-movement-timestamp', movement.created_at_timestamp);
        
        const quantityFormatted = formatStockForDisplay(Math.abs(movement.quantity), itemUnit);
        const balanceFormatted = formatStockForDisplay(movement.balance_after, itemUnit);
        const quantityColor = movement.quantity > 0 ? 'text-green-600' : 'text-red-600';
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm">${movement.created_at}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getTypeBadgeClass(movement.type)}">
                    ${movement.type.charAt(0).toUpperCase() + movement.type.slice(1)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm quantity-cell ${quantityColor}" 
                data-quantity="${movement.quantity}" 
                data-item-unit="${itemUnit}">
                ${movement.quantity > 0 ? '+' : ''}${quantityFormatted}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold balance-cell" 
                data-balance="${movement.balance_after}" 
                data-item-unit="${itemUnit}">
                ${balanceFormatted}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">${movement.creator_name}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${movement.notes || ''}</td>
        `;
        
        // Insert at the top of the table
        tbody.insertBefore(row, tbody.firstChild);
        
        // Add highlight animation
        row.style.backgroundColor = '#fef3c7';
        setTimeout(() => {
            row.style.transition = 'background-color 2s';
            row.style.backgroundColor = '';
        }, 100);
    }
    
    // Update current stock display
    function updateCurrentStock(formattedStock) {
        const stockDisplay = document.getElementById('currentStockDisplay');
        if (stockDisplay) {
            stockDisplay.textContent = formattedStock;
        }
    }
    
    // Update last update time
    function updateLastUpdateTime() {
        const timeDisplay = document.getElementById('lastUpdateTime');
        if (timeDisplay) {
            const now = new Date();
            timeDisplay.textContent = `(Updated: ${now.toLocaleTimeString()})`;
        }
    }
    
    // Update existing row with new data
    function updateMovementRow(row, movement, itemUnit) {
        const quantityFormatted = formatStockForDisplay(Math.abs(movement.quantity), itemUnit);
        const balanceFormatted = formatStockForDisplay(movement.balance_after, itemUnit);
        const quantityColor = movement.quantity > 0 ? 'text-green-600' : 'text-red-600';
        
        // Update quantity cell
        const quantityCell = row.querySelector('.quantity-cell');
        if (quantityCell) {
            quantityCell.setAttribute('data-quantity', movement.quantity);
            quantityCell.setAttribute('data-item-unit', itemUnit);
            // Update class and text content
            quantityCell.className = `px-6 py-4 whitespace-nowrap text-sm quantity-cell ${quantityColor}`;
            quantityCell.textContent = (movement.quantity > 0 ? '+' : '') + quantityFormatted;
        }
        
        // Update balance cell
        const balanceCell = row.querySelector('.balance-cell');
        if (balanceCell) {
            balanceCell.setAttribute('data-balance', movement.balance_after);
            balanceCell.setAttribute('data-item-unit', itemUnit);
            balanceCell.textContent = balanceFormatted;
        }
        
        // Update timestamp attribute
        row.setAttribute('data-movement-timestamp', movement.created_at_timestamp);
    }
    
    // Fetch latest stock history
    async function fetchStockHistory() {
        try {
            const response = await fetch(apiUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch stock history');
            }
            
            const data = await response.json();
            
            // Update current stock
            updateCurrentStock(data.formatted_stock);
            updateLastUpdateTime();
            
            // Create a map of existing movement IDs to rows
            const existingRows = new Map();
            document.querySelectorAll('[data-movement-id]').forEach(row => {
                const movementId = row.getAttribute('data-movement-id');
                existingRows.set(parseInt(movementId), row);
            });
            
            // Create a set of movement IDs from API
            const apiMovementIds = new Set(data.movements.map(m => m.id));
            
            // Remove rows that no longer exist in API (shouldn't happen, but just in case)
            existingRows.forEach((row, id) => {
                if (!apiMovementIds.has(id)) {
                    row.remove();
                    existingRows.delete(id);
                }
            });
            
            // Process all movements from API
            const tbody = document.getElementById('stockHistoryTableBody');
            const existingEmptyRow = tbody.querySelector('td[colspan="6"]');
            if (existingEmptyRow && data.movements.length > 0) {
                existingEmptyRow.closest('tr').remove();
            }
            
            // Sort movements by timestamp descending (newest first)
            const sortedMovements = [...data.movements].sort((a, b) => b.created_at_timestamp - a.created_at_timestamp);
            
            // Track which rows we've processed
            const processedIds = new Set();
            
            // Update existing rows or add new ones
            sortedMovements.forEach((movement, index) => {
                const existingRow = existingRows.get(movement.id);
                
                if (existingRow) {
                    // Update existing row with latest data (this updates quantity and balance_after)
                    updateMovementRow(existingRow, movement, data.unit_of_measure);
                    existingRow.setAttribute('data-movement-timestamp', movement.created_at_timestamp);
                    processedIds.add(movement.id);
                } else {
                    // Add new row
                    addMovementRow(movement, data.unit_of_measure);
                    processedIds.add(movement.id);
                }
            });
            
            // Reorder rows to match sorted order (newest first)
            sortedMovements.forEach((movement, index) => {
                const row = document.querySelector(`[data-movement-id="${movement.id}"]`);
                if (row && index < tbody.children.length) {
                    const targetPosition = index;
                    const currentPosition = Array.from(tbody.children).indexOf(row);
                    if (currentPosition !== targetPosition) {
                        if (targetPosition === 0) {
                            tbody.insertBefore(row, tbody.firstChild);
                        } else {
                            const targetRow = tbody.children[targetPosition];
                            if (targetRow && targetRow !== row) {
                                tbody.insertBefore(row, targetRow);
                            }
                        }
                    }
                }
            });
            
            // Update last timestamp
            if (sortedMovements.length > 0) {
                lastUpdateTimestamp = Math.max(...sortedMovements.map(m => m.created_at_timestamp));
            }
        } catch (error) {
            console.error('Error fetching stock history:', error);
        }
    }
    
    // Initial display - always use kg_g format (no unit selector needed)
    updateDisplay();
    
    // Start polling every 3 seconds
    updateInterval = setInterval(fetchStockHistory, 3000);
    
    // Also fetch immediately
    fetchStockHistory();
    
    // Clean up interval when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }
        } else {
            if (!updateInterval) {
                updateInterval = setInterval(fetchStockHistory, 3000);
                fetchStockHistory();
            }
        }
    });
});
</script>
@endsection


