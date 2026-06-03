<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesLocalizedFields;
use App\Http\Resources\Api\V1\Concerns\SerializesMedia;
use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroContentResource extends JsonResource
{
    use ResolvesLocalizedFields;
    use SerializesMedia;

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
        [$missingFields, $fallbackFields] = $this->localizedFallbackFields($request, $this->localizedFieldMap());
        [$missingFields, $fallbackFields] = $this->mergeMediaFallbackMeta($request, [
            'heroImage' => 'heroImage',
            'ogImage' => 'ogImage',
        ], $missingFields, $fallbackFields);

        return $this->fallbackMetaFromFields($request, $missingFields, $fallbackFields);
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
}
