<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\SalesReportView;
use App\Application\Ports\Inbound\GetSalesReport;
use App\Application\Ports\Outbound\SalesReportQuery;
use App\Domain\Exception\BusinessRuleViolation;
use DateTimeImmutable;

final class SalesReportService implements GetSalesReport
{
    private SalesReportQuery $salesReportQuery;

    public function __construct(SalesReportQuery $salesReportQuery)
    {
        $this->salesReportQuery = $salesReportQuery;
    }

    public function getReport(DateTimeImmutable $from, DateTimeImmutable $to): SalesReportView
    {
        if ($to < $from) {
            throw new BusinessRuleViolation('La fecha final no puede ser anterior a la inicial.');
        }

        return $this->salesReportQuery->getReport($from, $to);
    }
}

