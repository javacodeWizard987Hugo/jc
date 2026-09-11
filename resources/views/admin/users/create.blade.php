@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create User</h1>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('name') }}">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" id="email" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('email') }}">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required minlength="6"
                               class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        <button type="button" onclick="togglePasswordVisibility('password')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-red-600 focus:outline-none mt-1">
                            <svg id="eyeIcon-password" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eyeSlashIcon-password" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                    <select name="role" id="role" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                            onchange="togglePermissions()">
                        <option value="cashier" {{ old('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="pin" class="block text-sm font-medium text-gray-700">PIN (6 digits, optional)</label>
                    <input type="text" name="pin" id="pin" maxlength="6" pattern="[0-9]{6}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('pin') }}" placeholder="000000">
                </div>
            </div>

            <div id="permissions-section" class="bg-white shadow rounded-lg p-6 {{ old('role') == 'admin' ? 'hidden' : '' }}">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Permissions</h2>
                    <div class="flex items-center">
                        <input type="checkbox" id="select-all-permissions" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <label for="select-all-permissions" class="ml-2 text-sm font-medium text-gray-700">Select All</label>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-4">Select the modules this cashier can access.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                    @php
                        $available_permissions = [
                            'dashboard' => 'Dashboard',
                            'customers' => 'Customers',
                            'installments' => 'Installments',
                            'agreements' => 'Agreements',
                            'billing' => 'Billing (POS)',
                            'payments' => 'Payments',
                            'reports' => 'Reports',
                            'audit_logs' => 'Audit Logs',
                            'user_management' => 'User Management',
                            'settings' => 'Settings',
                        ];
                    @endphp

                    @foreach($available_permissions as $key => $label)
                        <div class="flex items-center">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ $key }}"
                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                                   {{ is_array(old('permissions')) && in_array($key, old('permissions')) ? 'checked' : '' }}>
                            <label for="perm_{{ $key }}" class="ml-2 block text-sm text-gray-900">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Create User
            </button>
        </div>
    </form>
</div>

<script>
function togglePasswordVisibility(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const eyeIcon = document.getElementById('eyeIcon-' + fieldId);
    const eyeSlashIcon = document.getElementById('eyeSlashIcon-' + fieldId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeSlashIcon.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeSlashIcon.classList.add('hidden');
    }
}

function togglePermissions() {
    const roleSelect = document.getElementById('role');
    const permissionsSection = document.getElementById('permissions-section');
    
    if (roleSelect.value === 'admin') {
        permissionsSection.classList.add('hidden');
    } else {
        permissionsSection.classList.remove('hidden');
    }
}

document.getElementById('select-all-permissions').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>
@endsection
