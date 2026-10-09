<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidRoleException;

final class Role
{
    public const ADMIN = 'admin';
    public const SELLER = 'seller';

    private const ALLOWED = [self::ADMIN, self::SELLER];

    public readonly string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidRoleException("Rol no permitido '{$value}'. Los roles válidos son: admin, seller.");
        }
        $this->value = $normalized;
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function seller(): self
    {
        return new self(self::SELLER);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    public function isSeller(): bool
    {
        return $this->value === self::SELLER;
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

