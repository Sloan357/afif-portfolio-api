<?php

namespace App\Http\Resources\Api\V1;

use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperienceResource extends JsonResource
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
            'company' => $this->company,
            'companyUrl' => $this->company_url,
            'location' => $this->location,
            'role' => $this->localizedValue('role', $locale),
            'summary' => $this->localizedValue('summary', $locale),
            'responsibilities' => $this->localizedValue('responsibilities', $locale) ?? [],
            'startDate' => $this->start_date?->toDateString(),
            'endDate' => $this->end_date?->toDateString(),
            'isCurrent' => $this->is_current,
            'sortOrder' => $this->sort_order,
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
            'role' => 'role',
            'summary' => 'summary',
            'responsibilities' => 'responsibilities',
        ];
    }
}
