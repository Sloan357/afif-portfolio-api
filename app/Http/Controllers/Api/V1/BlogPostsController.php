<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesPublicApiRequests;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BlogPostResource;
use App\Models\BlogPost;
use App\Support\ApiResponse;
use App\Support\PublicApiLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BlogPostsController extends Controller
{
    use HandlesPublicApiRequests;

    public function index(Request $request): JsonResponse
    {
        $localeMeta = $this->resolvePublicApiLocale($request);

        if ($localeMeta instanceof JsonResponse) {
            return $localeMeta;
        }

        $blogPosts = BlogPost::query()
            ->published()
            ->with(['translations', 'featuredImage', 'seoImage'])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->latest('id')
            ->get();

        return ApiResponse::make(
            data: $blogPosts->map(fn (BlogPost $blogPost): array => (new BlogPostResource($blogPost))->resolve($request))->values(),
            meta: $this->collectionFallbackMeta($request, $blogPosts),
            links: ['self' => url('/api/v1/blog-posts')],
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $localeMeta = $this->resolvePublicApiLocale($request);

        if ($localeMeta instanceof JsonResponse) {
            return $localeMeta;
        }

        $blogPost = BlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->with(['translations', 'featuredImage', 'seoImage'])
            ->first();

        if (! $blogPost) {
            return $this->publicApiNotFound($request, url('/api/v1/blog-posts/'.$slug));
        }

        $resource = new BlogPostResource($blogPost);

        return ApiResponse::make(
            data: $resource->resolve($request),
            meta: $resource->fallbackMeta($request),
            links: ['self' => url('/api/v1/blog-posts/'.$blogPost->slug)],
        );
    }

    /**
     * @param  Collection<int, BlogPost>  $blogPosts
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    private function collectionFallbackMeta(Request $request, Collection $blogPosts): array
    {
        $localeMeta = PublicApiLocale::resolve($request);
        $missingFields = [];
        $fallbackFields = [];

        foreach ($blogPosts->values() as $index => $blogPost) {
            $blogPostMeta = (new BlogPostResource($blogPost))->fallbackMeta($request);
            $missingFields = array_merge($missingFields, $this->prefixFields("blogPosts.$index", $blogPostMeta['missingFields']));
            $fallbackFields = array_merge($fallbackFields, $this->prefixFields("blogPosts.$index", $blogPostMeta['fallbackFields']));
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
