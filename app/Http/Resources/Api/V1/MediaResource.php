<?php

namespace App\Http\Resources\Api\V1;

use App\Support\PublicApiLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $localeMeta = PublicApiLocale::resolve($request);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'type' => $this->enumValue($this->type),
            'src' => $this->sourceUrl(),
            'alt' => $this->localizedValue($this->alt_text, $localeMeta['resolvedLocale']),
            'caption' => $this->localizedValue($this->caption, $localeMeta['resolvedLocale']),
            'width' => $this->width,
            'height' => $this->height,
            'mimeType' => $this->mime_type,
            'sizeBytes' => $this->size_bytes,
            'variants' => $this->publicVariants(),
            'metadata' => $this->publicMetadata(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => $this->fallbackMeta($request),
        ];
    }

    /**
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    public function fallbackMeta(Request $request): array
    {
        $localeMeta = PublicApiLocale::resolve($request);
        $fallbackFields = [];
        $missingFields = [];

        foreach (['alt' => $this->alt_text, 'caption' => $this->caption] as $field => $values) {
            if ($this->hasLocalizedValue($values, $localeMeta['resolvedLocale'])) {
                continue;
            }

            if ($this->hasLocalizedValue($values, PublicApiLocale::DEFAULT_LOCALE)) {
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

    /**
     * @param  array<string, mixed>|null  $values
     */
    private function localizedValue(?array $values, string $locale): ?string
    {
        if ($this->hasLocalizedValue($values, $locale)) {
            return (string) $values[$locale];
        }

        if ($this->hasLocalizedValue($values, PublicApiLocale::DEFAULT_LOCALE)) {
            return (string) $values[PublicApiLocale::DEFAULT_LOCALE];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $values
     */
    private function hasLocalizedValue(?array $values, string $locale): bool
    {
        return isset($values[$locale]) && is_scalar($values[$locale]) && trim((string) $values[$locale]) !== '';
    }

    /**
     * @return array<string, mixed>
     */
    private function publicMetadata(): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $allowedKeys = config('portfolio.media.public_metadata_keys', ['blurhash']);

        return collect($metadata)
            ->only($allowedKeys)
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function publicVariants(): array
    {
        $variants = is_array($this->variants) ? $this->variants : [];
        $allowedKeys = config('portfolio.media.public_variant_keys', [
            'src',
            'url',
            'path',
            'width',
            'height',
            'mimeType',
            'sizeBytes',
        ]);
        $publicVariants = [];

        foreach ($variants as $name => $variant) {
            if (! is_string($name) || ! is_array($variant)) {
                continue;
            }

            $safeVariant = collect($variant)
                ->only($allowedKeys)
                ->filter(fn (mixed $value): bool => is_scalar($value) && trim((string) $value) !== '')
                ->all();

            if (! $this->hasPublicVariantSource($safeVariant)) {
                continue;
            }

            $publicVariants[$name] = $safeVariant;
        }

        return $publicVariants;
    }

    /**
     * @param  array<string, mixed>  $variant
     */
    private function hasPublicVariantSource(array $variant): bool
    {
        foreach (['src', 'url', 'path'] as $sourceKey) {
            if (isset($variant[$sourceKey]) && is_scalar($variant[$sourceKey]) && trim((string) $variant[$sourceKey]) !== '') {
                return true;
            }
        }

        return false;
    }

    private function sourceUrl(): ?string
    {
        if (filled($this->url)) {
            return $this->url;
        }

        if (blank($this->disk) || blank($this->path)) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum ? $value->value : $value;
    }
}
