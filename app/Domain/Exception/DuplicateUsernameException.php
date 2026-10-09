<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class DuplicateUsernameException extends BusinessRuleViolation
{
    public function __construct(string $username)
    {
        parent::__construct(sprintf("El nombre de usuario '%s' ya está registrado.", $username));
    }
}

