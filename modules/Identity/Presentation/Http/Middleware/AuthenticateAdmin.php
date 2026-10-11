<?php

namespace Modules\Identity\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Identity\Application\Authenticator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets only admins through. Must run after Authenticate, which handles guests.
 */
class AuthenticateAdmin
{
    public function __construct(private Authenticator $authenticator) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->authenticator->user()?->isAdmin) {
            abort(403);
        }

        return $next($request);
    }
}
