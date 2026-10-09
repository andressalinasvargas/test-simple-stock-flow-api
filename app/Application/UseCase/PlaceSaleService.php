<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\PlaceSaleCommand;
use App\Application\DTO\SaleItemView;
use App\Application\DTO\SaleView;
use App\Application\Ports\Inbound\PlaceSale;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\ProductRepository;
use App\Application\Ports\Outbound\SaleRepository;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Domain\Exception\ConcurrencyConflictException;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Model\Sale;
use App\Domain\Model\SaleItem;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Username;
use Ramsey\Uuid\Uuid;

final class PlaceSaleService implements PlaceSale
{
    private ProductRepository $productRepository;
    private CategoryRepository $categoryRepository;
    private SaleRepository $saleRepository;
    private UnitOfWork $unitOfWork;
    private Clock $clock;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        SaleRepository $saleRepository,
        UnitOfWork $unitOfWork,
        Clock $clock
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->saleRepository = $saleRepository;
        $this->unitOfWork = $unitOfWork;
        $this->clock = $clock;
    }

    public function execute(PlaceSaleCommand $command): SaleView
    {
        $maxRetries = 3;
        $attempt = 0;

        while (true) {
            $attempt++;
            try {
                return $this->unitOfWork->execute(function () use ($command): SaleView {
                    $saleId = Uuid::uuid4()->toString();
                    $soldAt = $this->clock->now();
                    $username = new Username($command->getUsername());

                    $saleItems = [];
                    $viewItems = [];

                    foreach ($command->getItems() as $itemCmd) {
                        $product = $this->productRepository->findById($itemCmd->getProductId());
                        if ($product === null) {
                            throw new ProductNotFoundException("El producto {$itemCmd->getProductId()} no existe.");
                        }

                        $expectedVersion = $this->productRepository->getVersion($product->getId());

                        // Withdraw checks stock and inactive status
                        $product->withdraw($itemCmd->getQuantity());

                        $category = $this->categoryRepository->findById($product->getCategoryId());
                        $categoryName = $category !== null ? $category->getName() : 'General';

                        $itemId = Uuid::uuid4()->toString();
                        $quantityVO = new Quantity($itemCmd->getQuantity());

                        $saleItem = new SaleItem(
                            $itemId,
                            $saleId,
                            $product->getId(),
                            $product->getName(),
                            $categoryName,
                            $quantityVO,
                            $product->getPrice()
                        );

                        $saleItems[] = $saleItem;
                        $viewItems[] = new SaleItemView(
                            $saleItem->getId(),
                            $saleItem->getProductId(),
                            $saleItem->getProductName(),
                            $saleItem->getCategoryName(),
                            $saleItem->getQuantity()->getValue(),
                            $saleItem->getUnitPrice()->toFloat(),
                            $saleItem->calculateSubtotal()->toFloat()
                        );

                        // Persist stock deduction checking optimistic lock version
                        $this->productRepository->save($product, $expectedVersion);
                    }

                    $sale = new Sale(
                        $saleId,
                        $soldAt,
                        $username,
                        $command->getUserId(),
                        $saleItems
                    );

                    $this->saleRepository->save($sale);

                    return new SaleView(
                        $sale->getId(),
                        $sale->getSoldAt(),
                        $sale->getSoldByUsername()->getValue(),
                        $sale->getTotal()->toFloat(),
                        $viewItems,
                        'COP'
                    );
                });
            } catch (ConcurrencyConflictException $e) {
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                // Random backoff before retry (ADR-002)
                usleep(random_int(30000, 70000) * $attempt);
            }
        }
    }
}
