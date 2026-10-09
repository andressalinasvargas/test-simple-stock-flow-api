<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class RepeatedProductException extends DomainException
{
    public function __construct(string $productId)
    {
        parent::__construct(sprintf("El producto con ID '%s' está repetido en la solicitud.", $productId));
    }
}

