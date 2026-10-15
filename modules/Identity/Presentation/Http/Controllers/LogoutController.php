<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\Identity\Application\Authenticator;

class LogoutController
{
    public function __invoke(Authenticator $authenticator): RedirectResponse
    {
        $authenticator->logout();

        return redirect()->route('login');
    }
}
