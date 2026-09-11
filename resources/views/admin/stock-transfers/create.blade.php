@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Title --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-danger">
            <i class="fas fa-exchange-alt me-2"></i> Create Stock Transfer
        </h3>
        <a href="{{ route('admin.stock-transfers.index') }}"
            class="btn btn-danger btn-sm px-3 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back</span>
            </a>

    </div>

    <form action="{{ route('admin.stock-transfers.store') }}" method="POST">
        @csrf

        {{-- Transfer Info --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-danger text-white fw-semibold">
                Transfer Information
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">From Location</label>
                        <select name="from_location_id" class="form-select" required>
                            <option value="">-- Select Source --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">To Location</label>
                        <select name="to_location_id" class="form-select" required>
                            <option value="">-- Select Destination --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Transfer Date</label>
                        <input type="date" name="transfer_date" class="form-control" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- Items Section --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-semibold d-flex justify-content-between">
                <span>Transfer Items</span>
                <button type="button" id="add-item" class="btn btn-sm btn-light">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>

            <div class="card-body p-0">
                <table class="table table-bordered table-hover mb-0" id="items-table">
                    <thead class="table-light">
                        <tr class="text-center align-middle">
                            <th width="40%">Item</th>
                            <th width="20%">Quantity</th>
                            <th width="30%">Serial Number</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="text" name="items[0][item_id]" class="form-control" placeholder="Item ID / Name">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][quantity]" class="form-control text-end">
                            </td>
                            <td>
                                <input type="text" name="items[0][serial_number]" class="form-control">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit --}}
        <div class="text-end">
           <button type="submit" class="btn btn-danger btn-lg px-5 fw-semibold">
                <i class="fas fa-save me-1"></i> Create Transfer
            </button>

        </div>

    </form>
</div>
@endsection
