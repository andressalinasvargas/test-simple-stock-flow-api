<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\BusinessRuleViolation;

final class Username
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            throw new BusinessRuleViolation('El nombre de usuario no puede estar vacío.');
        }
        $this->value = $normalized;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
