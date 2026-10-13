<?php

namespace App\Http\Middleware;

use App\Services\Authenticator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps logged-in users away from guest-only pages such as the login form.
 */
class RedirectIfAuthenticated
{
    public function __construct(private Authenticator $authenticator) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->authenticator->user() !== null) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
