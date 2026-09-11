@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Warranty Details</h1>
        <div>
            <strong>Item:</strong> {{ $warranty->serialNumber->item->name }}
        </div>
        <div>
            <strong>Serial Number:</strong> {{ $warranty->serialNumber->serial_number }}
        </div>
        <div>
            <strong>Customer:</strong> {{ $warranty->customer->name }}
        </div>
        <div>
            <strong>Invoice #:</strong> {{ $warranty->saleItem->sale->invoice_number }}
        </div>
        <div>
            <strong>Start Date:</strong> {{ $warranty->start_date }}
        </div>
        <div>
            <strong>Expiry Date:</strong> {{ $warranty->expiry_date }}
        </div>
        <hr>
        <a href="{{ route('admin.warranties.index') }}" class="btn btn-secondary mt-3">Back to List</a>
    </div>
@endsection
