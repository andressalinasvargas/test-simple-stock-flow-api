<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTO\AuthResultView;
use App\Application\DTO\LoginCommand;
use App\Application\DTO\RegisterUserCommand;

interface Authenticate
{
    public function login(LoginCommand $command): AuthResultView;

    public function registerSeller(RegisterUserCommand $command): AuthResultView;
}

