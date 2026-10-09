<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Application\DTO\SalesReportView;
use DateTimeImmutable;

interface SalesReportQuery
{
    public function getReport(DateTimeImmutable $from, DateTimeImmutable $to): SalesReportView;
}

