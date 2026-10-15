<?php

namespace App\Http\Middleware;

use App\Services\Authenticator;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets only logged-in users through; others are sent to the login page.
 */
class Authenticate
{
    public function __construct(private Authenticator $authenticator) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->authenticator->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        View::share('currentUser', $user);

        return $next($request);
    }
}
