<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Matches both clients that send Accept: application/json
        // AND any request to /api/* that forgets the header.
        $isApiRequest = static fn (Request $r): bool =>
            $r->expectsJson() || $r->is('api/*');

        // 422 — Validation
        $exceptions->render(function (ValidationException $e, Request $request) use ($isApiRequest): ?\Illuminate\Http\JsonResponse {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            return null;
        });

        // 404 — Not Found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApiRequest): ?\Illuminate\Http\JsonResponse {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }

            return null;
        });

        // 401 — Unauthenticated
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isApiRequest): ?\Illuminate\Http\JsonResponse {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return null;
        });

        // 403 — Unauthorized (Gate::authorize() throws AuthorizationException which
        // Laravel converts to AccessDeniedHttpException; we catch both to be safe).
        $exceptions->render(function (AuthorizationException $e, Request $request) use ($isApiRequest): ?\Illuminate\Http\JsonResponse {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This action is unauthorized.',
                ], 403);
            }

            return null;
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($isApiRequest): ?\Illuminate\Http\JsonResponse {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This action is unauthorized.',
                ], 403);
            }

            return null;
        });
    })
    ->create();
