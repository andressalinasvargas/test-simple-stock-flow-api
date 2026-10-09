<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidQuantityException;

final class Quantity
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidQuantityException("La cantidad debe ser mayor que cero. Valor recibido: {$value}.");
        }
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
