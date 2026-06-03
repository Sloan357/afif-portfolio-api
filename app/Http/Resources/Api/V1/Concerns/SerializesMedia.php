<?php

namespace App\Http\Resources\Api\V1\Concerns;

use App\Http\Resources\Api\V1\MediaResource;
use Illuminate\Http\Request;

trait SerializesMedia
{
    protected function serializeMedia(Request $request, string $relation): ?array
    {
        $media = $this->mediaRelation($relation);

        if (! $media || ! $media->is_public) {
            return null;
        }

        return (new MediaResource($media))->resolve($request);
    }

    /**
     * @param  array<string, string>  $relationMap
     * @param  array<int, string>  $missingFields
     * @param  array<int, string>  $fallbackFields
     * @return array{array<int, string>, array<int, string>}
     */
    protected function mergeMediaFallbackMeta(Request $request, array $relationMap, array $missingFields, array $fallbackFields): array
    {
        foreach ($relationMap as $prefix => $relation) {
            $media = $this->mediaRelation($relation);

            if (! $media || ! $media->is_public) {
                continue;
            }

            $mediaMeta = (new MediaResource($media))->fallbackMeta($request);
            $missingFields = array_merge($missingFields, $this->prefixFallbackFields($prefix, $mediaMeta['missingFields']));
            $fallbackFields = array_merge($fallbackFields, $this->prefixFallbackFields($prefix, $mediaMeta['fallbackFields']));
        }

        return [$missingFields, $fallbackFields];
    }

    protected function mediaRelation(string $relation): mixed
    {
        if (! $this->resource->relationLoaded($relation)) {
            return null;
        }

        return $this->{$relation};
    }
}
