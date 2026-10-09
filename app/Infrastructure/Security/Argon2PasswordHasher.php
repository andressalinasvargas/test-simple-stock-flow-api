<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Ports\Outbound\PasswordHasher;

final class Argon2PasswordHasher implements PasswordHasher
{
    public function hash(string $plain): string
    {
        // Use Argon2id if available, fallback to default safe algorithm
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        return password_hash($plain, $algo);
    }

    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}

