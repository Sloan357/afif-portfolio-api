<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\PublicApiLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $localeMeta = PublicApiLocale::resolve($request);

        app()->setLocale($localeMeta['resolvedLocale']);

        if ($localeMeta['requestedLocale'] !== null && ! PublicApiLocale::isSupported($localeMeta['requestedLocale'])) {
            return ApiResponse::validationError(
                PublicApiLocale::validationErrors($localeMeta['requestedLocale']),
                $localeMeta,
            );
        }

        return ApiResponse::make([
            'status' => 'ok',
        ], $localeMeta);
    }
}
