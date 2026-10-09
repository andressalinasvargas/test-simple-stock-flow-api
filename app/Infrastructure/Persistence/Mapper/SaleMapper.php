<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Model\Sale;
use App\Domain\Model\SaleItem;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Username;
use App\Infrastructure\Persistence\Model\SaleItemModel;
use App\Infrastructure\Persistence\Model\SaleModel;
use DateTimeImmutable;

final class SaleMapper
{
    public static function toDomain(SaleModel $model): Sale
    {
        $soldAt = new DateTimeImmutable($model->sold_at->format('Y-m-d H:i:s.u'));
        $username = new Username((string) $model->sold_by_username);

        $items = [];
        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $items[] = self::toItemDomain($itemModel);
            }
        }

        return new Sale(
            (string) $model->id,
            $soldAt,
            $username,
            (string) $model->sold_by_user_id,
            $items
        );
    }

    public static function toItemDomain(SaleItemModel $itemModel): SaleItem
    {
        return new SaleItem(
            (string) $itemModel->id,
            (string) $itemModel->sale_id,
            (string) $itemModel->product_id,
            (string) $itemModel->product_name,
            (string) $itemModel->category_name,
            new Quantity((int) $itemModel->quantity),
            Money::of((string) $itemModel->unit_price)
        );
    }
}
