<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Authenticator;
use Illuminate\Http\RedirectResponse;

class LogoutController extends Controller
{
    public function __invoke(Authenticator $authenticator): RedirectResponse
    {
        $authenticator->logout();

        return redirect()->route('login');
    }
}
