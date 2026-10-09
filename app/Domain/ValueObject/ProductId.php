<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\BusinessRuleViolation;

final class ProductId
{
    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new BusinessRuleViolation('El identificador del producto no puede estar vacío.');
        }
        $this->value = $trimmed;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return new self(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
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
