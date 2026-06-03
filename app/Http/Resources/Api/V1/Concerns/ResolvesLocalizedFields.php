<?php

namespace App\Http\Resources\Api\V1\Concerns;

use App\Support\PublicApiLocale;
use Illuminate\Http\Request;

trait ResolvesLocalizedFields
{
    protected function localizedValue(string $attribute, string $locale): mixed
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

    protected function hasLocalizedValue(string $attribute, string $locale): bool
    {
        $translation = $this->translation($locale);

        return $translation !== null && $this->hasValue($translation->{$attribute});
    }

    protected function hasValue(mixed $value): bool
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
     * @param  array<string, string>  $fieldMap
     * @return array{array<int, string>, array<int, string>}
     */
    protected function localizedFallbackFields(Request $request, array $fieldMap): array
    {
        $localeMeta = PublicApiLocale::resolve($request);
        $missingFields = [];
        $fallbackFields = [];

        foreach ($fieldMap as $field => $attribute) {
            if ($this->hasLocalizedValue($attribute, $localeMeta['resolvedLocale'])) {
                continue;
            }

            if ($this->hasLocalizedValue($attribute, PublicApiLocale::DEFAULT_LOCALE)) {
                $fallbackFields[] = $field;
            } else {
                $missingFields[] = $field;
            }
        }

        return [$missingFields, $fallbackFields];
    }

    /**
     * @param  array<int, string>  $missingFields
     * @param  array<int, string>  $fallbackFields
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    protected function fallbackMetaFromFields(Request $request, array $missingFields, array $fallbackFields): array
    {
        $localeMeta = PublicApiLocale::resolve($request);

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
    protected function prefixFallbackFields(string $prefix, array $fields): array
    {
        return array_map(fn (string $field): string => $prefix.'.'.$field, $fields);
    }
}
