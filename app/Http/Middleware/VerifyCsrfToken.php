<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Logout should work even with expired tokens (user wants to logout anyway)
        // But we'll handle it in the controller instead for better security
    ];

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $token = $request->input('_token') ?: 
                 $request->header('X-CSRF-TOKEN') ?: 
                 $request->header('X-XSRF-TOKEN');

        $sessionToken = $request->session()->token();

        return is_string($sessionToken) &&
               is_string($token) &&
               hash_equals($sessionToken, $token);
    }
}

