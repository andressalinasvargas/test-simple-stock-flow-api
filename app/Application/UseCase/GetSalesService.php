<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\PageRequest;
use App\Application\DTO\PagedResult;
use App\Application\DTO\SaleView;
use App\Application\Ports\Inbound\GetSales;
use App\Application\Ports\Outbound\SaleRepository;
use DateTimeImmutable;
use DomainException;

final class GetSalesService implements GetSales
{
    private SaleRepository $saleRepository;

    public function __construct(SaleRepository $saleRepository)
    {
        $this->saleRepository = $saleRepository;
    }

    public function listSales(DateTimeImmutable $from, DateTimeImmutable $to, PageRequest $pageRequest): PagedResult
    {
        if ($to < $from) {
            throw new \App\Domain\Exception\BusinessRuleViolation('La fecha final no puede ser anterior a la inicial.');
        }

        return $this->saleRepository->listPaginated($from, $to, $pageRequest);
    }

    public function getSale(string $id): SaleView
    {
        $sale = $this->saleRepository->findById($id);
        if ($sale === null) {
            throw new DomainException("Venta no encontrada con id '{$id}'.");
        }

        $items = [];
        foreach ($sale->getItems() as $item) {
            $items[] = new \App\Application\DTO\SaleItemView(
                $item->getId(),
                $item->getProductId(),
                $item->getProductName(),
                $item->getCategoryName(),
                $item->getQuantity()->getValue(),
                $item->getUnitPrice()->toFloat(),
                $item->calculateSubtotal()->toFloat()
            );
        }

        return new SaleView(
            $sale->getId(),
            $sale->getSoldAt(),
            $sale->getSoldByUsername()->getValue(),
            $sale->getTotal()->toFloat(),
            $items,
            'COP'
        );
    }

    public function getSales(DateTimeImmutable $from, DateTimeImmutable $to, int $page = 1, int $size = 20): PagedResult
    {
        return $this->listSales($from, $to, new PageRequest($page, $size));
    }

    public function getSaleById(string $id): SaleView
    {
        return $this->getSale($id);
    }
}

