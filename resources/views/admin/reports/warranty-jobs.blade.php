@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Warranty Jobs Report</h1>

        <form method="GET" action="{{ route('admin.reports.warranty-jobs') }}" class="mb-3">
            <div class="form-row">
                <div class="col">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col">
                    <select name="branch_id" class="form-control">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No warranty jobs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $jobs->links() }}
    </div>
@endsection
