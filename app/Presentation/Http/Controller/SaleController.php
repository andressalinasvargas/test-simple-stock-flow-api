<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\DTO\PlaceSaleCommand;
use App\Application\DTO\PlaceSaleItemCommand;
use App\Application\Ports\Inbound\GetSales;
use App\Application\Ports\Inbound\PlaceSale;
use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\ConcurrencyConflictException;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\InvalidQuantityException;
use App\Presentation\Http\Request\PlaceSaleRequest;
use App\Presentation\Http\Resource\SaleResource;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SaleController
{
    public function __construct(
        private readonly PlaceSale $placeSale,
        private readonly GetSales $getSales
    ) {
    }

    public function store(PlaceSaleRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $userId = (string) ($user?->id ?? $request->header('X-User-Id', '11111111-1111-4111-8111-111111111111'));
            $username = (string) ($user?->username ?? $request->header('X-User-Name', 'seller'));

            $lines = [];
            foreach ($request->input('lines', []) as $lineData) {
                $lines[] = new PlaceSaleItemCommand(
                    productId: (string) $lineData['productId'],
                    quantity: (int) $lineData['quantity']
                );
            }

            $command = new PlaceSaleCommand(
                userId: $userId,
                username: $username,
                lines: $lines
            );

            $view = $this->placeSale->execute($command);

            return response()->json([
                'id' => $view->id,
            ], 201, [
                'Location' => "/api/sales/{$view->id}",
            ]);
        } catch (ConcurrencyConflictException) {
            return $this->problemDetails(
                409,
                'Conflicto con otra operación simultánea',
                'Otra operación modificó los datos al mismo tiempo. Inténtalo de nuevo.'
            );
        } catch (InsufficientStockException|InvalidQuantityException|BusinessRuleViolation $e) {
            return $this->problemDetails(
                422,
                'Regla de negocio violada',
                $e->getMessage()
            );
        }
    }

    public function index(Request $request): JsonResponse
    {
        $fromStr = $request->query('from');
        $toStr = $request->query('to');

        if (!is_string($fromStr) || !is_string($toStr) || trim($fromStr) === '' || trim($toStr) === '') {
            return response()->json([
                'title' => 'Error de validación sintáctica',
                'status' => 400,
                'detail' => 'Los parámetros from y to son obligatorios.',
                'errors' => [
                    'from' => ['The from query parameter is required.'],
                    'to' => ['The to query parameter is required.'],
                ],
            ], 400);
        }

        try {
            $from = new DateTimeImmutable(trim($fromStr));
            $to = new DateTimeImmutable(trim($toStr));
        } catch (Exception) {
            return response()->json([
                'title' => 'Error de validación sintáctica',
                'status' => 400,
                'detail' => 'Formato de fecha inválido. Se requiere ISO 8601.',
                'errors' => ['date' => ['Invalid date format.']],
            ], 400);
        }

        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        try {
            $result = $this->getSales->getSales($from, $to, $page, $size);

            $items = array_map(
                fn($view) => (new SaleResource($view))->toArray($request),
                $result['items']
            );

            return response()->json([
                'items' => $items,
                'page' => $result['page'],
                'size' => $result['size'],
                'total' => $result['total'],
                'totalPages' => $result['totalPages'],
            ], 200);
        } catch (BusinessRuleViolation $e) {
            return $this->problemDetails(422, 'Regla de negocio violada', $e->getMessage());
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $view = $this->getSales->getSaleById($id);
            return response()->json((new SaleResource($view))->toArray(request()), 200);
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
