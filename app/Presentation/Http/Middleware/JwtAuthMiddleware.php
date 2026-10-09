<?php

declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use App\Application\Ports\Outbound\TokenGenerator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function __construct(
        private readonly TokenGenerator $tokenGenerator
    ) {
    }

    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        $authHeader = $request->header('Authorization');
        if ($authHeader === null || !str_starts_with($authHeader, 'Bearer ')) {
            return response('', 401, ['Content-Length' => '0', 'WWW-Authenticate' => 'Bearer']);
        }

        $token = substr($authHeader, 7);
        $payload = $this->tokenGenerator->validate($token);
        if ($payload === null) {
            return response('', 401, ['Content-Length' => '0', 'WWW-Authenticate' => 'Bearer']);
        }

        if ($requiredRole !== null && ($payload['role'] ?? '') !== $requiredRole) {
            return response('', 403, ['Content-Length' => '0']);
        }

        $request->attributes->set('auth_user', $payload);
        $request->setUserResolver(function () use ($payload) {
            return (object) [
                'id' => $payload['sub'] ?? '',
                'username' => $payload['username'] ?? $payload['unique_name'] ?? '',
                'role' => $payload['role'] ?? '',
            ];
        });

        return $next($request);
    }
}

