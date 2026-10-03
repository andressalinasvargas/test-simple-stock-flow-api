<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resource;

use App\Application\DTO\ProductView;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ProductView $resource
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ProductView $view */
        $view = $this->resource;

        return [
            'id' => $view->id,
            'name' => $view->name,
            'price' => round($view->price, 2),
            'currency' => $view->currency ?: 'COP',
            'stock' => $view->stock,
            'categoryId' => $view->categoryId,
            'categoryName' => $view->categoryName,
            'imageUrl' => $view->imageUrl,
        ];
    }
}

