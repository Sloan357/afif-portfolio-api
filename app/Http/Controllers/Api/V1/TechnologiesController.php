<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesPublicApiRequests;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TechnologyResource;
use App\Models\Technology;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechnologiesController extends Controller
{
    use HandlesPublicApiRequests;

    public function index(Request $request): JsonResponse
    {
        $localeMeta = $this->resolvePublicApiLocale($request);

        if ($localeMeta instanceof JsonResponse) {
            return $localeMeta;
        }

        $technologies = Technology::query()
            ->visible()
            ->with('iconMedia')
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ApiResponse::make(
            data: $technologies->map(fn (Technology $technology): array => (new TechnologyResource($technology))->resolve($request))->values(),
            links: ['self' => url('/api/v1/technologies')],
        );
    }
}
