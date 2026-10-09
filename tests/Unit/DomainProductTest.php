<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Exception\InsufficientStockException;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DomainProductTest extends TestCase
{
    public function test_can_deduct_stock_successfully(): void
    {
        $product = new Product(
            id: ProductId::fromString('11111111-1111-1111-1111-111111111111'),
            name: 'Café Especial',
            price: Money::fromFloat(12000.00),
            stock: 10,
            categoryId: 'cccccccc-cccc-cccc-cccc-cccccccccccc'
        );

        $product->deductStock(new Quantity(4));
        $this->assertSame(6, $product->getStock());
    }

    public function test_throws_when_deducting_more_than_available_stock(): void
    {
        $product = new Product(
            id: ProductId::fromString('11111111-1111-1111-1111-111111111111'),
            name: 'Café Especial',
            price: Money::fromFloat(12000.00),
            stock: 3,
            categoryId: 'cccccccc-cccc-cccc-cccc-cccccccccccc'
        );

        $this->expectException(InsufficientStockException::class);
        $product->deductStock(new Quantity(5));
    }

    public function test_soft_delete_is_idempotent(): void
    {
        $product = new Product(
            id: ProductId::fromString('11111111-1111-1111-1111-111111111111'),
            name: 'Café Especial',
            price: Money::fromFloat(12000.00),
            stock: 3,
            categoryId: 'cccccccc-cccc-cccc-cccc-cccccccccccc'
        );

        $now = new DateTimeImmutable('2026-10-02 12:00:00');
        $product->softDelete($now);

        $this->assertFalse($product->isActive());
        $this->assertSame($now, $product->getDeletedAt());

        // Repeated soft delete should NOT throw (idempotency ADR-003)
        $later = new DateTimeImmutable('2026-10-02 12:05:00');
        $product->softDelete($later);
        $this->assertFalse($product->isActive());
        $this->assertSame($later, $product->getDeletedAt());
    }
}

