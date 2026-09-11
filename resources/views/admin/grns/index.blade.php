@extends('layouts.app')

@section('title', 'GRNs - Goods Received Notes')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="page-header">
        <div class="flex justify-between items-center">
            <div>
                <h1>Goods Received Notes (GRN)</h1>
                <p>Manage incoming stock and supplier deliveries</p>
            </div>
            <a href="{{ route(($routePrefix ?? 'admin') . '.grns.create') }}" class="btn btn-primary bg-white text-red-600 hover:bg-gray-100">
                <span class="mr-2">➕</span> Create GRN
            </a>
        </div>
    </div>

    <!-- GRNs Table Card -->
    <div class="content-card overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All GRNs</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>GRN Number</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Total Amount</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grns as $grn)
                    <tr>
                        <td class="font-mono font-semibold text-gray-900">{{ $grn->grn_number }}</td>
                        <td class="text-gray-900">{{ $grn->supplier->name }}</td>
                        <td class="text-gray-600">{{ $grn->grn_date->format('M d, Y') }}</td>
                        <td class="text-gray-500">{{ $grn->reference_document ?? 'N/A' }}</td>
                        <td class="font-semibold text-red-600">Rs. {{ number_format($grn->total_amount, 2) }}</td>
                        <td class="text-gray-600">{{ $grn->creator->name }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route(($routePrefix ?? 'admin') . '.grns.show', $grn) }}"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View
                                </a>
                                <span class="text-gray-300">|</span>
                                <form action="{{ route(($routePrefix ?? 'admin') . '.grns.destroy', $grn) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Are you sure? This will reverse stock movements.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">📋</div>
                            <p>No GRNs found. Create your first GRN!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($grns->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $grns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
