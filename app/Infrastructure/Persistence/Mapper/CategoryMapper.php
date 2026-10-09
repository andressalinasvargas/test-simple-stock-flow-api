<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Model\Category;
use App\Infrastructure\Persistence\Model\CategoryModel;

final class CategoryMapper
{
    public static function toDomain(CategoryModel $model): Category
    {
        return new Category(
            id: (string) $model->id,
            name: (string) $model->name
        );
    }

    public static function toModel(Category $category): CategoryModel
    {
        $model = new CategoryModel();
        $model->id = $category->getId();
        $model->name = $category->getName();

        return $model;
    }
}

