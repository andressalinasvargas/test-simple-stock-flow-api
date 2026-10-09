<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Model\Sale;
use App\Application\DTO\PageRequest;
use App\Application\DTO\PagedResult;
use DateTimeImmutable;

interface SaleRepository
{
    public function save(Sale $sale): void;

    public function findById(string $id): ?Sale;

    public function listPaginated(DateTimeImmutable $from, DateTimeImmutable $to, PageRequest $pageRequest): PagedResult;
}
