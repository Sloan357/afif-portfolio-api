<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnologyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'name' => $this->name,
            'category' => $this->enumValue($this->category),
            'websiteUrl' => $this->website_url,
            'color' => $this->color,
            'icon' => $this->serializeIcon($request),
            'sortOrder' => $this->sort_order,
        ];
    }

    private function serializeIcon(Request $request): ?array
    {
        if (! $this->resource->relationLoaded('iconMedia') || ! $this->iconMedia || ! $this->iconMedia->is_public) {
            return null;
        }

        return (new MediaResource($this->iconMedia))->resolve($request);
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum ? $value->value : $value;
    }
}
