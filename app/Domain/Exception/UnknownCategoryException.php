<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UnknownCategoryException extends BusinessRuleViolation
{
    public function __construct(string $categoryId)
    {
        parent::__construct(sprintf("La categoría con ID '%s' no existe.", $categoryId));
    }
}

