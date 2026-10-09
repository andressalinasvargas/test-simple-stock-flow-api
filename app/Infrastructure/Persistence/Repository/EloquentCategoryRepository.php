<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Ports\Outbound\CategoryRepository;
use App\Domain\Model\Category;
use App\Infrastructure\Persistence\Mapper\CategoryMapper;
use App\Infrastructure\Persistence\Model\CategoryModel;

final class EloquentCategoryRepository implements CategoryRepository
{
    public function findById(string $id): ?Category
    {
        $model = CategoryModel::find($id);
        return $model !== null ? CategoryMapper::toDomain($model) : null;
    }

    public function findAll(): array
    {
        $models = CategoryModel::orderBy('name', 'asc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = CategoryMapper::toDomain($model);
        }
        return $result;
    }
}
