<?php

namespace App\Http\Resources\Api\V1;

use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $localeMeta = PublicApiLocale::resolve($request);
        $locale = $localeMeta['resolvedLocale'];

        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->localizedValue('title', $locale),
            'summary' => $this->localizedValue('summary', $locale),
            'content' => $this->localizedValue('content', $locale),
            'status' => $this->enumValue($this->status),
            'isFeatured' => $this->is_featured,
            'sortOrder' => $this->sort_order,
            'publishedAt' => $this->published_at?->toIso8601String(),
            'featuredImage' => $this->serializeMedia($request, 'featuredImage'),
            'seoImage' => $this->serializeMedia($request, 'seoImage'),
            'seo' => [
                'title' => $this->localizedValue('seo_title', $locale),
                'description' => $this->localizedValue('seo_description', $locale),
                'keywords' => $this->localizedValue('seo_keywords', $locale) ?? [],
            ],
            'links' => [
                'self' => url('/api/v1/projects/'.$this->slug),
            ],
        ];
    }

    /**
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    public function fallbackMeta(Request $request): array
    {
        $localeMeta = PublicApiLocale::resolve($request);
        $missingFields = [];
        $fallbackFields = [];

        foreach ($this->localizedFieldMap() as $field => $attribute) {
            if ($this->hasLocalizedValue($attribute, $localeMeta['resolvedLocale'])) {
                continue;
            }

            if ($this->hasLocalizedValue($attribute, PublicApiLocale::DEFAULT_LOCALE)) {
                $fallbackFields[] = $field;
            } else {
                $missingFields[] = $field;
            }
        }

        foreach (['featuredImage' => 'featuredImage', 'seoImage' => 'seoImage'] as $prefix => $relation) {
            $media = $this->mediaRelation($relation);

            if (! $media || ! $media->is_public) {
                continue;
            }

            $mediaMeta = (new MediaResource($media))->fallbackMeta($request);
            $missingFields = array_merge($missingFields, $this->prefixFields($prefix, $mediaMeta['missingFields']));
            $fallbackFields = array_merge($fallbackFields, $this->prefixFields($prefix, $mediaMeta['fallbackFields']));
        }

        return PublicApiLocale::fallbackMeta(
            requestedLocale: $localeMeta['requestedLocale'],
            resolvedLocale: $localeMeta['resolvedLocale'],
            fallbackUsed: $localeMeta['fallbackUsed'] || $fallbackFields !== [],
            missingFields: $missingFields,
            fallbackFields: $fallbackFields,
        );
    }

    private function localizedValue(string $attribute, string $locale): mixed
    {
        $translation = $this->translation($locale);

        if ($translation && $this->hasValue($translation->{$attribute})) {
            return $translation->{$attribute};
        }

        $fallbackTranslation = $this->translation(PublicApiLocale::DEFAULT_LOCALE);

        if ($fallbackTranslation && $this->hasValue($fallbackTranslation->{$attribute})) {
            return $fallbackTranslation->{$attribute};
        }

        return null;
    }

    private function hasLocalizedValue(string $attribute, string $locale): bool
    {
        $translation = $this->translation($locale);

        return $translation !== null && $this->hasValue($translation->{$attribute});
    }

    private function hasValue(mixed $value): bool
    {
        if (is_array($value)) {
            return $value !== [];
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return filled($value);
    }

    /**
     * @return array<string, string>
     */
    private function localizedFieldMap(): array
    {
        return [
            'title' => 'title',
            'summary' => 'summary',
            'content' => 'content',
            'seo.title' => 'seo_title',
            'seo.description' => 'seo_description',
            'seo.keywords' => 'seo_keywords',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function prefixFields(string $prefix, array $fields): array
    {
        return array_map(fn (string $field): string => $prefix.'.'.$field, $fields);
    }

    /**
     * @param  'featuredImage'|'seoImage'  $relation
     */
    private function serializeMedia(Request $request, string $relation): ?array
    {
        $media = $this->mediaRelation($relation);

        if (! $media || ! $media->is_public) {
            return null;
        }

        return (new MediaResource($media))->resolve($request);
    }

    /**
     * @param  'featuredImage'|'seoImage'  $relation
     */
    private function mediaRelation(string $relation): mixed
    {
        if (! $this->resource->relationLoaded($relation)) {
            return null;
        }

        return $this->{$relation};
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum ? $value->value : $value;
    }
}
