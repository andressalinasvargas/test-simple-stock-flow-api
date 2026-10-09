<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Model\Category;

interface CategoryRepository
{
    public function findById(string $id): ?Category;

    /**
     * @return Category[]
     */
    public function findAll(): array;
}
