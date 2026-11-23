<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\Authenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller {

    public function __construct(private Authenticator $authenticator) {}

    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $this->authenticator->user(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $this->authenticator->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->authenticator->user()->update($request->validated());

        return redirect()
            ->route('profile.show')
            ->with('status', 'Profile updated successfully.');
    }

    public function deactivate(Request $request, Authenticator $authenticator): RedirectResponse
    {
        $user = $authenticator->user();

        $user->update(['deactivated' => true]);

        $authenticator->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Your account has been deactivated.');
    }
}
