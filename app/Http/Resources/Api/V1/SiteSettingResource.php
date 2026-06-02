<?php

namespace App\Http\Resources\Api\V1;

use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $localeMeta = PublicApiLocale::resolve($request);

        return [
            'siteName' => $this->site_name,
            'tagline' => $this->localizedValue('tagline', $localeMeta['resolvedLocale']),
            'description' => $this->localizedValue('description', $localeMeta['resolvedLocale']),
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            'primaryDomain' => $this->primary_domain,
            'frontendUrl' => $this->frontend_url,
            'socialLinks' => $this->social_links ?? [],
            'contactLinks' => $this->contact_links ?? [],
            'defaultSeo' => [
                'title' => $this->localizedValue('default_seo_title', $localeMeta['resolvedLocale']),
                'description' => $this->localizedValue('default_seo_description', $localeMeta['resolvedLocale']),
                'keywords' => $this->localizedValue('default_seo_keywords', $localeMeta['resolvedLocale']) ?? [],
            ],
            'defaultOgImage' => $this->serializeMedia($request, 'defaultOgImage'),
            'favicon' => $this->serializeMedia($request, 'faviconMedia'),
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

        foreach (['defaultOgImage' => 'defaultOgImage', 'favicon' => 'faviconMedia'] as $prefix => $relation) {
            $media = $this->whenLoaded($relation);

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
            'tagline' => 'tagline',
            'description' => 'description',
            'defaultSeo.title' => 'default_seo_title',
            'defaultSeo.description' => 'default_seo_description',
            'defaultSeo.keywords' => 'default_seo_keywords',
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
     * @param  'defaultOgImage'|'faviconMedia'  $relation
     */
    private function serializeMedia(Request $request, string $relation): ?array
    {
        $media = $this->whenLoaded($relation);

        if (! $media || ! $media->is_public) {
            return null;
        }

        return (new MediaResource($media))->resolve($request);
    }
}
