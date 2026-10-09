<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

/**
 * Thrown when a product does not exist.
 * Handled as HTTP 404 Not Found (empty body, Content-Length: 0).
 */
class ProductNotFoundException extends DomainException
{
}
