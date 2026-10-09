<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidPriceException extends BusinessRuleViolation
{
    public function __construct(string $message = 'El precio debe ser un número positivo con hasta dos decimales y en COP.')
    {
        parent::__construct($message);
    }
}

