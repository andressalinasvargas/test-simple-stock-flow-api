<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Ports\Inbound\GetSalesReport;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController
{
    public function __construct(
        private readonly GetSalesReport $getSalesReport
    ) {
    }

    public function sales(Request $request): JsonResponse
    {
        $fromRaw = $request->query('from');
        $toRaw = $request->query('to');

        if (!$fromRaw || !$toRaw) {
            $errors = [];
            if (!$fromRaw) {
                $errors['from'] = ['The from field is required.'];
            }
            if (!$toRaw) {
                $errors['to'] = ['The to field is required.'];
            }
            return response()->json([
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'Los parámetros from y to son obligatorios.',
                'errors' => $errors,
            ], 400);
        }

        try {
            $from = new DateTimeImmutable((string) $fromRaw);
            $to = new DateTimeImmutable((string) $toRaw);
        } catch (Exception) {
            return response()->json([
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'Los parámetros from y to deben ser fechas ISO 8601 válidas.',
                'errors' => ['from' => ['Formato de fecha inválido.']],
            ], 400);
        }

        $report = $this->getSalesReport->getReport($from, $to);

        $rows = [];
        foreach ($report->getRows() as $row) {
            $rows[] = [
                'productId' => $row->getProductId(),
                'productName' => $row->getProductName(),
                'categoryName' => $row->getCategoryName(),
                'unitsSold' => $row->getUnitsSold(),
                'revenue' => round($row->getRevenue(), 2),
            ];
        }

        return response()->json([
            'from' => $report->getFrom(),
            'to' => $report->getTo(),
            'salesCount' => $report->getSalesCount(),
            'grandTotal' => round($report->getGrandTotal(), 2),
            'currency' => $report->getCurrency(),
            'rows' => $rows,
        ], 200);
    }
}

