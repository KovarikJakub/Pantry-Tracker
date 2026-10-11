<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Application\UserRegistrar;
use Modules\Identity\Domain\EmailAlreadyRegistered;

class RegisterController
{
    public function create(): View
    {
        return view('identity::register');
    }

    public function store(Request $request, UserRegistrar $registrar): RedirectResponse
    {
        try {
            $registrar->register(
                (string) $request->input('email'),
                (string) $request->input('password'),
                (string) $request->input('first_name'),
                (string) $request->input('last_name'),
            );
        } catch (EmailAlreadyRegistered) {
            throw ValidationException::withMessages(['email' => 'A user with this email already exists.']);
        } catch (QueryException) {
            throw ValidationException::withMessages(['email' => 'Registration failed, please try again.']);
        }

        return redirect()->route('login')->with('status', 'Account created, you can log in now.');
    }
}
