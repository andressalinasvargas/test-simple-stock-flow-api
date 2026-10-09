<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use App\Domain\Exception\InvalidPriceException;
use App\Domain\Exception\BusinessRuleViolation;

final class Money
{
    private const CURRENCY = 'COP';
    private BigDecimal $amount;
    private string $currency;

    private function __construct(BigDecimal $amount, string $currency = self::CURRENCY)
    {
        if ($currency !== self::CURRENCY) {
            throw new BusinessRuleViolation("Moneda no permitida '{$currency}'. La única moneda aceptada es " . self::CURRENCY . ".");
        }

        if ($amount->isNegative()) {
            throw new InvalidPriceException('El precio no puede ser negativo.');
        }

        // Article VI & Decision 1: Reject amounts with more than 2 decimal places
        if ($amount->getScale() > 2) {
            throw new InvalidPriceException('El importe no puede tener más de dos decimales.');
        }

        // Article VI & Decision 1: Scale 2, HALF_UP rounding
        $this->amount = $amount->toScale(2, RoundingMode::HALF_UP);
        $this->currency = $currency;
    }

    public static function of(string|int|float|BigDecimal $amount, string $currency = self::CURRENCY): self
    {
        $bigDecimal = $amount instanceof BigDecimal ? $amount : BigDecimal::of((string) $amount);
        return new self($bigDecimal, $currency);
    }

    public static function zero(string $currency = self::CURRENCY): self
    {
        return new self(BigDecimal::zero(), $currency);
    }

    public function add(self $other): self
    {
        return new self($this->amount->plus($other->amount), $this->currency);
    }

    public function multiply(int $quantity): self
    {
        return new self($this->amount->multipliedBy($quantity), $this->currency);
    }

    public static function fromFloat(float $amount, string $currency = self::CURRENCY): self
    {
        return self::of($amount, $currency);
    }

    public function getAmount(): BigDecimal
    {
        return $this->amount;
    }

    public function amountString(): string
    {
        return (string) $this->amount;
    }

    public function getAmountString(): string
    {
        return (string) $this->amount;
    }

    public function toFloat(): float
    {
        return $this->amount->toFloat();
    }

    public function amountFloat(): float
    {
        return $this->amount->toFloat();
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function isPositive(): bool
    {
        return $this->amount->isPositive();
    }

    public function equals(self $other): bool
    {
        return $this->amount->isEqualTo($other->amount) && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return (string) $this->amount;
    }
}
