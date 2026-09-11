@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="page-header">
    <h1>System Settings</h1>
    <p>Manage application-wide settings.</p>
</div>

<div class="content-card">
    <div class="p-6">
        <form action="{{ route('admin.system-settings.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="input-group">
                    <label for="overdue_grace_period" class="form-label">Overdue Grace Period (Days)</label>
                    <input type="number" id="overdue_grace_period" name="overdue_grace_period" class="form-input" value="{{ old('overdue_grace_period', $gracePeriod) }}" min="0" required>
                    <p class="text-sm text-gray-500 mt-2">
                        The number of days after a due date before an installment is marked as overdue.
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
