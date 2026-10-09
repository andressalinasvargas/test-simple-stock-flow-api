<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTO\SalesReportView;
use DateTimeImmutable;

interface GetSalesReport
{
    public function getReport(DateTimeImmutable $from, DateTimeImmutable $to): SalesReportView;
}

