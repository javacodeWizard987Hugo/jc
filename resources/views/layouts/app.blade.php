<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS System - GC SOLUTION')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Function to refresh CSRF token
        function refreshCSRFToken() {
            fetch(window.location.origin + '/csrf-token', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                // Fallback: get token from current page
                return fetch(window.location.href, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const metaToken = doc.querySelector('meta[name="csrf-token"]');
                    return metaToken ? { token: metaToken.getAttribute('content') } : null;
                });
            })
            .then(data => {
                if (data && data.token) {
                    const newToken = data.token;
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', newToken);
                    }
                    // Update all forms with new token
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = newToken;
                    });
                    // Update all hidden CSRF inputs
                    document.querySelectorAll('input[type="hidden"][name="_token"]').forEach(input => {
                        input.value = newToken;
                    });
                    console.log('CSRF token refreshed successfully');
                }
            })
            .catch(err => {
                console.log('CSRF token refresh failed:', err);
                // Try to get token from current page meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    console.log('Using existing CSRF token from page');
                }
            });
        }

        // Auto-refresh CSRF token every 30 minutes (less frequent to reduce load)
        setInterval(refreshCSRFToken, 30 * 60 * 1000); // 30 minutes
        
        // Refresh token when page becomes visible (user returns to tab) - debounced
        let visibilityTimeout;
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                clearTimeout(visibilityTimeout);
                visibilityTimeout = setTimeout(refreshCSRFToken, 2000); // Wait 2 seconds
            }
        });
        
        // Initial token refresh after 2 minutes (delayed to not slow initial load)
        setTimeout(refreshCSRFToken, 2 * 60 * 1000);

        // Session timeout auto-logout
        let inactivityTimer;
        const sessionTimeout = {{ $sessionTimeout ?? 120 }} * 60 * 1000; // Convert to milliseconds
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(function() {
                alert('Your session has expired due to inactivity. You will be logged out.');
                window.location.href = '{{ route("logout") }}';
            }, sessionTimeout);
        }
        
        // Reset timer on user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(event) {
            document.addEventListener(event, resetInactivityTimer, true);
        });
        
        // Start timer
        resetInactivityTimer();

        // Setup AJAX CSRF token - Enhanced version
        if (typeof window.fetch !== 'undefined') {
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                // Ensure headers object exists
                if (!args[1]) {
                    args[1] = {};
                }
                if (!args[1].headers) {
                    args[1].headers = {};
                }
                
                // Add CSRF token to all POST, PUT, PATCH, DELETE requests
                const method = (args[1].method || 'GET').toUpperCase();
                if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
                    if (!args[1].headers['X-CSRF-TOKEN'] && !args[1].headers['x-csrf-token']) {
                        args[1].headers['X-CSRF-TOKEN'] = token;
                    }
                    args[1].headers['X-Requested-With'] = 'XMLHttpRequest';
                }
                
                // Ensure credentials are included
                if (!args[1].credentials) {
                    args[1].credentials = 'same-origin';
                }
                
                // Handle 419 errors globally
                return originalFetch.apply(this, args)
                    .then(response => {
                        if (response.status === 419) {
                            // Token expired - refresh and retry once
                            console.warn('CSRF token expired, refreshing...');
                            refreshCSRFToken();
                            // Update token in request
                            const newToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            if (newToken && args[1] && args[1].headers) {
                                args[1].headers['X-CSRF-TOKEN'] = newToken;
                                // Retry the request
                                return originalFetch.apply(this, args);
                            }
                            // If refresh failed, show error
                            alert('Your session has expired. Please refresh the page and try again.');
                            window.location.reload();
                            return Promise.reject(new Error('CSRF token expired'));
                        }
                        return response;
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        throw error;
                    });
            };
        }
        
        // Also handle XMLHttpRequest
        if (typeof XMLHttpRequest !== 'undefined') {
            const originalOpen = XMLHttpRequest.prototype.open;
            const originalSend = XMLHttpRequest.prototype.send;
            
            XMLHttpRequest.prototype.open = function(method, url, ...rest) {
                this._method = method;
                this._url = url;
                return originalOpen.apply(this, [method, url, ...rest]);
            };
            
            XMLHttpRequest.prototype.send = function(data) {
                const method = this._method?.toUpperCase();
                if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    this.setRequestHeader('X-CSRF-TOKEN', token);
                    this.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                }
                
                // Handle 419 errors
                this.addEventListener('load', function() {
                    if (this.status === 419) {
                        console.warn('CSRF token expired in XHR, refreshing...');
                        refreshCSRFToken();
                        alert('Your session has expired. Please refresh the page and try again.');
                        window.location.reload();
                    }
                });
                
                return originalSend.apply(this, [data]);
            };
        }
        
        
        // Ensure all forms have fresh CSRF token before submission
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName === 'FORM' && form.method.toUpperCase() === 'POST') {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) {
                    let tokenInput = form.querySelector('input[name="_token"]');
                    if (!tokenInput) {
                        // Create token input if it doesn't exist
                        tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = '_token';
                        form.appendChild(tokenInput);
                    }
                    // Always update with latest token
                    tokenInput.value = token;
                }
            }
        }, true); // Use capture phase to run before other handlers
        
        // Ensure logout form works properly
        function initLogoutForm() {
            const logoutForm = document.getElementById('logoutForm');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function(e) {
                    // Refresh token before submitting
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (token) {
                        const tokenInput = this.querySelector('input[name="_token"]');
                        if (tokenInput) {
                            tokenInput.value = token;
                        }
                    }
                });
            }
        }
        
        // Initialize logout form when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLogoutForm);
        } else {
            initLogoutForm();
        }
        
        // Ensure Password link works
        document.querySelectorAll('a[href*="password.change"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Ensure link works
                if (!this.href || this.href === '#') {
                    e.preventDefault();
                    window.location.href = '{{ route("password.change") }}';
                }
            });
        });
    </script>
    <style>
        @media print {
            .letterhead {
                display: block !important;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                padding: 1rem;
                text-align: center;
                border-bottom: 1px solid #ccc;
            }
        }
        :root {
            /* GREEN THEME */
            --primary-green: #5e0606;        /* dark green */
            --primary-green-dark: #c23a04;   /* darker green */
            --primary-green-light: #d31a02;  /* light green */

            /* Existing */
            --sidebar-bg: rgb(22, 0, 0);
            --sidebar-hover: rgba(231, 9, 1, 0.86);
            --sidebar-active: rgb(156, 5, 0);
            --bg-white: #FFFFFF;
            --text-dark: #7a3a1d;
            --text-light: #6B7280;
        }

        
        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-active) 100%);
            min-height: 100vh;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 10px;
            min-height: 0;
        }
        
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
            font-size: 15px;
            font-weight: 500;
            margin: 2px 10px;
            border-radius: 8px;
            white-space: nowrap;
        }
        
        .nav-item:hover {
            background: var(--sidebar-hover);
            border-left-color: white;
            transform: translateX(4px);
        }
        
        .nav-item.active {
            background: var(--sidebar-active);
            border-left-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .nav-item-icon {
            font-size: 20px;
            width: 28px;
            text-align: center;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .nav-item-text {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* User profile section - clear and visible text */
        .sidebar .p-6 {
            flex-shrink: 0;
        }
        
        .sidebar .p-3 {
            overflow: hidden;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
        }
        
        .sidebar .p-3 p {
            margin: 0 !important;
            line-height: 1.5 !important;
            display: block !important;
            position: relative;
            z-index: 1;
            clear: both;
        }
        
        .sidebar .text-sm {
            margin-bottom: 4px !important;
            font-weight: 600 !important;
            color: #FFFFFF !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar .text-xs {
            margin-top: 0 !important;
            font-weight: 500 !important;
            color: #FEE2E2 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        /* Prevent any text duplication */
        .sidebar .flex-1 {
            position: relative;
            overflow: hidden;
        }
        
        .sidebar .flex-1 > * {
            display: block;
            width: 100%;
        }
        
        /* User profile card improvements */
        .user-profile-card {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .user-profile-card > div {
            display: flex;
            align-items: center;
            width: 100%;
        }
        
        .user-profile-card > div > div {
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
            width: 100%;
            min-width: 0;
        }
        
        .user-name {
            font-size: 17px;
            font-weight: 700;
            color: #FFFFFF !important;
            margin: 0 !important;
            padding: 0 !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            display: block !important;
            line-height: 1.5 !important;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .user-role {
            font-size: 12px;
            font-weight: 700;
            color: #FEE2E2 !important;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.4 !important;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Prevent any duplication or overlap */
        .user-profile-card .user-name,
        .user-profile-card .user-role {
            position: relative;
            z-index: 1;
        }
        
        /* Hide any duplicate elements */
        .user-profile-card > div > div > *:nth-child(n+3) {
            display: none !important;
        }
        
        /* Ensure no avatar circle appears - hide any avatar elements */
        .user-profile-card .w-12,
        .user-profile-card .h-12,
        .user-profile-card .rounded-full,
        .user-profile-card .bg-white {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            background: #F9FAFB;
            transition: margin-left 0.3s ease;
            width: calc(100% - 260px);
            position: relative;
            z-index: 1;
            overflow-x: hidden;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            padding: 16px 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
            margin: 0;
        }
        
        /* Main content area */
        main {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            padding: 24px;
            box-sizing: border-box;
        }
        
        /* Sidebar Footer */
        .sidebar-footer {
            flex-shrink: 0;
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--sidebar-active);
            margin-top: auto;
        }
        
        /* Mobile - Sidebar always visible */
        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }
            
            .main-content {
                margin-left: 220px;
                width: calc(100% - 220px);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            
            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
        }
        
        /* Ensure buttons are clickable */
        button, a {
            cursor: pointer;
            user-select: none;
        }
        
        /* Prevent content overflow */
        body {
            overflow-x: hidden;
        }
        
        main {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Page Header Styling */
        .page-header {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            border-left: 4px solid var(--primary-red);
        }
        
        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 8px 0;
            line-height: 1.2;
        }
        
        .page-header p {
            font-size: 15px;
            color: #6B7280;
            margin: 0;
            line-height: 1.5;
        }
        
        /* Content Card Styling */
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #E5E7EB;
            margin-bottom: 24px;
        }
        
        /* Form Styling */
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .form-label::after {
            content: '';
        }
        
        .form-input {
            width: 100%;
            padding: 10px 14px;
            font-size: 15px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            background: white;
            color: #1F2937;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .form-input:disabled {
            background: #F3F4F6;
            color: #9CA3AF;
            cursor: not-allowed;
        }
        
        select.form-input {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 8px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 36px;
        }
        
        /* Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            line-height: 1.5;
            white-space: nowrap;
        }
        
        .btn-primary {
            background: var(--primary-green);
            color: white;
            }

            .btn-primary:hover {
                background: var(--primary-green-dark);
                transform: translateY(-1px);
                box-shadow: 0 4px 6px rgba(6, 95, 70, 0.35);
            }

        .btn-secondary {
            background: #6B7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4B5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(107, 114, 128, 0.3);
        }
        
        .btn-danger {
            background: #EF4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #09da02ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.3);
        }
        
        /* Table Styling */
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .table thead {
            background: #F9FAFB;
            border-bottom: 2px solid #E5E7EB;
        }
        
        .table th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 14px 16px;
            border-bottom: 1px solid #E5E7EB;
            color: #1F2937;
        }
        
        .table tbody tr:hover {
            background: #F9FAFB;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Badge Styling */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .badge-danger {
            background: #FEE2E2;
            color: #991B1B;
        }
        
        .badge-info {
            background: #DBEAFE;
            color: #1E40AF;
        }
        
        .badge-warning {
            background: #FEF3C7;
            color: #92400E;
        }
        
        /* Empty State Styling */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #6B7280;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 20px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            font-size: 15px;
            color: #6B7280;
            margin-bottom: 24px;
        }
        
        /* Alert/Message Styling */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .alert-success {
            background: #014120ff;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        
        .alert-error {
            background: #FEE2E2;
            color: #d40808ff;
            border: 1px solid #FECACA;
        }
        
        .alert-info {
            background: #DBEAFE;
            color: #1E40AF;
            border: 1px solid #BFDBFE;
        }
        
        /* Input Group Styling */
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 6px;
        }
        
        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
        }
        
        /* Grid Layout */
        .grid {
            display: grid;
        }
        
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        
        .gap-4 {
            gap: 16px;
        }
        
        .gap-6 {
            gap: 24px;
        }
        
        /* Spacing */
        .space-y-6 > * + * {
            margin-top: 24px;
        }
        
        .mb-6 {
            margin-bottom: 24px;
        }
        
        .mt-6 {
            margin-top: 24px;
        }
        
        /* Text Utilities */
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-semibold {
            font-weight: 600;
        }
        
        .font-bold {
            font-weight: 700;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 16px;
            }
            
            .page-header h1 {
                font-size: 24px;
            }
            
            main {
                padding: 16px;
            }
            
            .table {
                font-size: 12px;
            }
            
            .table th,
            .table td {
                padding: 10px 12px;
            }
        }
    </style>
</head>


<body class="bg-gray-50">
    <div class="letterhead" style="display: none;">
        <h1>{{ config('app.name', 'POS System') }}</h1>
        {{-- TODO: Make this address dynamic --}}
        <p>123 Main Street, Anytown, USA</p>
    </div>
    @auth
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar">
            <div class="p-6 border-b border-red-800">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="flex-shrink-0">
                        @php
                            $logoPath = null;
                            $logoExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif', 'webp'];
                            foreach ($logoExtensions as $ext) {
                                if (file_exists(public_path('images/logo.' . $ext))) {
                                    $logoPath = asset('images/logo.' . $ext);
                                    break;
                                }
                            }
                        @endphp
                        
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">JC ENTERPRICES</h1>
                        <p class="text-xs text-red-200">POS System</p>
                    </div>
                </div>
                <div class="user-profile-card">
                    <div class="flex items-center">
                        <div class="flex-1 min-w-0" style="display: flex; flex-direction: column; gap: 4px; overflow: hidden;">
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-role">{{ strtoupper(auth()->user()->role) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-scroll p-4">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-item-icon">📊</span>
                        <span class="nav-item-text">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.pos') }}" class="nav-item {{ request()->routeIs('admin.pos') ? 'active' : '' }}">
                        <span class="nav-item-icon">💰</span>
                        <span class="nav-item-text">Billing</span>
                    </a>
                    <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">Inventory</div>
                    <a href="{{ route('admin.items.index') }}" class="nav-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">📦</span>
                        <span class="nav-item-text">Items</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">📁</span>
                        <span class="nav-item-text">Categories</span>
                    </a>
                    <a href="{{ route('admin.grns.index') }}" class="nav-item {{ request()->routeIs('admin.grns.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">📋</span>
                        <span class="nav-item-text">GRN</span>
                    </a>
                    <!--<a href="{{ route('admin.stock-adjustments.index') }}" class="nav-item {{ request()->routeIs('admin.stock-adjustments.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">⚖️</span>
                        <span class="nav-item-text">Stock Adjustments</span>
                    </a>-->
                    <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">People</div>
                    <a href="{{ route('admin.customers.index') }}" class="nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">👥</span>
                        <span class="nav-item-text">Customers</span>
                    </a>
                    <a href="{{ route('admin.suppliers.index') }}" class="nav-item {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">🏢</span>
                        <span class="nav-item-text">Suppliers</span>
                    </a>
                    <a href="{{ route('admin.customer-history.index') }}" class="nav-item {{ request()->routeIs('admin.customer-history.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">📜</span>
                        <span class="nav-item-text">Customer History</span>
                    </a>
                   <!-- <a href="{{ route('admin.stock-transfers.index') }}"
                        class="nav-item {{ request()->routeIs('admin.stock-transfers.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">🔄</span>
                            <span class="nav-item-text">Stock Transfers</span>
                        </a>-->

                    <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">👤</span>
                        <span class="nav-item-text">Users</span>
                    </a>
                    <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">Financial</div>
                    <a href="{{ route('admin.credits.index') }}" class="nav-item {{ request()->routeIs('admin.credits.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">💳</span>
                        <span class="nav-item-text">Credits</span>
                    </a>
                    <a href="{{ route('admin.installments.index') }}" class="nav-item {{ request()->routeIs('admin.installments.index') || request()->routeIs('admin.installments.show') ? 'active' : '' }}">
                        <span class="nav-item-icon">🗓️</span>
                        <span class="nav-item-text">All Installments</span>
                    </a>
                     <a href="{{ route('cashier.installments.index') }}" class="nav-item {{ request()->routeIs('cashier.installments.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">🗓️</span>
                            <span class="nav-item-text">Installment History</span>
                        </a>
                    <a href="{{ route('admin.installments.overdue') }}" class="nav-item {{ request()->routeIs('admin.installments.overdue') ? 'active' : '' }}">
                        <span class="nav-item-icon">⚠️</span>
                        <span class="nav-item-text">Overdue Installments</span>
                    </a>
                    <a href="{{ route('admin.installments.sms-reminder') }}" class="nav-item {{ request()->routeIs('admin.installments.sms-reminder') ? 'active' : '' }}">
                        <span class="nav-item-icon">📱</span>
                        <span class="nav-item-text">SMS Reminder</span>
                    </a>
                    <a href="{{ route('admin.expenses.index') }}" class="nav-item {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">💰</span>
                        <span class="nav-item-text">Expenses</span>
                    </a>
                    <a href="{{ route('admin.expense-categories.index') }}" class="nav-item {{ request()->routeIs('admin.expense-categories.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">📂</span>
                        <span class="nav-item-text">Expense Categories</span>
                    </a>
                    <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">Reports</div>
                    <a href="{{ route('admin.reports.sales') }}" class="nav-item {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                        <span class="nav-item-icon">📈</span>
                        <span class="nav-item-text">Sales Report</span>
                    </a>
                  <!--  <a href="{{ route('admin.warranties.index') }}"
                        class="nav-item {{ request()->routeIs('admin.warranties.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">📄</span>
                            <span class="nav-item-text">Warranties</span>
                        </a>

                        <a href="{{ route('admin.warranty-jobs.index') }}"
                        class="nav-item {{ request()->routeIs('admin.warranty-jobs.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">🛠</span>
                            <span class="nav-item-text">Warranty Jobs</span>-->
                        </a>
                    <a href="{{ route('admin.reports.stock') }}" class="nav-item {{ request()->routeIs('admin.reports.stock') ? 'active' : '' }}">
                        <span class="nav-item-icon">📦</span>
                        <span class="nav-item-text">Stock Report</span>
                    </a>
                    <a href="{{ route('admin.reports.profit-loss') }}" class="nav-item {{ request()->routeIs('admin.reports.profit-loss') ? 'active' : '' }}">
                        <span class="nav-item-icon">💵</span>
                        <span class="nav-item-text">Profit & Loss</span>
                    </a>
                     <a href="{{ route('admin.reports.paid-off-agreements') }}" class="nav-item {{ request()->routeIs('admin.reports.paid-off-agreements') ? 'active' : '' }}">
                        <span class="nav-item-icon">✅</span>
                        <span class="nav-item-text">Paid Off Agreements</span>
                    </a>
                    <a href="{{ route('admin.reports.installment-income') }}" class="nav-item {{ request()->routeIs('admin.reports.installment-income') ? 'active' : '' }}">
                        <span class="nav-item-icon">📈</span>
                        <span class="nav-item-text">Installment Income</span>
                    </a>
                   <!--  <a href="{{ route('admin.reports.delay-payments') }}" class="nav-item {{ request()->routeIs('admin.reports.delay-payments') ? 'active' : '' }}">
                        <span class="nav-item-icon">⏰</span>
                        <span class="nav-item-text">Delay Payments Report</span>
                    </a>-->
                    <a href="{{ route('admin.reports.daily-installments') }}" class="nav-item {{ request()->routeIs('admin.reports.daily-installments') ? 'active' : '' }}">
                        <span class="nav-item-icon">🗓️</span>
                        <span class="nav-item-text">Daily Installment Details</span>
                    </a>
                     <a href="{{ route('admin.reports.cash-collection') }}" class="nav-item {{ request()->routeIs('admin.reports.cash-collection') ? 'active' : '' }}">
                        <span class="nav-item-icon">💰</span>
                        <span class="nav-item-text">Cash Collection Report</span>
                    </a>
                    <a href="{{ route('admin.reports.online-collection') }}" 
                        class="nav-item {{ request()->routeIs('online-collection') ? 'active' : '' }}">
                            <span class="nav-item-icon">💻</span>
                            <span class="nav-item-text">Online Collection</span>
                        </a>
  <!--<a href="{{ route('admin.reports.on-date-installments') }}" class="nav-item {{ request()->routeIs('admin.reports.on-date-installments') ? 'active' : '' }}">
                        <span class="nav-item-icon">📅</span>
                        <span class="nav-item-text">On Date Installments</span>
                    </a>-->
                  
   <a href="{{ route('admin.estimate-report') }}" 
   class="nav-item {{ request()->routeIs('admin.estimate-report') ? 'active' : '' }}">
    <span class="nav-item-icon">📊</span>
    <span class="nav-item-text">Estimate Report</span>
</a>  

<a href="{{ route('admin.reports.estimate-collection') }}" 
   class="nav-item {{ request()->routeIs('admin.reports.estimate-collection') ? 'active' : '' }}">
    <span class="nav-item-icon">📊</span>
    <span class="nav-item-text">Estimate Collection</span>
</a>


<a href="{{ route('admin.reports.outstanding-installments') }}" class="nav-item {{ request()->routeIs('admin.reports.outstanding-installments') ? 'active' : '' }}">
    
                        <span class="nav-item-icon">❗</span>
                        <span class="nav-item-text">Outstanding Installments</span>
                    </a>
                    <a href="{{ route('admin.reports.disconnect') }}" class="nav-item {{ request()->routeIs('admin.reports.disconnect') ? 'active' : '' }}">
                        <span class="nav-item-icon">🚫</span>
                        <span class="nav-item-text">Disconnect Report</span>
                    </a>
                    <!--<a href="{{ route('admin.reports.customer-behavior') }}" class="nav-item {{ request()->routeIs('admin.reports.customer-behavior') ? 'active' : '' }}">
                        <span class="nav-item-icon">👤</span>
                        <span class="nav-item-text">Customer Behavior</span>
                    </a>
                    <a href="{{ route('admin.reports.sales-growth') }}" class="nav-item {{ request()->routeIs('admin.reports.sales-growth') ? 'active' : '' }}">
                        <span class="nav-item-icon">📊</span>
                        <span class="nav-item-text">Sales Growth</span>
                    </a>
                    <a href="{{ route('admin.reports.sales-tracking') }}" class="nav-item {{ request()->routeIs('admin.reports.sales-tracking') ? 'active' : '' }}">
                        <span class="nav-item-icon">📍</span>
                        <span class="nav-item-text">Sales Tracking</span>
                    </a>-->
                    <a href="{{ route('admin.reports.daily-installment-income') }}" class="nav-item {{ request()->routeIs('admin.reports.daily-installment-income') ? 'active' : '' }}">
                        <span class="nav-item-icon">💰</span>
                        <span class="nav-item-text">Daily Installment Income</span>
                    </a>
                    <a href="{{ route('admin.reports.supplier-ledger') }}" class="nav-item {{ request()->routeIs('admin.reports.supplier-ledger') ? 'active' : '' }}">
                        <span class="nav-item-icon">📋</span>
                        <span class="nav-item-text">Supplier Ledger</span>
                    </a>
                    <a href="{{ route('admin.reports.expenses') }}" class="nav-item {{ request()->routeIs('admin.reports.expenses') ? 'active' : '' }}">
                        <span class="nav-item-icon">💰</span>
                        <span class="nav-item-text">Expenses Report</span>
                    </a>
                    <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">Management</div>
                  <!--  <a href="{{ route('admin.sale-approvals.index') }}" class="nav-item {{ request()->routeIs('admin.sale-approvals.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">✅</span>
                        <span class="nav-item-text">Approvals</span>
                    </a>
                    <a href="{{ route('admin.shift.summary') }}" class="nav-item {{ request()->routeIs('admin.shift.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">📊</span>
                        <span class="nav-item-text">Shift Summary</span>
                    </a>-->
                    <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">⚙️</span>
                        <span class="nav-item-text">Store Settings</span>
                    </a>
                    <a href="{{ route('admin.system-settings.index') }}" class="nav-item {{ request()->routeIs('admin.system-settings.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">🛠️</span>
                        <span class="nav-item-text">System Settings</span>
                    </a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="nav-item {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">📝</span>
                        <span class="nav-item-text">Logs</span>
                    </a>
                   <!-- <a href="{{ route('admin.sms-campaigns.index') }}" class="nav-item {{ request()->routeIs('admin.sms-campaigns.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">📱</span>
                        <span class="nav-item-text">SMS Campaigns</span>
                    </a>-->
                    <a href="{{ route('admin.sms-templates.index') }}" class="nav-item {{ request()->routeIs('admin.sms-templates.*') ? 'active' : '' }}">
                        <span class="nav-item-icon">✉️</span>
                        <span class="nav-item-text">SMS Templates</span>
                    </a>
                @else
                    @if(auth()->user()->hasPermission('dashboard'))
                        <a href="{{ route('cashier.dashboard') }}" class="nav-item {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
                            <span class="nav-item-icon">🏠</span>
                            <span class="nav-item-text">Dashboard</span>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('billing'))
                        <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">Sales & Inventory</div>
                        <a href="{{ route('cashier.pos') }}" class="nav-item {{ request()->routeIs('cashier.pos') ? 'active' : '' }}">
                            <span class="nav-item-icon">💰</span>
                            <span class="nav-item-text">POS</span>
                        </a>
                        <a href="{{ route('cashier.items.index') }}" class="nav-item {{ request()->routeIs('cashier.items.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">📦</span>
                            <span class="nav-item-text">Items</span>
                        </a>
                        <a href="{{ route('cashier.grns.index') }}" class="nav-item {{ request()->routeIs('cashier.grns.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">📥</span>
                            <span class="nav-item-text">GRN</span>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('customers'))
                        <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">People</div>
                        <a href="{{ route('cashier.customers.index') }}" class="nav-item {{ request()->routeIs('cashier.customers.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">👥</span>
                            <span class="nav-item-text">Customers</span>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('payments'))
                        <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">Financial</div>
                        <a href="{{ route('cashier.credits.index') }}" class="nav-item {{ request()->routeIs('cashier.credits.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">💳</span>
                            <span class="nav-item-text">Credits</span>
                        </a>
                        <a href="{{ route('cashier.expenses.index') }}" class="nav-item {{ request()->routeIs('cashier.expenses.*') ? 'active' : '' }}">
                            <span class="nav-item-icon">💰</span>
                            <span class="nav-item-text">Expenses</span>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('reports'))
                        <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">Reports</div>
                        <a href="{{ route('cashier.reports.stock') }}" class="nav-item {{ request()->routeIs('cashier.reports.stock') ? 'active' : '' }}">
                            <span class="nav-item-icon">📦</span>
                            <span class="nav-item-text">Stock Report</span>
                        </a>
                        <a href="{{ route('cashier.reports.supplier-ledger') }}" class="nav-item {{ request()->routeIs('cashier.reports.supplier-ledger') ? 'active' : '' }}">
                            <span class="nav-item-icon">📋</span>
                            <span class="nav-item-text">Supplier Ledger</span>
                        </a>
                        <a href="{{ route('cashier.reports.expenses') }}" class="nav-item {{ request()->routeIs('cashier.reports.expenses') ? 'active' : '' }}">
                            <span class="nav-item-icon">💰</span>
                            <span class="nav-item-text">Expenses Report</span>
                        </a>
                        <a href="{{ route('cashier.warranty.index') }}" class="nav-item {{ request()->routeIs('cashier.warranty.*') ? 'active' : '' }}">
    <span class="nav-item-icon">🛡️</span>
    <span class="nav-item-text">Warranty Management</span>
</a>
                    @endif

                    @if(auth()->user()->hasPermission('audit_logs'))
                        <div class="text-red-200 text-xs font-semibold px-4 py-2 mt-4 mb-2 uppercase tracking-wider">Management</div>
                        <a href="{{ route('cashier.audit-logs.index') }}" class="nav-item {{ request()->routeIs('cashier.audit-logs.index') ? 'active' : '' }}">
                            <span class="nav-item-icon">📝</span>
                            <span class="nav-item-text">Logs</span>
                        </a>
                    @endif
                @endif
            </nav>
            
            <div >
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('password.change') }}" class="nav-item mb-2">
                    <span class="nav-item-icon">🔒</span>
                    <span class="nav-item-text">Password</span>
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="nav-item w-full text-left bg-red-700 hover:bg-red-600">
                        <span class="nav-item-icon">🚪</span>
                        <span class="nav-item-text">Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="flex items-center space-x-4">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
                </div>
                <div class="text-sm text-gray-600">
                    {{ now()->format('l, F j, Y') }}
                </div>
            </div>

            <main class="p-6">
                @if(session('success'))
                    <div class="mb-6 bg-green-500 border-l-4 border-green-700 text-black px-6 py-4 rounded-lg shadow-lg flex items-center" role="alert">
                        <span class="text-xl mr-3">✅</span>
                        <span class="text-lg font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('session_expired'))
                    <div class="mb-6 bg-yellow-500 border-l-4 border-yellow-700 text-white px-6 py-4 rounded-lg shadow-lg" role="alert">
                        <div class="flex items-center">
                            <span class="text-xl mr-3">⚠️</span>
                            <div>
                                <span class="text-lg font-semibold block mb-1">Session Expired</span>
                                <span class="text-base">Your session has expired. The page will refresh automatically in 3 seconds...</span>
                            </div>
                        </div>
                    </div>
                    <script>
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
                    </script>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-500 border-l-4 border-red-700 text-white px-6 py-4 rounded-lg shadow-lg" role="alert">
                        <div class="flex items-center mb-2">
                            <span class="text-xl mr-3">❌</span>
                            <span class="text-lg font-semibold">Error!</span>
                        </div>
                        <ul class="list-disc list-inside ml-6">
                            @foreach($errors->all() as $error)
                                <li class="text-base">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    @endauth
    
    @guest
        <main class="min-h-screen">
            @yield('content')
      
        </main>
    @endguest
    
    <script>
         console.log("🟢 LAYOUT JS LOADED");
        // Optimize page loading - reduce unnecessary operations
        // Use requestIdleCallback for non-critical tasks
        if ('requestIdleCallback' in window) {
            requestIdleCallback(function() {
                // Prefetch links on idle
                const links = document.querySelectorAll('a[href]');
                links.forEach(function(link) {
                    if (link.href && !link.href.includes('#')) {
                        link.setAttribute('rel', 'prefetch');
                    }
                });
            });
        }
        
        // Optimize navigation clicks - show immediate feedback
        document.querySelectorAll('.nav-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                // Immediate visual feedback
                if (this.href && !this.href.includes('#')) {
                    this.style.opacity = '0.7';
                    // Remove opacity after navigation
                    setTimeout(function() {
                        if (item.style) {
                            item.style.opacity = '';
                        }
                    }, 100);
                }
            });
        });
    </script>
          @stack('scripts')
</body>
</html>


