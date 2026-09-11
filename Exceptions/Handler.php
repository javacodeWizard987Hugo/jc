<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (TokenMismatchException $e, $request) {
            // Regenerate session token if session exists
            try {
                if ($request->hasSession()) {
                    $request->session()->regenerateToken();
                }
            } catch (\Exception $sessionException) {
                // Session might be invalid, continue anyway
            }
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                    'error' => 'CSRF token mismatch',
                    'refresh_required' => true
                ], 419);
            }

            // If this is a login request, redirect to login page with fresh token
            $isLoginRequest = $request->routeIs('login') || 
                             $request->is('login') || 
                             str_ends_with($request->path(), 'login');
            
            if ($isLoginRequest) {
                try {
                    return redirect()->route('login')
                        ->withInput($request->except('password', '_token', 'password_confirmation'))
                        ->withErrors([
                            'csrf_token' => 'Your session has expired. Please try logging in again.'
                        ])
                        ->with('csrf_error', true);
                } catch (\Exception $routeException) {
                    // If route() fails, use URL redirect
                    return redirect('/login')
                        ->withInput($request->except('password', '_token', 'password_confirmation'))
                        ->withErrors([
                            'csrf_token' => 'Your session has expired. Please try logging in again.'
                        ])
                        ->with('csrf_error', true);
                }
            }

            // For regular requests, try to redirect back, but fallback to login if not authenticated
            try {
                if (auth()->check()) {
                    return redirect()->back()
                        ->withInput($request->except('password', '_token', 'password_confirmation'))
                        ->withErrors([
                            'error' => 'Your session has expired. Please refresh the page and try again.'
                        ])
                        ->with('session_expired', true)
                        ->with('csrf_error', true);
                }
            } catch (\Exception $authException) {
                // Fall through to login redirect
            }

            // If user is not authenticated or redirect back failed, redirect to login
            return redirect()->route('login')
                ->withInput($request->except('password', '_token', 'password_confirmation'))
                ->withErrors([
                    'csrf_token' => 'Your session has expired. Please try logging in again.'
                ])
                ->with('csrf_error', true);
        });
    }
}

