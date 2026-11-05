<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\User;
use App\Services\Authenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HouseholdMemberController extends Controller
{
    /**
     * Adds an existing user, found by email, to the household. Managers only.
     */
    public function store(Request $request, Authenticator $authenticator, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $user = User::query()->where('email', (string) $request->input('email'))->first();

        if ($user === null) {
            $status = 'User does not exist.';
        } elseif ($household->members()->where('user_id', $user->id)->exists()) {
            $status = 'User is already in the household.';
        } else {
            $household->members()->create([
                'user_id' => $user->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
            $status = 'User was added.';
        }

        return redirect()->route('households.show', $household->id)->with('status', $status);
    }
}
