<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\DTO\CreateProductCommand;
use App\Application\DTO\UpdateProductCommand;
use App\Application\Ports\Inbound\ManageProducts;
use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\ConcurrencyConflictException;
use App\Domain\Exception\ProductNotFoundException;
use App\Presentation\Http\Request\StoreProductRequest;
use App\Presentation\Http\Request\UploadProductImageRequest;
use App\Presentation\Http\Resource\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ProductController
{
    public function __construct(
        private readonly ManageProducts $manageProducts
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $categoryId = $request->query('categoryId');
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        $result = $this->manageProducts->listProducts(
            is_string($search) ? $search : null,
            is_string($categoryId) ? $categoryId : null,
            $page,
            $size
        );

        $items = array_map(
            fn($view) => (new ProductResource($view))->toArray($request),
            $result['items']
        );

        return response()->json([
            'items' => $items,
            'page' => $result['page'],
            'size' => $result['size'],
            'total' => $result['total'],
            'totalPages' => $result['totalPages'],
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $view = $this->manageProducts->getProduct($id);
            return response()->json((new ProductResource($view))->toArray(request()), 200);
        } catch (ProductNotFoundException) {
            return response()->json(null, 404);
        } catch (BusinessRuleViolation $e) {
            if ($e->getCode() === 404) {
                return response()->json(null, 404);
            }
            return $this->problemDetails(422, 'Regla de negocio violada', $e->getMessage());
        }
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $command = new CreateProductCommand(
                name: (string) $request->input('name'),
                price: $request->input('price'),
                stock: (int) $request->input('stock'),
                categoryId: (string) $request->input('categoryId'),
                sku: $request->input('sku') !== null ? (string) $request->input('sku') : null
            );

            $view = $this->manageProducts->createProduct($command);

            return response()->json([
                'id' => $view->id,
            ], 201, [
                'Location' => "/api/products/{$view->id}",
            ]);
        } catch (BusinessRuleViolation $e) {
            return $this->problemDetails(422, 'Regla de negocio violada', $e->getMessage());
        }
    }

    public function update(StoreProductRequest $request, string $id): Response|JsonResponse
    {
        try {
            $command = new UpdateProductCommand(
                id: $id,
                name: (string) $request->input('name'),
                price: $request->input('price'),
                stock: (int) $request->input('stock'),
                categoryId: (string) $request->input('categoryId'),
                sku: $request->input('sku') !== null ? (string) $request->input('sku') : null
            );

            $this->manageProducts->updateProduct($command);

            return response()->noContent(204);
        } catch (ProductNotFoundException) {
            return response()->noContent(404);
        } catch (ConcurrencyConflictException $e) {
            return $this->problemDetails(409, 'Conflicto con otra operación simultánea', $e->getMessage());
        } catch (BusinessRuleViolation $e) {
            if ($e->getCode() === 404) {
                return response()->noContent(404);
            }
            return $this->problemDetails(422, 'Regla de negocio violada', $e->getMessage());
        }
    }

    public function destroy(string $id): Response|JsonResponse
    {
        try {
            $this->manageProducts->deleteProduct($id);
            return response()->noContent(204);
        } catch (ProductNotFoundException) {
            return response()->noContent(404);
        } catch (ConcurrencyConflictException $e) {
            return $this->problemDetails(409, 'Conflicto con otra operación simultánea', $e->getMessage());
        } catch (BusinessRuleViolation $e) {
            if ($e->getCode() === 404) {
                return response()->noContent(404);
            }
            return $this->problemDetails(422, 'Regla de negocio violada', $e->getMessage());
        }
    }

    public function uploadImage(UploadProductImageRequest $request, string $id): JsonResponse
    {
        try {
            $file = $request->file('file');
            if ($file === null) {
                return response()->json([
                    'title' => 'Error de validación sintáctica',
                    'status' => 400,
                    'detail' => 'Falta el archivo de imagen.',
                    'errors' => ['file' => ['The file field is required.']],
                ], 400);
            }

            $content = (string) file_get_contents($file->getRealPath());
            $mimeType = (string) $file->getMimeType();
            $ext = (string) $file->getClientOriginalExtension();

            $url = $this->manageProducts->attachImage($id, $content, $mimeType, $ext);

            return response()->json([
                'url' => $url,
            ], 200);
        } catch (ProductNotFoundException) {
            return response()->json(null, 404);
        } catch (BusinessRuleViolation $e) {
            if ($e->getCode() === 404) {
                return response()->json(null, 404);
            }
            return $this->problemDetails(422, 'Regla de negocio violada', $e->getMessage());
        }
    }

    private function problemDetails(int $status, string $title, string $detail): JsonResponse
    {
        return response()->json([
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
        ], $status, [
            'Content-Type' => 'application/problem+json; charset=utf-8',
        ]);
    }
}
