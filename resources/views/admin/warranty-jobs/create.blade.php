@extends('layouts.app')

@section('title', 'Warranty & Service Job Register / Create Warranty Claim Job')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="page-header border-l-4 border-red-600 bg-white p-6 rounded-lg shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 style="color: #DC2626;" class="text-2xl font-bold">
                🛠️ Warranty & Service Job Register / Create Warranty Claim Job
            </h1>
            <p class="text-sm text-gray-600 mt-1">Register a new warranty claim job for a customer item</p>
        </div>
        @php
            $backRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.index') : route('cashier.warranty-jobs.index');
        @endphp
        <a href="{{ $backRoute }}" class="btn btn-secondary text-sm font-semibold">
            ⬅️ Back to Jobs List
        </a>
    </div>

    <!-- Form Card -->
    <div class="content-card bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.warranty-jobs.store') : route('cashier.warranty-jobs.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Select Warranty Item --}}
                <div class="md:col-span-2">
                    <label for="warranty_id_select" class="form-label font-bold text-gray-800">
                        Select Warranty Item / Serial Number <span class="text-red-600">*</span>
                    </label>
                    <select name="warranty_id" id="warranty_id_select" class="form-select w-full border-red-400 focus:border-red-600 focus:ring-red-200" required>
                        <option value="">-- Select Warranty Record --</option>
                        @foreach($warranties as $warranty)
                            @php
                                $itemName = $warranty->serialNumber->item->name ?? 'N/A';
                                $serialNo = $warranty->serialNumber->serial_number ?? 'N/A';
                                $invNo = $warranty->saleItem->sale->invoice_number ?? 'N/A';
                                $cust = $warranty->customer ?? $warranty->saleItem?->sale?->customer;
                                $custName = $cust?->name ?? 'N/A';
                                $custNic = $cust?->nic ?? 'No NIC';
                            @endphp
                            <option value="{{ $warranty->id }}" {{ (request('warranty_id') == $warranty->id || old('warranty_id') == $warranty->id) ? 'selected' : '' }}>
                                Item: {{ $itemName }} | S/N: {{ $serialNo }} | Inv #: {{ $invNo }} | Customer: {{ $custName }} ({{ $custNic }})
                            </option>
                        @endforeach
                    </select>
                    @error('warranty_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Receiving Branch --}}
                <div>
                    <label for="branch_id" class="form-label font-bold text-gray-800">
                        Branch Receiving Claim <span class="text-red-600">*</span>
                    </label>
                    <select name="branch_id" id="branch_id" class="form-select w-full border-red-400 focus:border-red-600 focus:ring-red-200" required>
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (auth()->user()->branch_id == $branch->id || old('branch_id') == $branch->id) ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Claim Date --}}
                <div>
                    <label for="claim_date" class="form-label font-bold text-gray-800">
                        Claim Date <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="claim_date" id="claim_date" class="form-control w-full border-red-400 focus:border-red-600 focus:ring-red-200" value="{{ old('claim_date', now()->format('Y-m-d')) }}" required>
                    @error('claim_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Claim Type --}}
                <div class="md:col-span-2">
                    <label for="claim_type" class="form-label font-bold text-gray-800">
                        Claim Type <span class="text-red-600">*</span>
                    </label>
                    <select name="claim_type" id="claim_type" class="form-select w-full border-red-400 focus:border-red-600 focus:ring-red-200" required>
                        <option value="repair" {{ old('claim_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                        <option value="replacement" {{ old('claim_type') == 'replacement' ? 'selected' : '' }}>Replacement</option>
                        <option value="inspection" {{ old('claim_type') == 'inspection' ? 'selected' : '' }}>Inspection Only</option>
                    </select>
                    @error('claim_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Problem Description --}}
                <div class="md:col-span-2">
                    <label for="problem_description" class="form-label font-bold text-gray-800">
                        Problem Description <span class="text-red-600">*</span>
                    </label>
                    <textarea name="problem_description" id="problem_description" class="form-control w-full border-red-400 focus:border-red-600 focus:ring-red-200" rows="3" placeholder="Describe the fault reported by customer..." required>{{ old('problem_description') }}</textarea>
                    @error('problem_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remarks --}}
                <div class="md:col-span-2">
                    <label for="remarks" class="form-label font-bold text-gray-800">
                        Remarks / Notes
                    </label>
                    <textarea name="remarks" id="remarks" class="form-control w-full" rows="2" placeholder="Optional notes...">{{ old('remarks') }}</textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="btn btn-danger px-8 py-3 font-bold text-lg">
                    ➕ Create Warranty Job
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
