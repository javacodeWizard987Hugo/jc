@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card border-danger shadow-sm col-md-10 mx-auto">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 fw-bold">
                🛠️ Create Warranty Claim Job
            </h4>
            @php
                $backRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.index') : route('cashier.warranty-jobs.index');
            @endphp
            <a href="{{ $backRoute }}" class="btn btn-light btn-sm fw-bold">
                ⬅️ Back to Jobs List
            </a>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.warranty-jobs.store') : route('cashier.warranty-jobs.store') }}">
                @csrf

                <div class="row g-3">
                    {{-- Select Warranty Item --}}
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Select Warranty Item / Serial Number <span class="text-danger">*</span></label>
                        <select name="warranty_id" class="form-select border-danger" required id="warranty_id_select">
                            <option value="">-- Select Warranty Record --</option>
                            @foreach($warranties as $warranty)
                                <option value="{{ $warranty->id }}" {{ request('warranty_id') == $warranty->id ? 'selected' : '' }}>
                                    Item: {{ $warranty->serialNumber->item->name ?? 'N/A' }} | 
                                    S/N: {{ $warranty->serialNumber->serial_number ?? 'N/A' }} | 
                                    Inv #: {{ $warranty->saleItem->sale->invoice_number ?? 'N/A' }} | 
                                    Customer: {{ $warranty->customer->name ?? 'N/A' }} ({{ $warranty->customer->nic ?? 'No NIC' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Receiving Branch --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Branch Receiving Claim <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select border-danger" required>
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (auth()->user()->branch_id == $branch->id || old('branch_id') == $branch->id) ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Claim Date --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Claim Date <span class="text-danger">*</span></label>
                        <input type="date" name="claim_date" class="form-control border-danger" value="{{ old('claim_date', now()->format('Y-m-d')) }}" required>
                    </div>

                    {{-- Claim Type --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Claim Type <span class="text-danger">*</span></label>
                        <select name="claim_type" class="form-select border-danger" required>
                            <option value="repair" {{ old('claim_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                            <option value="replacement" {{ old('claim_type') == 'replacement' ? 'selected' : '' }}>Replacement</option>
                            <option value="inspection" {{ old('claim_type') == 'inspection' ? 'selected' : '' }}>Inspection Only</option>
                        </select>
                    </div>

                    {{-- Problem Description --}}
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Problem Description <span class="text-danger">*</span></label>
                        <textarea name="problem_description" class="form-control border-danger" rows="3" placeholder="Describe the fault reported by customer..." required>{{ old('problem_description') }}</textarea>
                    </div>

                    {{-- Remarks --}}
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes...">{{ old('remarks') }}</textarea>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold">
                            ➕ Create Warranty Job
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection
