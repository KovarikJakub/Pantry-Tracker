<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Session\Session;

/**
 * Our own session-based authentication: verifies credentials and remembers
 * the logged-in user's id in the session.
 */
class Authenticator
{
    private const SESSION_KEY = 'user_id';

    public function __construct(
        private Hasher $hasher,
        private Session $session,
    ) {}

    public function attempt(string $email, string $password): bool
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || $user->deactivated || ! $this->hasher->check($password, $user->password_hash)) {
            return false;
        }

        // New session id after login prevents session fixation attacks.
        $this->session->regenerate();
        $this->session->put(self::SESSION_KEY, $user->id);

        return true;
    }

    public function user(): ?User
    {
        $userId = $this->session->get(self::SESSION_KEY);

        if ($userId === null) {
            return null;
        }

        $user = User::query()->find($userId);

        return $user !== null && ! $user->deactivated ? $user : null;
    }

    public function logout(): void
    {
        $this->session->invalidate();
        $this->session->regenerateToken();
    }
}
