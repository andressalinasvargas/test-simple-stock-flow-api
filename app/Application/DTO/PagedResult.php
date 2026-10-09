<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * @template T
 */
final class PagedResult
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $size,
        public readonly int $total,
        public readonly int $totalPages
    ) {
    }
}

