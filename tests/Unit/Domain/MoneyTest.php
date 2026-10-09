<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Exception\InvalidPriceException;
use App\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_can_create_valid_money(): void
    {
        $money = Money::fromFloat(1500.50);
        $this->assertSame('1500.50', $money->amountString());
        $this->assertSame('COP', $money->currency());
        $this->assertSame(1500.50, $money->amountFloat());
    }

    public function test_rejects_negative_money(): void
    {
        $this->expectException(InvalidPriceException::class);
        Money::fromFloat(-10.00);
    }

    public function test_rejects_more_than_two_decimals(): void
    {
        $this->expectException(InvalidPriceException::class);
        Money::of('100.555');
    }

    public function test_can_add_money(): void
    {
        $m1 = Money::fromFloat(100.25);
        $m2 = Money::fromFloat(50.75);
        $result = $m1->add($m2);

        $this->assertSame('151.00', $result->amountString());
    }

    public function test_can_multiply_money(): void
    {
        $m = Money::fromFloat(250.00);
        $result = $m->multiply(3);

        $this->assertSame('750.00', $result->amountString());
    }
}

