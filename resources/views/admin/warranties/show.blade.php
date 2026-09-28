@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card border-danger shadow-sm mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 fw-bold">
                🛡️ Warranty Details
            </h4>
            @php
                $backRoute = auth()->user()->isAdmin() ? route('admin.warranties.index') : route('cashier.warranties.index');
            @endphp
            <a href="{{ $backRoute }}" class="btn btn-light btn-sm fw-bold">
                ⬅️ Back to Warranty Register
            </a>
        </div>

        <div class="card-body">
            <div class="row g-4">
                {{-- Item & Serial Info --}}
                <div class="col-md-6">
                    <div class="card border-secondary h-100">
                        <div class="card-header bg-secondary text-white fw-bold">
                            📦 Item & Warranty Info
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%;">Item / Model:</th>
                                    <td><strong>{{ $warranty->serialNumber->item->name ?? 'N/A' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Item Code:</th>
                                    <td>{{ $warranty->serialNumber->item->item_code ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Serial Number:</th>
                                    <td><span class="badge bg-dark fs-6">{{ $warranty->serialNumber->serial_number ?? 'N/A' }}</span></td>
                                </tr>
                                <tr>
                                    <th>Warranty Duration:</th>
                                    <td><span class="badge bg-info text-dark fs-6">{{ $warranty->duration }} Months</span></td>
                                </tr>
                                <tr>
                                    <th>Start Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($warranty->start_date)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Expiry Date:</th>
                                    <td>
                                        @php
                                            $isExpired = \Carbon\Carbon::parse($warranty->expiry_date)->isPast();
                                        @endphp
                                        <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }} fs-6">
                                            {{ \Carbon\Carbon::parse($warranty->expiry_date)->format('d M Y') }}
                                        </span>
                                        <span class="ms-2 fw-bold {{ $isExpired ? 'text-danger' : 'text-success' }}">
                                            ({{ $isExpired ? 'Expired' : 'Active' }})
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Invoice & Customer Info --}}
                <div class="col-md-6">
                    <div class="card border-secondary h-100">
                        <div class="card-header bg-secondary text-white fw-bold">
                            👤 Customer & Sale Info
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%;">Customer Name:</th>
                                    <td><strong>{{ $warranty->customer->name ?? 'Walk-in Customer' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Customer NIC:</th>
                                    <td>{{ $warranty->customer->nic ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Phone:</th>
                                    <td>{{ $warranty->customer->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Invoice Number:</th>
                                    <td><span class="text-primary fw-bold fs-6">{{ $warranty->saleItem->sale->invoice_number ?? 'N/A' }}</span></td>
                                </tr>
                                <tr>
                                    <th>Invoice Date:</th>
                                    <td>{{ $warranty->saleItem->sale->created_at ? $warranty->saleItem->sale->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Selling Branch:</th>
                                    <td>{{ $warranty->saleItem->sale->branch->name ?? 'Main Branch' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Warranty Claim Jobs Section --}}
            <div class="card border-danger mt-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <h5 class="mb-0 fw-bold text-danger">🛠️ Warranty Claim Jobs</h5>
                    @php
                        $createJobRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.create', ['warranty_id' => $warranty->id]) : route('cashier.warranty-jobs.create', ['warranty_id' => $warranty->id]);
                    @endphp
                    <a href="{{ $createJobRoute }}" class="btn btn-danger btn-sm">
                        + Create Claim Job
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Job #</th>
                                    <th>Claim Date</th>
                                    <th>Branch</th>
                                    <th>Claim Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warranty->warrantyJobs as $job)
                                    <tr>
                                        <td><strong>{{ $job->job_number }}</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($job->claim_date ?? $job->created_at)->format('d M Y') }}</td>
                                        <td>{{ $job->branch->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($job->claim_type) }}</span></td>
                                        <td><span class="badge bg-warning text-dark">{{ $job->status }}</span></td>
                                        <td>
                                            @php
                                                $showJobRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.show', $job->id) : route('cashier.warranty-jobs.show', $job->id);
                                            @endphp
                                            <a href="{{ $showJobRoute }}" class="btn btn-sm btn-outline-danger">View Job</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            No claim jobs created for this warranty record yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
