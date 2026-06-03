<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesPublicApiRequests;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HeroContentResource;
use App\Models\HeroContent;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeroContentController extends Controller
{
    use HandlesPublicApiRequests;

    public function __invoke(Request $request): JsonResponse
    {
        $localeMeta = $this->resolvePublicApiLocale($request);

        if ($localeMeta instanceof JsonResponse) {
            return $localeMeta;
        }

        $heroContent = HeroContent::query()
            ->active()
            ->with(['translations', 'heroImage', 'ogImage'])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('id')
            ->first();

        if (! $heroContent) {
            return $this->publicApiNotFound($request, url('/api/v1/hero'));
        }

        $resource = new HeroContentResource($heroContent);

        return ApiResponse::make(
            data: $resource->resolve($request),
            meta: $resource->fallbackMeta($request),
        );
    }
}
