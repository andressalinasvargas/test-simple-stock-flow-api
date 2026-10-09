<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class AuthResultView
{
    private string $accessToken;
    private string $expiresAt;
    private string $username;
    private string $role;

    public function __construct(string $accessToken, string $expiresAt, string $username, string $role)
    {
        $this->accessToken = $accessToken;
        $this->expiresAt = $expiresAt;
        $this->username = $username;
        $this->role = $role;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}

