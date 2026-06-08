<?php

use App\Support\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        if (env('APP_ENV') === 'production') {
            $trustedProxies = trim((string) env('TRUSTED_PROXIES', '*')) ?: '*';

            $middleware->trustProxies(
                at: $trustedProxies,
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO
                    | Request::HEADER_X_FORWARDED_PREFIX,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            return ApiResponse::validationError($exception->errors());
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            return ApiResponse::notFound(links: ['self' => $request->url()]);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            return ApiResponse::error([
                'method' => ['The requested method is not allowed for this endpoint.'],
            ], links: ['self' => $request->url()], status: 405);
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            return ApiResponse::error([
                'server' => ['An unexpected error occurred.'],
            ], links: ['self' => $request->url()], status: 500);
        });
    })->create();
