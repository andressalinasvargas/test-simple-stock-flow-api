<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Model\Product;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Infrastructure\Persistence\Model\ProductModel;

final class ProductMapper
{
    public static function toDomain(ProductModel $model): Product
    {
        $isActive = $model->deleted_at === null;

        return new Product(
            id: new ProductId((string) $model->id),
            name: (string) $model->name,
            price: Money::of((string) $model->price),
            stock: (int) $model->stock,
            categoryId: new CategoryId((string) $model->category_id),
            sku: null,
            isActive: $isActive,
            imageKey: $model->image_key !== null ? (string) $model->image_key : null
        );
    }

    public static function toModel(Product $domain, ?ProductModel $existing = null): ProductModel
    {
        $model = $existing ?? new ProductModel();

        $model->id = $domain->getId()->getValue();
        $model->name = $domain->getName();
        $model->price = $domain->getPrice()->getAmount()->toFloat();
        $model->stock = $domain->getStock();
        $model->category_id = $domain->getCategoryId()->getValue();
        $model->image_key = $domain->getImageKey();

        if ($existing === null) {
            $model->version = 1;
            $model->deleted_at = $domain->isActive() ? null : now();
        }

        return $model;
    }
}

