<?php

namespace Tests\Unit;

use App\Enums\MediaType;
use App\Enums\TechnologyCategory;
use App\Http\Resources\Api\V1\TechnologyResource;
use App\Models\Media;
use App\Models\Technology;
use Illuminate\Http\Request;
use Tests\TestCase;

class TechnologyResourceTest extends TestCase
{
    public function test_technology_resource_serializes_public_shape(): void
    {
        $technology = $this->makeTechnology();
        $technology->setRelation('iconMedia', $this->makeMedia());

        $request = Request::create('/api/v1/technologies', 'GET');
        $data = (new TechnologyResource($technology))->resolve($request);

        $this->assertSame('11111111-1111-1111-1111-111111111111', $data['uuid']);
        $this->assertSame('laravel', $data['slug']);
        $this->assertSame('Laravel', $data['name']);
        $this->assertSame('framework', $data['category']);
        $this->assertSame('https://laravel.com', $data['websiteUrl']);
        $this->assertSame('#ff2d20', $data['color']);
        $this->assertSame('https://cdn.example.com/media/laravel.svg', $data['icon']['src']);
        $this->assertSame('Laravel icon', $data['icon']['alt']);
        $this->assertSame(1, $data['sortOrder']);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('isVisible', $data);
        $this->assertArrayNotHasKey('createdBy', $data);
        $this->assertArrayNotHasKey('updatedBy', $data);
        $this->assertArrayNotHasKey('deletedAt', $data);
    }

    public function test_technology_resource_hides_private_icon_media(): void
    {
        $technology = $this->makeTechnology();
        $technology->setRelation('iconMedia', $this->makeMedia(false));

        $request = Request::create('/api/v1/technologies', 'GET');
        $data = (new TechnologyResource($technology))->resolve($request);

        $this->assertNull($data['icon']);
    }

    private function makeTechnology(): Technology
    {
        return new Technology([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'slug' => 'laravel',
            'name' => 'Laravel',
            'category' => TechnologyCategory::Framework,
            'website_url' => 'https://laravel.com',
            'color' => '#ff2d20',
            'sort_order' => 1,
            'is_visible' => true,
        ]);
    }

    private function makeMedia(bool $isPublic = true): Media
    {
        $media = new Media([
            'uuid' => '22222222-2222-2222-2222-222222222222',
            'disk' => 'public',
            'path' => 'media/laravel.svg',
            'url' => 'https://cdn.example.com/media/laravel.svg',
            'type' => MediaType::Image,
            'alt_text' => ['en' => 'Laravel icon'],
            'caption' => [],
            'width' => 64,
            'height' => 64,
            'mime_type' => 'image/svg+xml',
            'size_bytes' => 2048,
            'metadata' => [],
            'variants' => [],
            'is_public' => $isPublic,
        ]);
        $media->id = 10;

        return $media;
    }
}
