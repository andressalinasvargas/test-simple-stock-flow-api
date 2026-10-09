<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

/**
 * Thrown when an optimistic concurrency conflict occurs (affected rows === 0 on version check).
 * Handled as HTTP 409 Conflict.
 */
class ConcurrencyConflictException extends RuntimeException
{
}
