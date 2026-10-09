<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class PageRequest
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $size = 20
    ) {
    }

    public function offset(): int
    {
        return max(0, ($this->page - 1) * $this->size);
    }

    public function limit(): int
    {
        return $this->size;
    }
}

