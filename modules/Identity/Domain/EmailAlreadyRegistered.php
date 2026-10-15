<?php

namespace Modules\Identity\Domain;

use DomainException;

class EmailAlreadyRegistered extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct("The email {$email} is already registered.");
    }
}
