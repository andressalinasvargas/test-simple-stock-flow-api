<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\ValueObject\CategoryId;

final class Category
{
    private CategoryId $id;
    private string $name;

    public function __construct(string|CategoryId $id, string $name)
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new BusinessRuleViolation('El nombre de la categoría no puede estar vacío.');
        }

        $this->id = $id instanceof CategoryId ? $id : new CategoryId($id);
        $this->name = $trimmed;
    }

    public function getId(): CategoryId
    {
        return $this->id;
    }

    public function getIdString(): string
    {
        return $this->id->getValue();
    }

    public function getName(): string
    {
        return $this->name;
    }
}

