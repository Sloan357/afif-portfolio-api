<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesLocalizedFields;
use App\Http\Resources\Api\V1\Concerns\SerializesMedia;
use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteSettingResource extends JsonResource
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
            'siteName' => $this->site_name,
            'tagline' => $this->localizedValue('tagline', $locale),
            'description' => $this->localizedValue('description', $locale),
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            'primaryDomain' => $this->primary_domain,
            'frontendUrl' => $this->frontend_url,
            'socialLinks' => $this->social_links ?? [],
            'contactLinks' => $this->contact_links ?? [],
            'defaultSeo' => [
                'title' => $this->localizedValue('default_seo_title', $locale),
                'description' => $this->localizedValue('default_seo_description', $locale),
                'keywords' => $this->localizedValue('default_seo_keywords', $locale) ?? [],
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
        [$missingFields, $fallbackFields] = $this->localizedFallbackFields($request, $this->localizedFieldMap());
        [$missingFields, $fallbackFields] = $this->mergeMediaFallbackMeta($request, [
            'defaultOgImage' => 'defaultOgImage',
            'favicon' => 'faviconMedia',
        ], $missingFields, $fallbackFields);

        return $this->fallbackMetaFromFields($request, $missingFields, $fallbackFields);
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
}
