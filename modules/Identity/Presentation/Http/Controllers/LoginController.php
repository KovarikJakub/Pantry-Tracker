<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Application\Authenticator;
use Modules\Identity\Presentation\Http\Requests\LoginRequest;

class LoginController
{
    public function create(): View
    {
        return view('identity::login');
    }

    public function store(LoginRequest $request, Authenticator $authenticator): RedirectResponse
    {
        if (! $authenticator->attempt($request->validated('email'), $request->validated('password'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        return redirect()->intended(route('home'));
    }
}
