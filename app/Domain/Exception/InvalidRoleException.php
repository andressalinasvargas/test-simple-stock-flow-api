<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidRoleException extends BusinessRuleViolation
{
    public function __construct(string $role)
    {
        parent::__construct(sprintf("El rol '%s' no es válido. Los roles permitidos son 'admin' y 'seller'.", $role));
    }
}

