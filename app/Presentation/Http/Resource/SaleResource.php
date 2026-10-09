<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resource;

use App\Application\DTO\SaleView;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read SaleView $resource
 */
class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SaleView $view */
        $view = $this->resource;

        $items = [];
        foreach ($view->getItems() as $item) {
            $items[] = [
                'id' => $item->getId(),
                'productId' => $item->getProductId(),
                'productName' => $item->getProductName(),
                'categoryName' => $item->getCategoryName(),
                'quantity' => $item->getQuantity(),
                'unitPrice' => round($item->getUnitPrice(), 2),
                'subtotal' => round($item->getSubtotal(), 2),
            ];
        }

        return [
            'id' => $view->getId(),
            'soldAt' => $view->getSoldAt()->format('Y-m-d\TH:i:sP'),
            'soldBy' => $view->getSoldBy(),
            'total' => round($view->getTotal(), 2),
            'currency' => $view->getCurrency(),
            'items' => $items,
        ];
    }
}
