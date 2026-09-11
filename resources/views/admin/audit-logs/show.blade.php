@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.audit-logs.index') }}" class="text-red-600 hover:text-red-900 mb-4 inline-block">← Back to Audit Logs</a>
        <h1 class="text-3xl font-bold text-gray-900">Audit Log Details</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Log Information</h2>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Date & Time</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">User</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->user->name ?? 'System' }} ({{ $auditLog->user->email ?? 'N/A' }})</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Action</dt>
                <dd class="text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->description }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->ip_address ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->user_agent ?? 'N/A' }}</dd>
            </div>
        </dl>
    </div>

    @if($auditLog->old_values || $auditLog->new_values)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if($auditLog->old_values)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-red-600">Old Values</h3>
            <pre class="bg-gray-50 p-4 rounded text-xs overflow-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif

        @if($auditLog->new_values)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-green-600">New Values</h3>
            <pre class="bg-gray-50 p-4 rounded text-xs overflow-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection

