<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class PlaceSaleCommand
{
    private string $userId;
    private string $username;
    /** @var PlaceSaleItemCommand[] */
    private array $items;

    /**
     * @param PlaceSaleItemCommand[] $items
     */
    public function __construct(string $userId, string $username, array $items)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->items = $items;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return PlaceSaleItemCommand[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
