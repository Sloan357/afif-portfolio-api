<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ExperienceResource;
use App\Models\Experience;
use App\Support\ApiResponse;
use App\Support\PublicApiLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ExperiencesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $localeMeta = PublicApiLocale::resolve($request);

        app()->setLocale($localeMeta['resolvedLocale']);

        if ($localeMeta['requestedLocale'] !== null && ! PublicApiLocale::isSupported($localeMeta['requestedLocale'])) {
            return ApiResponse::validationError(
                PublicApiLocale::validationErrors($localeMeta['requestedLocale']),
                $localeMeta,
            );
        }

        $experiences = Experience::query()
            ->visible()
            ->with('translations')
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderByDesc('start_date')
            ->latest('id')
            ->get();

        return ApiResponse::make(
            data: $experiences->map(fn (Experience $experience): array => (new ExperienceResource($experience))->resolve($request))->values(),
            meta: $this->collectionFallbackMeta($request, $experiences),
            links: ['self' => url('/api/v1/experience')],
        );
    }

    /**
     * @param  Collection<int, Experience>  $experiences
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    private function collectionFallbackMeta(Request $request, Collection $experiences): array
    {
        $localeMeta = PublicApiLocale::resolve($request);
        $missingFields = [];
        $fallbackFields = [];

        foreach ($experiences->values() as $index => $experience) {
            $experienceMeta = (new ExperienceResource($experience))->fallbackMeta($request);
            $missingFields = array_merge($missingFields, $this->prefixFields("experience.$index", $experienceMeta['missingFields']));
            $fallbackFields = array_merge($fallbackFields, $this->prefixFields("experience.$index", $experienceMeta['fallbackFields']));
        }

        return PublicApiLocale::fallbackMeta(
            requestedLocale: $localeMeta['requestedLocale'],
            resolvedLocale: $localeMeta['resolvedLocale'],
            fallbackUsed: $localeMeta['fallbackUsed'] || $fallbackFields !== [],
            missingFields: $missingFields,
            fallbackFields: $fallbackFields,
        );
    }

    /**
     * @param  array<int, string>  $fields
     * @return array<int, string>
     */
    private function prefixFields(string $prefix, array $fields): array
    {
        return array_map(fn (string $field): string => $prefix.'.'.$field, $fields);
    }
}
