<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Ports\Inbound\ManageProducts;
use Illuminate\Http\JsonResponse;

final class CategoryController
{
    public function __construct(
        private readonly ManageProducts $manageProducts
    ) {
    }

    public function index(): JsonResponse
    {
        $categories = $this->manageProducts->listCategories();
        return response()->json($categories, 200);
    }
}

