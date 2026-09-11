@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Warranty Jobs</h1>
        <a href="{{ route('admin.warranty-jobs.create') }}" class="btn btn-primary mb-3">Create Warranty Job</a>

        <form method="GET" action="{{ route('admin.warranty-jobs.index') }}" class="mb-3">
            <div class="form-row">
                <div class="col">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                        <option value="Sent to Service Center" {{ request('status') == 'Sent to Service Center' ? 'selected' : '' }}>Sent to Service Center</option>
                        <option value="Under Repair" {{ request('status') == 'Under Repair' ? 'selected' : '' }}>Under Repair</option>
                        <option value="Returned to Branch" {{ request('status') == 'Returned to Branch' ? 'selected' : '' }}>Returned to Branch</option>
                        <option value="Ready for Collection" {{ request('status') == 'Ready for Collection' ? 'selected' : '' }}>Ready for Collection</option>
                        <option value="Collected" {{ request('status') == 'Collected' ? 'selected' : '' }}>Collected</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="Replaced" {{ request('status') == 'Replaced' ? 'selected' : '' }}>Replaced</option>
                    </select>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Job Number</th>
                    <th>Item</th>
                    <th>Serial Number</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                    <tr>
                        <td>{{ $job->job_number }}</td>
                        <td>{{ $job->warranty->serialNumber->item->name }}</td>
                        <td>{{ $job->warranty->serialNumber->serial_number }}</td>
                        <td>{{ $job->branch->name }}</td>
                        <td>{{ $job->status }}</td>
                        <td>{{ $job->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('admin.warranty-jobs.show', $job->id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No warranty jobs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $jobs->links() }}
    </div>
@endsection
