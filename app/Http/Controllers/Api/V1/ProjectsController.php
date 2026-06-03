<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesPublicApiPagination;
use App\Http\Controllers\Api\V1\Concerns\HandlesPublicApiRequests;
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
    use HandlesPublicApiPagination;
    use HandlesPublicApiRequests;

    public function index(Request $request): JsonResponse
    {
        $localeMeta = $this->resolvePublicApiLocale($request);

        if ($localeMeta instanceof JsonResponse) {
            return $localeMeta;
        }

        $pagination = $this->resolvePublicApiPagination($request, $localeMeta);

        if ($pagination instanceof JsonResponse) {
            return $pagination;
        }

        $projects = Project::query()
            ->published()
            ->with(['translations', 'featuredImage', 'seoImage'])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->latest('id')
            ->paginate(
                perPage: $pagination['perPage'],
                page: $pagination['page'],
            )
            ->withQueryString();

        return ApiResponse::make(
            data: $projects->getCollection()->map(fn (Project $project): array => (new ProjectResource($project))->resolve($request))->values(),
            meta: array_merge(
                $this->collectionFallbackMeta($request, $projects->getCollection()),
                ['pagination' => $this->paginationMeta($projects)],
            ),
            links: $this->paginationLinks($projects),
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $localeMeta = $this->resolvePublicApiLocale($request);

        if ($localeMeta instanceof JsonResponse) {
            return $localeMeta;
        }

        $project = Project::query()
            ->published()
            ->where('slug', $slug)
            ->with(['translations', 'featuredImage', 'seoImage'])
            ->first();

        if (! $project) {
            return $this->publicApiNotFound($request, url('/api/v1/projects/'.$slug));
        }

        $resource = new ProjectResource($project);

        return ApiResponse::make(
            data: $resource->resolve($request),
            meta: $resource->fallbackMeta($request),
            links: ['self' => url('/api/v1/projects/'.$project->slug)],
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
