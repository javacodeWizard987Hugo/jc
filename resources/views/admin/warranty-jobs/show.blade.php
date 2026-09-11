@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Warranty Job #{{ $warrantyJob->job_number }}</h1>

        <div><strong>Item:</strong> {{ $warrantyJob->warranty->serialNumber->item->name }}</div>
        <div><strong>Serial Number:</strong> {{ $warrantyJob->warranty->serialNumber->serial_number }}</div>
        <div><strong>Branch:</strong> {{ $warrantyJob->branch->name }}</div>
        <div><strong>Status:</strong> {{ $warrantyJob->status }}</div>
        <div><strong>Problem:</strong> {{ $warrantyJob->problem_description }}</div>
        @if($warrantyJob->collected_at)
            <div><strong>Collected By:</strong> {{ $warrantyJob->collected_by_name }} ({{ $warrantyJob->collected_by_id }}) on {{ $warrantyJob->collected_at->format('Y-m-d') }}</div>
        @endif

        <hr>

        @if ($warrantyJob->status !== 'Collected')
            <h3>Update Status</h3>
            <form action="{{ route('admin.warranty-jobs.update', $warrantyJob->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="status">New Status</label>
                    <select name="status" id="status" class="form-control" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ $warrantyJob->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea name="remarks" id="remarks" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        @endif

        @if ($warrantyJob->status === 'Ready for Collection')
            <hr>
            <h3>Mark as Collected</h3>
            <form action="{{ route('admin.warranty-jobs.collect', $warrantyJob->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="collected_by_name">Collected By Name</label>
                    <input type="text" name="collected_by_name" id="collected_by_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="collected_by_id">Collected By ID/NIC</label>
                    <input type="text" name="collected_by_id" id="collected_by_id" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Mark as Collected</button>
            </form>
        @endif

        <hr>

        <h3>Job History</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Old Status</th>
                    <th>New Status</th>
                    <th>Updated By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($warrantyJob->history as $history)
                    <tr>
                        <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $history->old_status ?? 'N/A' }}</td>
                        <td>{{ $history->new_status }}</td>
                        <td>{{ $history->updater->name }}</td>
                        <td>{{ $history->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
