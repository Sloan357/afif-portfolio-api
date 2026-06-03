<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesLocalizedFields;
use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperienceResource extends JsonResource
{
    use ResolvesLocalizedFields;

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
        [$missingFields, $fallbackFields] = $this->localizedFallbackFields($request, $this->localizedFieldMap());

        return $this->fallbackMetaFromFields($request, $missingFields, $fallbackFields);
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
