@extends('layouts.app')

@section('content')
<div class="container">

    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-shield-alt me-2"></i> Warranty Register
            </h4>
        </div>

        <div class="card-body">

          {{-- 🔍 Warranty Search --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body bg-light">
                <form method="GET" action="{{ route('admin.warranties.index') }}">
                    <div class="row g-2 align-items-center">

                        <div class="col-md-9">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-danger text-white border-danger">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control border-danger"
                                    placeholder="Serial No | Invoice No | Customer Name | Phone"
                                    value="{{ request('search') }}"
                                >
                            </div>
                        </div>

                        <div class="col-md-3 d-grid">
                            <button class="btn btn-danger btn-lg fw-semibold">
                                <i class="fas fa-filter me-1"></i> Search
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>


            {{-- 📋 Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-danger text-center">
                        <tr>
                            <th>Item</th>
                            <th>Serial Number</th>
                            <th>Customer</th>
                            <th>Invoice #</th>
                            <th>Start Date</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($warranties as $warranty)
                            <tr>
                                <td>
                                    <strong>{{ $warranty->serialNumber->item->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-dark">
                                        {{ $warranty->serialNumber->serial_number }}
                                    </span>
                                </td>
                                <td>{{ $warranty->customer->name }}</td>
                                <td>
                                    <span class="text-primary fw-semibold">
                                        {{ $warranty->saleItem->sale->invoice_number }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($warranty->start_date)->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-danger">
                                        {{ \Carbon\Carbon::parse($warranty->expiry_date)->format('d M Y') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.warranties.show', $warranty->id) }}"
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No warranties found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 📄 Pagination --}}
            <div class="d-flex justify-content-end">
                {{ $warranties->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
@endsection
