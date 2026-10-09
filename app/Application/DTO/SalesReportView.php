<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class SalesReportView
{
    private string $from;
    private string $to;
    private int $salesCount;
    private float $grandTotal;
    private string $currency;
    /** @var SalesReportRowView[] */
    private array $rows;

    /**
     * @param SalesReportRowView[] $rows
     */
    public function __construct(
        string $from,
        string $to,
        int $salesCount,
        float $grandTotal,
        array $rows,
        string $currency = 'COP'
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->salesCount = $salesCount;
        $this->grandTotal = $grandTotal;
        $this->rows = $rows;
        $this->currency = $currency;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSalesCount(): int
    {
        return $this->salesCount;
    }

    public function getGrandTotal(): float
    {
        return $this->grandTotal;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return SalesReportRowView[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}

