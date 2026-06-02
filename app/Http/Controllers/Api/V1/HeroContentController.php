<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HeroContentResource;
use App\Models\HeroContent;
use App\Support\ApiResponse;
use App\Support\PublicApiLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeroContentController extends Controller
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

        $heroContent = HeroContent::query()
            ->active()
            ->with(['translations', 'heroImage', 'ogImage'])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('id')
            ->first();

        if (! $heroContent) {
            return ApiResponse::make(null, $localeMeta, status: 404);
        }

        $resource = new HeroContentResource($heroContent);

        return ApiResponse::make(
            data: $resource->resolve($request),
            meta: $resource->fallbackMeta($request),
        );
    }
}
