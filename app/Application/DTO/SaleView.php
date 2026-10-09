<?php

declare(strict_types=1);

namespace App\Application\DTO;

use DateTimeImmutable;

final class SaleView
{
    private string $id;
    private DateTimeImmutable $soldAt;
    private string $soldBy;
    private float $total;
    private string $currency;
    /** @var SaleItemView[] */
    private array $items;

    /**
     * @param SaleItemView[] $items
     */
    public function __construct(
        string $id,
        DateTimeImmutable $soldAt,
        string $soldBy,
        float $total,
        array $items,
        string $currency = 'COP'
    ) {
        $this->id = $id;
        $this->soldAt = $soldAt;
        $this->soldBy = $soldBy;
        $this->total = $total;
        $this->items = $items;
        $this->currency = $currency;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSoldAt(): DateTimeImmutable
    {
        return $this->soldAt;
    }

    public function getSoldBy(): string
    {
        return $this->soldBy;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return SaleItemView[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
