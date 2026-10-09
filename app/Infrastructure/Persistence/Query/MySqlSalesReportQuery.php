<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Query;

use App\Application\DTO\SalesReportRowView;
use App\Application\DTO\SalesReportView;
use App\Application\Ports\Outbound\SalesReportQuery;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class MySqlSalesReportQuery implements SalesReportQuery
{
    public function getReport(DateTimeImmutable $from, DateTimeImmutable $to): SalesReportView
    {
        $fromStr = $from->format('Y-m-d H:i:s.u');
        $toStr = $to->format('Y-m-d H:i:s.u');

        // Total sales count in date range
        $salesCount = (int) DB::table('sale')
            ->whereBetween('sold_at', [$fromStr, $toStr])
            ->count();

        // Aggregated rows grouped by product_id, product_name, category_name
        $rawRows = DB::table('sale_item')
            ->join('sale', 'sale.id', '=', 'sale_item.sale_id')
            ->whereBetween('sale.sold_at', [$fromStr, $toStr])
            ->select([
                'sale_item.product_id',
                'sale_item.product_name',
                'sale_item.category_name',
                DB::raw('CAST(SUM(sale_item.quantity) AS UNSIGNED) as units_sold'),
                DB::raw('CAST(SUM(sale_item.quantity * sale_item.unit_price) AS DECIMAL(14,2)) as revenue'),
            ])
            ->groupBy('sale_item.product_id', 'sale_item.product_name', 'sale_item.category_name')
            ->orderByDesc('revenue')
            ->orderBy('sale_item.product_name')
            ->get();

        $grandTotal = 0.0;
        $rows = [];

        foreach ($rawRows as $row) {
            $revenue = (float) $row->revenue;
            $grandTotal += $revenue;
            $rows[] = new SalesReportRowView(
                productId: (string) $row->product_id,
                productName: (string) $row->product_name,
                categoryName: (string) $row->category_name,
                unitsSold: (int) $row->units_sold,
                revenue: $revenue
            );
        }

        return new SalesReportView(
            from: $from->format('c'),
            to: $to->format('c'),
            salesCount: $salesCount,
            grandTotal: round($grandTotal, 2),
            rows: $rows,
            currency: 'COP'
        );
    }
}
