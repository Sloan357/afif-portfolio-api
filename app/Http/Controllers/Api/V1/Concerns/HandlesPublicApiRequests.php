<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Support\ApiResponse;
use App\Support\PublicApiLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait HandlesPublicApiRequests
{
    /**
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}|JsonResponse
     */
    protected function resolvePublicApiLocale(Request $request): array|JsonResponse
    {
        $localeMeta = PublicApiLocale::resolve($request);

        app()->setLocale($localeMeta['resolvedLocale']);

        if (! PublicApiLocale::isRequestValid($request)) {
            return ApiResponse::validationError(
                PublicApiLocale::validationErrors(),
                $localeMeta,
            );
        }

        return $localeMeta;
    }

    protected function publicApiNotFound(Request $request, ?string $self = null): JsonResponse
    {
        $localeMeta = PublicApiLocale::resolve($request);

        return ApiResponse::notFound(
            meta: $localeMeta,
            links: $self ? ['self' => $self] : [],
        );
    }
}
