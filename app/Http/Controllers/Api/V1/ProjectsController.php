<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Support\ApiResponse;
use App\Support\PublicApiLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProjectsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $localeMeta = $this->resolveLocale($request);

        if ($this->hasInvalidLocale($localeMeta)) {
            return $this->invalidLocaleResponse($localeMeta);
        }

        $projects = Project::query()
            ->published()
            ->with(['translations', 'featuredImage', 'seoImage'])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->latest('id')
            ->get();

        return ApiResponse::make(
            data: $projects->map(fn (Project $project): array => (new ProjectResource($project))->resolve($request))->values(),
            meta: $this->collectionFallbackMeta($request, $projects),
            links: ['self' => url('/api/v1/projects')],
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $localeMeta = $this->resolveLocale($request);

        if ($this->hasInvalidLocale($localeMeta)) {
            return $this->invalidLocaleResponse($localeMeta);
        }

        $project = Project::query()
            ->published()
            ->where('slug', $slug)
            ->with(['translations', 'featuredImage', 'seoImage'])
            ->first();

        if (! $project) {
            return ApiResponse::make(null, $localeMeta, links: ['self' => url('/api/v1/projects/'.$slug)], status: 404);
        }

        $resource = new ProjectResource($project);

        return ApiResponse::make(
            data: $resource->resolve($request),
            meta: $resource->fallbackMeta($request),
            links: ['self' => url('/api/v1/projects/'.$project->slug)],
        );
    }

    /**
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    private function resolveLocale(Request $request): array
    {
        $localeMeta = PublicApiLocale::resolve($request);

        app()->setLocale($localeMeta['resolvedLocale']);

        return $localeMeta;
    }

    /**
     * @param  array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}  $localeMeta
     */
    private function hasInvalidLocale(array $localeMeta): bool
    {
        return $localeMeta['requestedLocale'] !== null && ! PublicApiLocale::isSupported($localeMeta['requestedLocale']);
    }

    /**
     * @param  array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}  $localeMeta
     */
    private function invalidLocaleResponse(array $localeMeta): JsonResponse
    {
        return ApiResponse::validationError(
            PublicApiLocale::validationErrors($localeMeta['requestedLocale']),
            $localeMeta,
        );
    }

    /**
     * @param  Collection<int, Project>  $projects
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    private function collectionFallbackMeta(Request $request, Collection $projects): array
    {
        $localeMeta = PublicApiLocale::resolve($request);
        $missingFields = [];
        $fallbackFields = [];

        foreach ($projects->values() as $index => $project) {
            $projectMeta = (new ProjectResource($project))->fallbackMeta($request);
            $missingFields = array_merge($missingFields, $this->prefixFields("projects.$index", $projectMeta['missingFields']));
            $fallbackFields = array_merge($fallbackFields, $this->prefixFields("projects.$index", $projectMeta['fallbackFields']));
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
