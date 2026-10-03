<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\CreateProductCommand;
use App\Application\DTO\ProductView;
use App\Application\DTO\UpdateProductCommand;
use App\Application\Ports\Inbound\ManageProducts;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\FileStorage;
use App\Application\Ports\Outbound\ProductRepository;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Model\Product;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;

final class ProductCatalogService implements ManageProducts
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB

    public function __construct(
        private readonly UnitOfWork $unitOfWork,
        private readonly ProductRepository $productRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly ?FileStorage $fileStorage = null
    ) {
    }

    public function createProduct(CreateProductCommand $command): ProductView
    {
        return $this->unitOfWork->execute(function () use ($command): ProductView {
            $categoryId = new CategoryId($command->categoryId);
            $category = $this->categoryRepository->findById($categoryId);
            if ($category === null) {
                throw new BusinessRuleViolation("La categoría {$command->categoryId} no existe.");
            }

            $price = Money::of($command->price);
            $productId = ProductId::generate();

            $product = new Product(
                id: $productId,
                name: $command->name,
                price: $price,
                stock: $command->stock,
                categoryId: $categoryId,
                sku: $command->sku,
                isActive: true,
                imageKey: null
            );

            $this->productRepository->save($product);

            return new ProductView(
                id: $product->getId()->getValue(),
                name: $product->getName(),
                price: $product->getPrice()->toFloat(),
                currency: $product->getPrice()->getCurrency(),
                stock: $product->getStock(),
                categoryId: $category->getId()->getValue(),
                categoryName: $category->getName(),
                imageUrl: $product->getImageKey() !== null ? "/media/{$product->getImageKey()}" : null,
                sku: $product->getSku(),
                isActive: $product->isActive()
            );
        });
    }

    public function updateProduct(UpdateProductCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $productId = new ProductId($command->id);
            $product = $this->productRepository->findActiveById($productId);
            if ($product === null) {
                throw new ProductNotFoundException($command->id);
            }

            $categoryId = new CategoryId($command->categoryId);
            $category = $this->categoryRepository->findById($categoryId);
            if ($category === null) {
                throw new BusinessRuleViolation("La categoría {$command->categoryId} no existe.");
            }

            $product->rename($command->name);
            $product->changePrice(Money::of($command->price));
            $product->changeCategory($categoryId);
            $product->setSku($command->sku);

            $currentStock = $product->getStock();
            $diff = $command->stock - $currentStock;
            if ($diff > 0) {
                $product->restock(new Quantity($diff));
            } elseif ($diff < 0) {
                $product->withdraw(new Quantity(abs($diff)));
            }

            $this->productRepository->save($product);
        });
    }

    public function getProduct(string $id): ProductView
    {
        $productId = new ProductId($id);
        $product = $this->productRepository->findActiveById($productId);
        if ($product === null) {
            throw new ProductNotFoundException($id);
        }

        $category = $this->categoryRepository->findById($product->getCategoryId());
        $categoryName = $category !== null ? $category->getName() : 'General';

        return new ProductView(
            id: $product->getId()->getValue(),
            name: $product->getName(),
            price: $product->getPrice()->toFloat(),
            currency: $product->getPrice()->getCurrency(),
            stock: $product->getStock(),
            categoryId: $product->getCategoryId()->getValue(),
            categoryName: $categoryName,
            imageUrl: $product->getImageKey() !== null ? "/media/{$product->getImageKey()}" : null,
            sku: $product->getSku(),
            isActive: $product->isActive()
        );
    }

    public function deleteProduct(string $id): void
    {
        $this->unitOfWork->execute(function () use ($id): void {
            $productId = new ProductId($id);
            $product = $this->productRepository->findActiveById($productId);
            if ($product === null) {
                throw new ProductNotFoundException($id);
            }

            // Baja lógica conforme a ADR-003
            $this->productRepository->delete($productId);
        });
    }

    public function listProducts(
        ?string $search = null,
        ?string $categoryId = null,
        int $page = 1,
        int $size = 20
    ): array {
        $clampedPage = max(1, $page);
        $clampedSize = $size < 1 ? 20 : min(100, $size);

        $products = $this->productRepository->findAllActive($search, $categoryId, $clampedPage, $clampedSize);
        $total = $this->productRepository->countAllActive($search, $categoryId);
        $totalPages = $clampedSize === 0 ? 0 : (int) ceil($total / $clampedSize);

        $items = [];
        foreach ($products as $product) {
            $category = $this->categoryRepository->findById($product->getCategoryId());
            $categoryName = $category !== null ? $category->getName() : 'General';

            $items[] = new ProductView(
                id: $product->getId()->getValue(),
                name: $product->getName(),
                price: $product->getPrice()->toFloat(),
                currency: $product->getPrice()->getCurrency(),
                stock: $product->getStock(),
                categoryId: $product->getCategoryId()->getValue(),
                categoryName: $categoryName,
                imageUrl: $product->getImageKey() !== null ? "/media/{$product->getImageKey()}" : null,
                sku: $product->getSku(),
                isActive: $product->isActive()
            );
        }

        return [
            'items' => $items,
            'page' => $clampedPage,
            'size' => $clampedSize,
            'total' => $total,
            'totalPages' => $totalPages,
        ];
    }

    public function attachImage(
        string $productId,
        string $fileContent,
        string $mimeType,
        string $extension
    ): string {
        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new BusinessRuleViolation("Tipo de archivo no permitido: {$mimeType}.");
        }

        if (strlen($fileContent) > self::MAX_FILE_SIZE_BYTES) {
            throw new BusinessRuleViolation('La imagen supera el máximo de 5 MB.');
        }

        return $this->unitOfWork->execute(function () use ($productId, $fileContent, $mimeType): string {
            $pId = new ProductId($productId);
            $product = $this->productRepository->findActiveById($pId);
            if ($product === null) {
                throw new ProductNotFoundException($productId);
            }

            $ext = self::ALLOWED_MIME_TYPES[$mimeType];
            $key = bin2hex(random_bytes(16)) . '.' . $ext;

            if ($this->fileStorage !== null) {
                $this->fileStorage->save($key, $fileContent, $mimeType);
            }

            $product->attachImage($key);
            $this->productRepository->save($product);

            return "/media/{$key}";
        });
    }

    public function listCategories(): array
    {
        $categories = $this->categoryRepository->findAll();
        $result = [];
        foreach ($categories as $cat) {
            $result[] = [
                'id' => $cat->getId()->getValue(),
                'name' => $cat->getName(),
            ];
        }
        return $result;
    }
}

