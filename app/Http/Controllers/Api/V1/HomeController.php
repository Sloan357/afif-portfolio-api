<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ExperienceResource;
use App\Http\Resources\Api\V1\HeroContentResource;
use App\Http\Resources\Api\V1\LabProjectResource;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Http\Resources\Api\V1\SiteSettingResource;
use App\Http\Resources\Api\V1\TechnologyResource;
use App\Models\Experience;
use App\Models\HeroContent;
use App\Models\LabProject;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Technology;
use App\Support\ApiResponse;
use App\Support\PublicApiLocale;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
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

        $settings = SiteSetting::query()
            ->active()
            ->with(['translations', 'defaultOgImage', 'faviconMedia'])
            ->latest('id')
            ->first();

        $hero = HeroContent::query()
            ->active()
            ->with(['translations', 'heroImage', 'ogImage'])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('id')
            ->first();

        $featuredProjects = Project::query()
            ->published()
            ->where('is_featured', true)
            ->with(['translations', 'featuredImage', 'seoImage'])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->latest('id')
            ->get();

        $labProjects = LabProject::query()
            ->public()
            ->with(['translations', 'featuredImage', 'seoImage'])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->latest('id')
            ->get();

        $experiences = Experience::query()
            ->visible()
            ->with('translations')
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderByDesc('start_date')
            ->latest('id')
            ->get();

        $technologies = Technology::query()
            ->visible()
            ->with('iconMedia')
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ApiResponse::make(
            data: [
                'settings' => $settings ? (new SiteSettingResource($settings))->resolve($request) : null,
                'hero' => $hero ? (new HeroContentResource($hero))->resolve($request) : null,
                'featuredProjects' => $featuredProjects->map(fn (Project $project): array => (new ProjectResource($project))->resolve($request))->values(),
                'labProjects' => $labProjects->map(fn (LabProject $labProject): array => (new LabProjectResource($labProject))->resolve($request))->values(),
                'experience' => $experiences->map(fn (Experience $experience): array => (new ExperienceResource($experience))->resolve($request))->values(),
                'technologies' => $technologies->map(fn (Technology $technology): array => (new TechnologyResource($technology))->resolve($request))->values(),
            ],
            meta: $this->fallbackMeta($request, $settings, $hero, $featuredProjects, $labProjects, $experiences),
            links: ['self' => url('/api/v1/home')],
        );
    }

    /**
     * @param  EloquentCollection<int, Project>  $featuredProjects
     * @param  EloquentCollection<int, LabProject>  $labProjects
     * @param  EloquentCollection<int, Experience>  $experiences
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    private function fallbackMeta(
        Request $request,
        ?SiteSetting $settings,
        ?HeroContent $hero,
        EloquentCollection $featuredProjects,
        EloquentCollection $labProjects,
        EloquentCollection $experiences,
    ): array {
        $localeMeta = PublicApiLocale::resolve($request);
        $missingFields = [];
        $fallbackFields = [];

        if ($settings) {
            [$missingFields, $fallbackFields] = $this->mergeResourceMeta(
                $request,
                new SiteSettingResource($settings),
                'settings',
                $missingFields,
                $fallbackFields,
            );
        }

        if ($hero) {
            [$missingFields, $fallbackFields] = $this->mergeResourceMeta(
                $request,
                new HeroContentResource($hero),
                'hero',
                $missingFields,
                $fallbackFields,
            );
        }

        [$missingFields, $fallbackFields] = $this->mergeCollectionMeta($request, $featuredProjects, ProjectResource::class, 'featuredProjects', $missingFields, $fallbackFields);
        [$missingFields, $fallbackFields] = $this->mergeCollectionMeta($request, $labProjects, LabProjectResource::class, 'labProjects', $missingFields, $fallbackFields);
        [$missingFields, $fallbackFields] = $this->mergeCollectionMeta($request, $experiences, ExperienceResource::class, 'experience', $missingFields, $fallbackFields);

        return PublicApiLocale::fallbackMeta(
            requestedLocale: $localeMeta['requestedLocale'],
            resolvedLocale: $localeMeta['resolvedLocale'],
            fallbackUsed: $localeMeta['fallbackUsed'] || $fallbackFields !== [],
            missingFields: $missingFields,
            fallbackFields: $fallbackFields,
        );
    }

    /**
     * @param  array<int, string>  $missingFields
     * @param  array<int, string>  $fallbackFields
     * @return array{array<int, string>, array<int, string>}
     */
    private function mergeResourceMeta(Request $request, mixed $resource, string $prefix, array $missingFields, array $fallbackFields): array
    {
        $resourceMeta = $resource->fallbackMeta($request);

        return [
            array_merge($missingFields, $this->prefixFields($prefix, $resourceMeta['missingFields'])),
            array_merge($fallbackFields, $this->prefixFields($prefix, $resourceMeta['fallbackFields'])),
        ];
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  EloquentCollection<int, TModel>  $models
     * @param  class-string  $resourceClass
     * @param  array<int, string>  $missingFields
     * @param  array<int, string>  $fallbackFields
     * @return array{array<int, string>, array<int, string>}
     */
    private function mergeCollectionMeta(Request $request, EloquentCollection $models, string $resourceClass, string $prefix, array $missingFields, array $fallbackFields): array
    {
        foreach ($models->values() as $index => $model) {
            /** @var mixed $resource */
            $resource = new $resourceClass($model);
            [$missingFields, $fallbackFields] = $this->mergeResourceMeta($request, $resource, $prefix.'.'.$index, $missingFields, $fallbackFields);
        }

        return [$missingFields, $fallbackFields];
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
