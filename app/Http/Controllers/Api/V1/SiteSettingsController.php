<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SiteSettingResource;
use App\Models\SiteSetting;
use App\Support\ApiResponse;
use App\Support\PublicApiLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
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

        $siteSettings = SiteSetting::query()
            ->active()
            ->with(['translations', 'defaultOgImage', 'faviconMedia'])
            ->latest('id')
            ->first();

        if (! $siteSettings) {
            return ApiResponse::make(null, $localeMeta, status: 404);
        }

        $resource = new SiteSettingResource($siteSettings);

        return ApiResponse::make(
            data: $resource->resolve($request),
            meta: $resource->fallbackMeta($request),
        );
    }
}
