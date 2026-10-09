<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\DTO\LoginCommand;
use App\Application\DTO\RegisterUserCommand;
use App\Application\Ports\Inbound\Authenticate;
use App\Presentation\Http\Request\LoginRequest;
use App\Presentation\Http\Request\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class AuthController
{
    public function __construct(
        private readonly Authenticate $authenticate
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $command = new LoginCommand(
            (string) $request->input('username'),
            (string) $request->input('password')
        );

        $result = $this->authenticate->login($command);

        return response()->json([
            'accessToken' => $result->getAccessToken(),
            'expiresAt' => $result->getExpiresAt(),
            'username' => $result->getUsername(),
            'role' => $result->getRole(),
        ], 200);
    }

    public function register(RegisterRequest $request): Response
    {
        $command = new RegisterUserCommand(
            (string) $request->input('username'),
            (string) $request->input('password')
        );

        $this->authenticate->registerSeller($command);

        return response('', 201, ['Content-Length' => '0']);
    }
}

