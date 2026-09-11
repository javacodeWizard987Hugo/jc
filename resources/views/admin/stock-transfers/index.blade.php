@extends('layouts.app')

@section('content')
    <h1>Stock Transfers</h1>
    <a href="{{ route('admin.stock-transfers.create') }}" class="btn btn-primary">Create Stock Transfer</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>From</th>
                <th>To</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stockTransfers as $stockTransfer)
                <tr>
                    <td>{{ $stockTransfer->id }}</td>
                    <td>{{ $stockTransfer->fromLocation->name }}</td>
                    <td>{{ $stockTransfer->toLocation->name }}</td>
                    <td>{{ $stockTransfer->transfer_date }}</td>
                    <td>{{ $stockTransfer->status }}</td>
                    <td>
                        <a href="{{ route('admin.stock-transfers.show', $stockTransfer) }}" class="btn btn-info">View</a>
                        <a href="{{ route('admin.stock-transfers.edit', $stockTransfer) }}" class="btn btn-warning">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
