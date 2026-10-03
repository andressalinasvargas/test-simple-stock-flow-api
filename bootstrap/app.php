<?php

declare(strict_types=1);

use App\Presentation\Http\Middleware\JwtAuthMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => JwtAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->noContent(404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            $headers = $e->getHeaders();
            return response()->noContent(405, $headers);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            return response()->noContent(403);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->noContent(401);
        });
    })->create();

