@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card border-danger shadow-sm mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 fw-bold">
                🛠️ Warranty Job Details - {{ $warrantyJob->job_number }}
            </h4>
            @php
                $backRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.index') : route('cashier.warranty-jobs.index');
            @endphp
            <a href="{{ $backRoute }}" class="btn btn-light btn-sm fw-bold">
                ⬅️ Back to Jobs List
            </a>
        </div>

        <div class="card-body">
            <div class="row g-4 mb-4">
                {{-- Job Overview --}}
                <div class="col-md-6">
                    <div class="card border-secondary h-100">
                        <div class="card-header bg-secondary text-white fw-bold">
                            📋 Job Information
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%;">Job Number:</th>
                                    <td><strong class="text-danger fs-6">{{ $warrantyJob->job_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Claim Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($warrantyJob->claim_date ?? $warrantyJob->created_at)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Receiving Branch:</th>
                                    <td>{{ $warrantyJob->branch->name ?? 'Main Branch' }}</td>
                                </tr>
                                <tr>
                                    <th>Claim Type:</th>
                                    <td><span class="badge bg-secondary text-uppercase">{{ $warrantyJob->claim_type }}</span></td>
                                </tr>
                                <tr>
                                    <th>Current Status:</th>
                                    <td>
                                        @php
                                            $badgeClass = match($warrantyJob->status) {
                                                'Received' => 'bg-info text-dark',
                                                'Sent to Service Center / Supplier' => 'bg-primary',
                                                'Under Repair' => 'bg-warning text-dark',
                                                'Returned to Branch' => 'bg-secondary',
                                                'Ready for Collection' => 'bg-success',
                                                'Collected' => 'bg-dark',
                                                'Rejected / Not Covered' => 'bg-danger',
                                                'Replaced' => 'bg-success',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} fs-6">{{ $warrantyJob->status }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Problem Description:</th>
                                    <td class="text-break">{{ $warrantyJob->problem_description }}</td>
                                </tr>
                                @if($warrantyJob->collected_at)
                                    <tr class="table-success">
                                        <th>Collection Details:</th>
                                        <td>
                                            Collected by <strong>{{ $warrantyJob->collected_by_name }}</strong> (ID/NIC: {{ $warrantyJob->collected_by_id }}) on {{ $warrantyJob->collected_at->format('d M Y, h:i A') }}
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Linked Item & Customer Info --}}
                <div class="col-md-6">
                    <div class="card border-secondary h-100">
                        <div class="card-header bg-secondary text-white fw-bold">
                            📦 Linked Warranty Record
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%;">Item / Model:</th>
                                    <td><strong>{{ $warrantyJob->warranty->serialNumber->item->name ?? 'N/A' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Serial Number:</th>
                                    <td><span class="badge bg-dark fs-6">{{ $warrantyJob->warranty->serialNumber->serial_number ?? 'N/A' }}</span></td>
                                </tr>
                                <tr>
                                    <th>Customer Name:</th>
                                    <td>{{ $warrantyJob->warranty->customer->name ?? 'Walk-in Customer' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer NIC:</th>
                                    <td>{{ $warrantyJob->warranty->customer->nic ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Phone:</th>
                                    <td>{{ $warrantyJob->warranty->customer->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Original Invoice #:</th>
                                    <td>
                                        <span class="text-primary fw-bold">
                                            {{ $warrantyJob->warranty->saleItem->sale->invoice_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Warranty Expiry:</th>
                                    <td>
                                        @php
                                            $isExpired = \Carbon\Carbon::parse($warrantyJob->warranty->expiry_date)->isPast();
                                        @endphp
                                        <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }}">
                                            {{ \Carbon\Carbon::parse($warrantyJob->warranty->expiry_date)->format('d M Y') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                {{-- Update Status Form --}}
                @if ($warrantyJob->status !== 'Collected')
                    <div class="col-md-6">
                        <div class="card border-primary shadow-sm h-100">
                            <div class="card-header bg-primary text-white fw-bold">
                                🔄 Update Warranty Job Status
                            </div>
                            <div class="card-body">
                                @php
                                    $updateRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.update', $warrantyJob->id) : route('cashier.warranty-jobs.update', $warrantyJob->id);
                                @endphp
                                <form action="{{ $updateRoute }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-semibold">New Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-select border-primary" required>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}" {{ $warrantyJob->status == $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="remarks" class="form-label fw-semibold">Remarks / Notes</label>
                                        <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="Note on status update..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary fw-bold w-100">
                                        Update Job Status
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Record Collection Form --}}
                @if ($warrantyJob->status === 'Ready for Collection' || ($warrantyJob->status !== 'Collected' && $warrantyJob->status !== 'Rejected / Not Covered'))
                    <div class="col-md-6">
                        <div class="card border-success shadow-sm h-100">
                            <div class="card-header bg-success text-white fw-bold">
                                📦 Record Customer Collection
                            </div>
                            <div class="card-body">
                                @php
                                    $collectRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.collect', $warrantyJob->id) : route('cashier.warranty-jobs.collect', $warrantyJob->id);
                                @endphp
                                <form action="{{ $collectRoute }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="collected_by_name" class="form-label fw-semibold">Person Collecting (Name) <span class="text-danger">*</span></label>
                                        <input type="text" name="collected_by_name" id="collected_by_name" class="form-control border-success" placeholder="Full Name" required value="{{ old('collected_by_name', $warrantyJob->warranty->customer->name ?? '') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="collected_by_id" class="form-label fw-semibold">Person ID / NIC <span class="text-danger">*</span></label>
                                        <input type="text" name="collected_by_id" id="collected_by_id" class="form-control border-success" placeholder="NIC or National ID Number" required value="{{ old('collected_by_id', $warrantyJob->warranty->customer->nic ?? '') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="collected_at" class="form-label fw-semibold">Actual Collection Date</label>
                                        <input type="date" name="collected_at" id="collected_at" class="form-control border-success" value="{{ old('collected_at', now()->format('Y-m-d')) }}">
                                    </div>
                                    <button type="submit" class="btn btn-success fw-bold w-100">
                                        ✅ Mark as Collected
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- History Audit Log --}}
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white fw-bold">
                    📜 Job Status History & Audit Log
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Previous Status</th>
                                    <th>New Status</th>
                                    <th>Updated By</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warrantyJob->history as $history)
                                    <tr>
                                        <td>{{ $history->created_at->format('d M Y, h:i A') }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $history->old_status ?? 'Initial Creation' }}</span></td>
                                        <td><span class="badge bg-info text-dark">{{ $history->new_status }}</span></td>
                                        <td>{{ $history->updater->name ?? 'System' }}</td>
                                        <td>{{ $history->remarks }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No status history recorded.</td>
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
