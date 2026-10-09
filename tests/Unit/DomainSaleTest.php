<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Model\Sale;
use App\Domain\Model\SaleItem;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\SaleId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DomainSaleTest extends TestCase
{
    public function test_sale_item_calculates_subtotal_in_memory(): void
    {
        $item = new SaleItem(
            id: 'item-1',
            productId: 'prod-1',
            productName: 'Camisa Polo',
            categoryName: 'Ropa',
            quantity: new Quantity(3),
            unitPrice: Money::fromFloat(45000.00)
        );

        $subtotal = $item->calculateSubtotal();
        $this->assertSame('135000.00', $subtotal->amountString());
    }

    public function test_sale_calculates_total_in_memory_without_stored_column(): void
    {
        $item1 = new SaleItem(
            id: 'item-1',
            productId: 'prod-1',
            productName: 'Camisa Polo',
            categoryName: 'Ropa',
            quantity: new Quantity(2),
            unitPrice: Money::fromFloat(45000.00)
        );

        $item2 = new SaleItem(
            id: 'item-2',
            productId: 'prod-2',
            productName: 'Gorra Deportiva',
            categoryName: 'Accesorios',
            quantity: new Quantity(1),
            unitPrice: Money::fromFloat(25000.00)
        );

        $sale = new Sale(
            id: SaleId::fromString('22222222-2222-2222-2222-222222222222'),
            soldByUserId: UserId::fromString('33333333-3333-3333-3333-333333333333'),
            soldByUsername: 'vendedor1',
            soldAt: new DateTimeImmutable('2026-10-02 14:00:00'),
            items: [$item1, $item2]
        );

        $total = $sale->calculateTotal();
        $this->assertSame('115000.00', $total->amountString());
        $this->assertSame('COP', $total->currency());
    }
}

