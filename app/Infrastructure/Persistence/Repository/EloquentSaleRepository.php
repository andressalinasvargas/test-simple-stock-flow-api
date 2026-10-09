<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\DTO\PageRequest;
use App\Application\DTO\PagedResult;
use App\Application\DTO\SaleItemView;
use App\Application\DTO\SaleView;
use App\Application\Ports\Outbound\SaleRepository;
use App\Domain\Model\Sale;
use App\Infrastructure\Persistence\Mapper\SaleMapper;
use App\Infrastructure\Persistence\Model\SaleItemModel;
use App\Infrastructure\Persistence\Model\SaleModel;
use DateTimeImmutable;

final class EloquentSaleRepository implements SaleRepository
{
    public function save(Sale $sale): void
    {
        SaleModel::create([
            'id' => $sale->getId(),
            'sold_at' => $sale->getSoldAt()->format('Y-m-d H:i:s.u'),
            'sold_by_username' => $sale->getSoldByUsername()->getValue(),
            'sold_by_user_id' => $sale->getSoldByUserId(),
        ]);

        foreach ($sale->getItems() as $item) {
            SaleItemModel::create([
                'id' => $item->getId(),
                'sale_id' => $item->getSaleId(),
                'product_id' => $item->getProductId(),
                'product_name' => $item->getProductName(),
                'category_name' => $item->getCategoryName(),
                'quantity' => $item->getQuantity()->getValue(),
                'unit_price' => (string) $item->getUnitPrice()->getAmount(),
            ]);
        }
    }

    public function findById(string $id): ?Sale
    {
        $model = SaleModel::with('items')->find($id);
        return $model !== null ? SaleMapper::toDomain($model) : null;
    }

    public function listPaginated(DateTimeImmutable $from, DateTimeImmutable $to, PageRequest $pageRequest): PagedResult
    {
        $fromStr = $from->format('Y-m-d H:i:s.u');
        $toStr = $to->format('Y-m-d H:i:s.u');

        $query = SaleModel::with('items')
            ->where('sold_at', '>=', $fromStr)
            ->where('sold_at', '<=', $toStr);

        $totalItems = $query->count();

        $models = $query->orderBy('sold_at', 'desc')
            ->offset($pageRequest->getOffset())
            ->limit($pageRequest->getPerPage())
            ->get();

        $items = [];
        foreach ($models as $model) {
            $sale = SaleMapper::toDomain($model);
            $viewItems = [];
            foreach ($sale->getItems() as $item) {
                $viewItems[] = new SaleItemView(
                    $item->getId(),
                    $item->getProductId(),
                    $item->getProductName(),
                    $item->getCategoryName(),
                    $item->getQuantity()->getValue(),
                    $item->getUnitPrice()->toFloat(),
                    $item->calculateSubtotal()->toFloat()
                );
            }

            $items[] = new SaleView(
                $sale->getId(),
                $sale->getSoldAt(),
                $sale->getSoldByUsername()->getValue(),
                $sale->getTotal()->toFloat(),
                $viewItems,
                'COP'
            );
        }

        return new PagedResult($items, $totalItems, $pageRequest->getPage(), $pageRequest->getPerPage());
    }
}
