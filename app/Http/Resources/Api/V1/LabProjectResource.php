<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesLocalizedFields;
use App\Http\Resources\Api\V1\Concerns\SerializesMedia;
use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabProjectResource extends JsonResource
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
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->localizedValue('title', $locale),
            'summary' => $this->localizedValue('summary', $locale),
            'content' => $this->localizedValue('content', $locale),
            'problem' => $this->localizedValue('problem', $locale),
            'approach' => $this->localizedValue('approach', $locale),
            'architectureNotes' => $this->localizedValue('architecture_notes', $locale),
            'status' => $this->enumValue($this->status),
            'isFeatured' => $this->is_featured,
            'sortOrder' => $this->sort_order,
            'startedAt' => $this->started_at?->toIso8601String(),
            'publishedAt' => $this->published_at?->toIso8601String(),
            'featuredImage' => $this->serializeMedia($request, 'featuredImage'),
            'seoImage' => $this->serializeMedia($request, 'seoImage'),
            'seo' => [
                'title' => $this->localizedValue('seo_title', $locale),
                'description' => $this->localizedValue('seo_description', $locale),
                'keywords' => $this->localizedValue('seo_keywords', $locale) ?? [],
            ],
        ];
    }

    /**
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    public function fallbackMeta(Request $request): array
    {
        [$missingFields, $fallbackFields] = $this->localizedFallbackFields($request, $this->localizedFieldMap());
        [$missingFields, $fallbackFields] = $this->mergeMediaFallbackMeta($request, [
            'featuredImage' => 'featuredImage',
            'seoImage' => 'seoImage',
        ], $missingFields, $fallbackFields);

        return $this->fallbackMetaFromFields($request, $missingFields, $fallbackFields);
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
            'problem' => 'problem',
            'approach' => 'approach',
            'architectureNotes' => 'architecture_notes',
            'seo.title' => 'seo_title',
            'seo.description' => 'seo_description',
            'seo.keywords' => 'seo_keywords',
        ];
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum ? $value->value : $value;
    }
}
