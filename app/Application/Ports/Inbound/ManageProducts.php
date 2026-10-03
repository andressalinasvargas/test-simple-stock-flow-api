<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTO\CreateProductCommand;
use App\Application\DTO\ProductView;
use App\Application\DTO\UpdateProductCommand;

interface ManageProducts
{
    public function createProduct(CreateProductCommand $command): ProductView;

    public function updateProduct(UpdateProductCommand $command): void;

    public function getProduct(string $id): ProductView;

    public function deleteProduct(string $id): void;

    /**
     * @return array{
     *     items: array<ProductView>,
     *     page: int,
     *     size: int,
     *     total: int,
     *     totalPages: int
     * }
     */
    public function listProducts(
        ?string $search = null,
        ?string $categoryId = null,
        int $page = 1,
        int $size = 20
    ): array;

    public function attachImage(string $productId, string $fileContent, string $mimeType, string $extension): string;

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function listCategories(): array;
}

