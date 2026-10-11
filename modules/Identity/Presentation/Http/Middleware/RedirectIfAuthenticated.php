<?php

namespace Modules\Identity\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Identity\Application\Authenticator;
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
