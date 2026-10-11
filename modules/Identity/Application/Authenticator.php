<?php

namespace Modules\Identity\Application;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Session\Session;
use Modules\Identity\Domain\IUserRepository;
use Modules\Identity\Domain\User;

/**
 * Our own session-based authentication: verifies credentials and remembers
 * the logged-in user's id in the session.
 */
class Authenticator
{
    private const SESSION_KEY = 'identity.user_id';

    public function __construct(
        private IUserRepository $usersRepository,
        private Hasher $hasher,
        private Session $session,
    ) {}

    public function attempt(string $email, string $password): bool
    {
        $user = $this->usersRepository->findByEmail($email);

        if ($user === null || $user->isDeactivated || ! $this->hasher->check($password, $user->passwordHash)) {
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

        $user = $this->usersRepository->findById($userId);

        return $user !== null && ! $user->isDeactivated ? $user : null;
    }

    public function logout(): void
    {
        $this->session->invalidate();
        $this->session->regenerateToken();
    }
}
