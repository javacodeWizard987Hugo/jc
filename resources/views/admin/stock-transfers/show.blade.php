@extends('layouts.app')

@section('content')
    <h1>Stock Transfer Details</h1>
    <p><strong>ID:</strong> {{ $stockTransfer->id }}</p>
    <p><strong>From:</strong> {{ $stockTransfer->fromLocation->name }}</p>
    <p><strong>To:</strong> {{ $stockTransfer->toLocation->name }}</p>
    <p><strong>Date:</strong> {{ $stockTransfer->transfer_date }}</p>
    <p><strong>Status:</strong> {{ $stockTransfer->status }}</p>
    <p><strong>Notes:</strong> {{ $stockTransfer->notes }}</p>
    <h2>Items</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Serial Number</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stockTransfer->items as $item)
                <tr>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->serial_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($stockTransfer->status == 'pending')
        <form action="{{ route('admin.stock-transfers.approve', $stockTransfer) }}" method="POST" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn btn-success">Approve</button>
        </form>
        <form action="{{ route('admin.stock-transfers.reject', $stockTransfer) }}" method="POST" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn btn-danger">Reject</button>
        </form>
    @endif
@endsection
