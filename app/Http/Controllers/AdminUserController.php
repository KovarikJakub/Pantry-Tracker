<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Authenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private Authenticator $authenticator) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('deactivated')
            ->orderByDesc('is_admin')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'currentUserId' => $this->authenticator->user()?->id,
        ]);
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        if ($user->id === $this->authenticator->user()?->id) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        if ($user->deactivated) {
            return back()->with('error', 'Deactivated accounts cannot be modified.');
        }

        $user->update(['is_admin' => ! $user->is_admin]);

        return back()->with('status', "{$user->first_name} {$user->last_name} is now ".($user->is_admin ? 'an admin' : 'a regular user').'.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === $this->authenticator->user()?->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->deactivated) {
            return back()->with('error', 'Deactivated accounts cannot be modified.');
        }

        $name = "{$user->first_name} {$user->last_name}";
        $user->delete();

        return back()->with('status', "User {$name} was deleted.");
    }

    public function activate(User $user): RedirectResponse
    {
        if (! $user->deactivated) {
            return back()->with('error', 'This account is already active.');
        }

        $user->update(['deactivated' => false]);

        return back()->with('status', "{$user->first_name} {$user->last_name}'s account has been activated.");
    }
}
