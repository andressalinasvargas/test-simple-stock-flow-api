<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class EmptySaleException extends DomainException
{
    public function __construct()
    {
        parent::__construct('La venta debe contener al menos un producto.');
    }
}

