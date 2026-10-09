<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class UpdateProductCommand
{
    private string $id;
    private string $name;
    private float $price;
    private int $stock;
    private string $categoryId;

    public function __construct(string $id, string $name, float $price, int $stock, string $categoryId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
        $this->categoryId = $categoryId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }
}
