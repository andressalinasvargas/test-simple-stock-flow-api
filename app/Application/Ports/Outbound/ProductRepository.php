<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Model\Product;
use App\Application\DTO\PageRequest;
use App\Application\DTO\PagedResult;

interface ProductRepository
{
    public function findById(string $id): ?Product;

    public function findActiveById(string $id): ?Product;

    /**
     * @param string[] $ids
     * @return array<string, Product>
     */
    public function findByIds(array $ids): array;

    /**
     * @param string[] $ids
     * @return array<Product>
     */
    public function findActiveByIds(array $ids): array;

    /**
     * Saves the product aggregate.
     * When $expectedVersion is provided, enforces optimistic concurrency check.
     */
    public function save(Product $product, ?int $expectedVersion = null): void;

    public function delete(string $id): void;

    public function getVersion(string $productId): ?int;

    public function search(?string $categoryId, ?string $search, PageRequest $pageRequest): PagedResult;

    public function hasActiveSales(string $productId): bool;

    /**
     * @return array<Product>
     */
    public function findAllActive(
        ?string $search = null,
        ?string $categoryId = null,
        int $page = 1,
        int $size = 20
    ): array;

    public function countAllActive(?string $search = null, ?string $categoryId = null): int;
}
