<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - JC ENTERPRICES</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-red-50 to-white min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-xl border-2 border-red-200">
        <div class="text-center">
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
            @if($logoPath)
                <img src="{{ $logoPath }}" 
                     alt="JC Enterprices Logo" 
                     class="h-20 w-auto object-contain mx-auto mb-4 max-w-[300px]">
            @else
                <div class="flex justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-12 w-12 text-red-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 18h.01M8 4h8a2 2 0 012 2v12a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                    </svg>
                </div>
            @endif
            
            <h2 class="text-3xl font-bold text-gray-900">JC ENTERPRICES</h2>
            <p class="mt-2 text-sm text-gray-600">Sign in to your account</p>
        </div>
        
        @if(session('csrf_error'))
            <div class="mb-4 bg-yellow-500 border-l-4 border-yellow-700 text-white px-4 py-3 rounded-lg shadow-lg" role="alert">
                <div class="flex items-center">
                    <span class="text-lg mr-2">⚠️</span>
                    <span class="text-sm font-semibold">Session expired. Please try logging in again.</span>
                </div>
            </div>
        @endif
        
        @if($errors->has('csrf_token'))
            <div class="mb-4 bg-red-500 border-l-4 border-red-700 text-white px-4 py-3 rounded-lg shadow-lg" role="alert">
                <div class="flex items-center">
                    <span class="text-lg mr-2">❌</span>
                    <span class="text-sm font-semibold">{{ $errors->first('csrf_token') }}</span>
                </div>
            </div>
        @endif
        
        <form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            
            <div class="mb-4 text-center">
                <button type="button" id="toggleLoginMode" class="text-sm text-red-600 hover:text-red-700 underline">
                    Switch to {{ old('login_mode') === 'pin' ? 'Email/Password' : 'PIN' }} Login
                </button>
            </div>
            
            <div class="space-y-4" id="emailPasswordLogin" style="display: {{ old('login_mode') === 'pin' ? 'none' : 'block' }};">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('email') border-red-500 @enderror"
                           value="{{ old('email') }}" autofocus>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" 
                               class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('password') border-red-500 @enderror">
                        <button type="button" id="togglePassword" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-red-600 focus:outline-none">
                            <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="space-y-4" id="pinLogin" style="display: {{ old('login_mode') === 'pin' ? 'block' : 'none' }};">
                <div>
                    <label for="pin" class="block text-sm font-medium text-gray-700">PIN (6 digits)</label>
                    <input id="pin" name="pin" type="text" maxlength="6" pattern="[0-9]{6}" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('pin') border-red-500 @enderror"
                           value="{{ old('pin') }}" autofocus placeholder="000000">
                    @error('pin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <input type="hidden" name="login_mode" id="login_mode" value="{{ old('login_mode', 'email') }}">
            
            <div class="flex items-center" id="rememberDiv" style="display: {{ old('login_mode') === 'pin' ? 'none' : 'flex' }};">
                <input id="remember" name="remember" type="checkbox" 
                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
            </div>
            
            <div>
               <button type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Sign in
                </button>
            </div>
            
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh CSRF token on page load to ensure it's fresh
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag && window.location.pathname === '/login') {
                fetch('/csrf-token', {
                    method: 'GET',
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.token) {
                        metaTag.setAttribute('content', data.token);
                        const tokenInput = document.querySelector('input[name="_token"]');
                        if (tokenInput) {
                            tokenInput.value = data.token;
                        }
                    }
                })
                .catch(err => {
                    console.log('CSRF token refresh failed:', err);
                });
            }
            
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');
            const toggleLoginMode = document.getElementById('toggleLoginMode');
            const emailPasswordLogin = document.getElementById('emailPasswordLogin');
            const pinLogin = document.getElementById('pinLogin');
            const rememberDiv = document.getElementById('rememberDiv');
            const loginMode = document.getElementById('login_mode');
            const pinInput = document.getElementById('pin');
            
            // Toggle password visibility
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if (type === 'text') {
                        eyeIcon.classList.add('hidden');
                        eyeSlashIcon.classList.remove('hidden');
                    } else {
                        eyeIcon.classList.remove('hidden');
                        eyeSlashIcon.classList.add('hidden');
                    }
                });
            }
            
            // Toggle login mode
            if (toggleLoginMode) {
                toggleLoginMode.addEventListener('click', function() {
                    const isPinMode = pinLogin.style.display === 'block';
                    
                    if (isPinMode) {
                        // Switch to email/password
                        emailPasswordLogin.style.display = 'block';
                        pinLogin.style.display = 'none';
                        rememberDiv.style.display = 'flex';
                        loginMode.value = 'email';
                        toggleLoginMode.textContent = 'Switch to PIN Login';
                        emailInput = document.getElementById('email');
                        if (emailInput) emailInput.focus();
                    } else {
                        // Switch to PIN
                        emailPasswordLogin.style.display = 'none';
                        pinLogin.style.display = 'block';
                        rememberDiv.style.display = 'none';
                        loginMode.value = 'pin';
                        toggleLoginMode.textContent = 'Switch to Email/Password Login';
                        if (pinInput) pinInput.focus();
                    }
                });
            }
            
            // PIN input - numbers only
            if (pinInput) {
                pinInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });
    </script>
</body>
</html>

