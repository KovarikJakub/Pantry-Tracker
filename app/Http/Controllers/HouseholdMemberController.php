<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class HouseholdMemberController extends Controller
{
    /**
     * Shows the household's members tab. Managers only.
     */
    public function index(string $id): View
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        return view('household.members', ['household' => $household]);
    }

    /**
     * Adds an existing user, found by email, to the household. Managers only.
     */
    public function store(Request $request, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

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

        return redirect()->route('households.members.index', $household->id)->with('status', $status);
    }

    /**
     * Removes a user from the household; the user account itself stays. Managers cannot be removed,
     * so a household always keeps at least one. Managers only.
     */
    public function destroy(string $id, string $memberId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        // The household_members row linking this user to this household.
        $membership = $household->members()->where('user_id', $memberId)->firstOrFail();

        if ($membership->role === 'manager') {
            throw ValidationException::withMessages([
                'member' => 'A manager cannot be removed from the household.',
            ])->errorBag('memberRemove');
        }

        $membership->delete();

        return redirect()->route('households.members.index', $household->id)
            ->with('status', 'User was removed.');
    }
}
