<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesPublicApiRequests;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SiteSettingResource;
use App\Models\SiteSetting;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    use HandlesPublicApiRequests;

    public function __invoke(Request $request): JsonResponse
    {
        $localeMeta = $this->resolvePublicApiLocale($request);

        if ($localeMeta instanceof JsonResponse) {
            return $localeMeta;
        }

        $siteSettings = SiteSetting::query()
            ->active()
            ->with(['translations', 'defaultOgImage', 'faviconMedia'])
            ->latest('id')
            ->first();

        if (! $siteSettings) {
            return $this->publicApiNotFound($request, url('/api/v1/settings'));
        }

        $resource = new SiteSettingResource($siteSettings);

        return ApiResponse::make(
            data: $resource->resolve($request),
            meta: $resource->fallbackMeta($request),
        );
    }
}
