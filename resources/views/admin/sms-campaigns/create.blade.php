@extends('layouts.app')

@section('title', 'Create SMS Campaign')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Create SMS Campaign</h1>
            <p class="mt-2 text-sm text-gray-700">Create a new promotional SMS campaign.</p>
        </div>
    </div>
    <div class="mt-8">
        <form action="{{ route('admin.sms-campaigns.store') }}" method="POST" class="bg-white shadow sm:rounded-lg p-6">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea id="message" name="message" rows="4" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
                <div>
                    <label for="customers" class="block text-sm font-medium text-gray-700">Customers</label>
                    <select id="customers" name="customers" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                        <option value="all">All Opted-in Customers</option>
                        <option value="by_branch">By Branch</option>
                        <option value="by_purchase_history">By Purchase History</option>
                        <option value="by_customer_category">By Customer Category</option>
                    </select>
                </div>
                <div id="branch-filter" style="display: none;">
                    <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                    <select id="branch_id" name="branch_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="purchase-history-filter" style="display: none;">
                    <label for="months" class="block text-sm font-medium text-gray-700">Purchased in the last (months)</label>
                    <input type="number" name="months" id="months" min="1" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div id="customer-category-filter" style="display: none;">
                    <label for="category" class="block text-sm font-medium text-gray-700">Customer Category</label>
                    <select id="category" name="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                        <option value="normal">Normal</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>
            </div>
            <script>
                document.getElementById('customers').addEventListener('change', function () {
                    document.getElementById('branch-filter').style.display = 'none';
                    document.getElementById('purchase-history-filter').style.display = 'none';
                    document.getElementById('customer-category-filter').style.display = 'none';

                    if (this.value === 'by_branch') {
                        document.getElementById('branch-filter').style.display = 'block';
                    } else if (this.value === 'by_purchase_history') {
                        document.getElementById('purchase-history-filter').style.display = 'block';
                    } else if (this.value === 'by_customer_category') {
                        document.getElementById('customer-category-filter').style.display = 'block';
                    }
                });
            </script>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Send Campaign
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
