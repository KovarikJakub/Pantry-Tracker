<?php

namespace App\Http\Middleware;

use App\Services\Authenticator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets only admins through. Must run after Authenticate, which handles guests.
 */
class AuthenticateAdmin
{
    public function __construct(private Authenticator $authenticator) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->authenticator->user()?->is_admin) {
            abort(403);
        }

        return $next($request);
    }
}
