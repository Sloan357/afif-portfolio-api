<?php

namespace App\Http\Resources\Api\V1;

use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroContentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $localeMeta = PublicApiLocale::resolve($request);
        $locale = $localeMeta['resolvedLocale'];

        return [
            'badge' => $this->localizedValue('badge', $locale),
            'headline' => $this->localizedValue('headline', $locale),
            'description' => $this->localizedValue('description', $locale),
            'primaryCta' => $this->cta('primary_cta_label', $this->primary_cta_url, $locale),
            'secondaryCta' => $this->cta('secondary_cta_label', $this->secondary_cta_url, $locale),
            'capabilities' => $this->localizedValue('capabilities', $locale) ?? [],
            'architectureItems' => $this->localizedValue('architecture_items', $locale) ?? [],
            'heroImage' => $this->serializeMedia($request, 'heroImage'),
            'ogImage' => $this->serializeMedia($request, 'ogImage'),
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

        foreach (['heroImage' => 'heroImage', 'ogImage' => 'ogImage'] as $prefix => $relation) {
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

    /**
     * @return array{label: mixed, url: ?string}|null
     */
    private function cta(string $labelAttribute, ?string $url, string $locale): ?array
    {
        $label = $this->localizedValue($labelAttribute, $locale);

        if (! $this->hasValue($label) && ! $this->hasValue($url)) {
            return null;
        }

        return [
            'label' => $label,
            'url' => $url,
        ];
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
            'badge' => 'badge',
            'headline' => 'headline',
            'description' => 'description',
            'primaryCta.label' => 'primary_cta_label',
            'secondaryCta.label' => 'secondary_cta_label',
            'capabilities' => 'capabilities',
            'architectureItems' => 'architecture_items',
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
     * @param  'heroImage'|'ogImage'  $relation
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
     * @param  'heroImage'|'ogImage'  $relation
     */
    private function mediaRelation(string $relation): mixed
    {
        if (! $this->resource->relationLoaded($relation)) {
            return null;
        }

        return $this->{$relation};
    }
}
