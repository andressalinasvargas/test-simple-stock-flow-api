<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Model\User;
use App\Application\DTO\AuthResultView;

interface TokenGenerator
{
    public function generate(User $user): AuthResultView;

    /**
     * @return array{sub: string, unique_name: string, role: string}|null
     */
    public function validate(string $token): ?array;
}

