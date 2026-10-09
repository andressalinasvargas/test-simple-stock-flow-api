<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

interface UnitOfWork
{
    /**
     * Executes the given callable within a database transaction.
     *
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function execute(callable $operation): mixed;
}
