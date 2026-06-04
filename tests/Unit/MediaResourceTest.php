<?php

namespace Tests\Unit;

use App\Enums\MediaType;
use App\Filament\Resources\MediaResource as FilamentMediaResource;
use App\Http\Resources\Api\V1\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Tests\TestCase;

class MediaResourceTest extends TestCase
{
    public function test_media_resource_serializes_normalized_public_shape(): void
    {
        $media = new Media([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'disk' => 'public',
            'path' => 'media/project.jpg',
            'url' => 'https://cdn.example.com/media/project.jpg',
            'type' => MediaType::Image,
            'alt_text' => ['en' => 'Project screenshot', 'fr' => 'Capture du projet'],
            'caption' => ['en' => 'Homepage view', 'fr' => "Vue de la page d'accueil"],
            'width' => 1200,
            'height' => 800,
            'mime_type' => 'image/jpeg',
            'size_bytes' => 245000,
            'variants' => [
                'thumb' => [
                    'src' => 'https://cdn.example.com/media/project-thumb.jpg',
                    'width' => 320,
                    'internalPath' => '/storage/app/private/project-thumb.jpg',
                ],
                'internal' => ['width' => 120],
            ],
            'metadata' => [
                'blurhash' => 'abc123',
                'prompt' => 'internal generation prompt',
                'source_path' => '/storage/app/private/project.psd',
            ],
            'is_public' => true,
        ]);
        $media->id = 10;

        $request = Request::create('/api/v1/media/10', 'GET', ['locale' => 'fr']);
        $data = (new MediaResource($media))->resolve($request);

        $this->assertSame([
            'id' => 10,
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'type' => 'image',
            'src' => 'https://cdn.example.com/media/project.jpg',
            'alt' => 'Capture du projet',
            'caption' => "Vue de la page d'accueil",
            'width' => 1200,
            'height' => 800,
            'mimeType' => 'image/jpeg',
            'sizeBytes' => 245000,
            'variants' => [
                'thumb' => [
                    'src' => 'https://cdn.example.com/media/project-thumb.jpg',
                    'width' => 320,
                ],
            ],
            'metadata' => ['blurhash' => 'abc123'],
        ], $data);

        $this->assertArrayNotHasKey('disk', $data);
        $this->assertArrayNotHasKey('path', $data);
        $this->assertArrayNotHasKey('url', $data);
        $this->assertArrayNotHasKey('created_by', $data);
        $this->assertArrayNotHasKey('is_public', $data);
    }

    public function test_media_resource_filters_private_metadata_and_unsafe_variant_fields(): void
    {
        $media = new Media([
            'uuid' => '44444444-4444-4444-4444-444444444444',
            'disk' => 'public',
            'path' => 'media/project.jpg',
            'url' => 'https://cdn.example.com/media/project.jpg',
            'type' => MediaType::Image,
            'alt_text' => ['en' => 'Project screenshot'],
            'caption' => [],
            'variants' => [
                'thumb' => [
                    'src' => 'https://cdn.example.com/media/project-thumb.jpg',
                    'width' => 320,
                    'secret' => 'do not expose',
                ],
                'internal' => [
                    'width' => 640,
                    'secret' => 'missing public source',
                ],
            ],
            'metadata' => [
                'blurhash' => 'abc123',
                'prompt' => 'internal generation prompt',
                'source_path' => '/storage/app/private/source.psd',
            ],
        ]);
        $media->id = 13;

        $request = Request::create('/api/v1/media/13', 'GET');
        $data = (new MediaResource($media))->resolve($request);

        $this->assertSame(['blurhash' => 'abc123'], $data['metadata']);
        $this->assertSame([
            'thumb' => [
                'src' => 'https://cdn.example.com/media/project-thumb.jpg',
                'width' => 320,
            ],
        ], $data['variants']);
        $this->assertArrayNotHasKey('prompt', $data['metadata']);
        $this->assertArrayNotHasKey('source_path', $data['metadata']);
        $this->assertArrayNotHasKey('internal', $data['variants']);
        $this->assertArrayNotHasKey('secret', $data['variants']['thumb']);
    }

    public function test_public_filesystem_disk_uses_same_origin_storage_url_by_default(): void
    {
        $this->assertSame('/storage', config('filesystems.disks.public.url'));
    }

    public function test_media_resource_generates_public_storage_url_for_public_disk_media(): void
    {
        config()->set('filesystems.disks.public.url', '/storage');

        $media = new Media([
            'uuid' => '55555555-5555-5555-5555-555555555555',
            'disk' => 'public',
            'path' => 'media/project.png',
            'type' => MediaType::Image,
            'alt_text' => ['en' => 'Project screenshot'],
            'caption' => [],
            'variants' => [],
            'metadata' => [],
            'is_public' => true,
        ]);
        $media->id = 14;

        $request = Request::create('/api/v1/media/14', 'GET');
        $data = (new MediaResource($media))->resolve($request);

        $this->assertSame('/storage/media/project.png', $data['src']);
    }

    public function test_media_resource_does_not_emit_private_media_source_url(): void
    {
        $media = new Media([
            'uuid' => '66666666-6666-6666-6666-666666666666',
            'disk' => 'local',
            'path' => 'media/private.png',
            'url' => 'https://cdn.example.com/media/private.png',
            'type' => MediaType::Image,
            'alt_text' => ['en' => 'Private screenshot'],
            'caption' => [],
            'variants' => [],
            'metadata' => [],
            'is_public' => false,
        ]);
        $media->id = 15;

        $request = Request::create('/api/v1/media/15', 'GET');
        $data = (new MediaResource($media))->resolve($request);

        $this->assertNull($data['src']);
    }

    public function test_filament_media_resource_uses_public_disk_only_for_public_media(): void
    {
        config()->set('portfolio.storage.private_media_disk', 'local');

        $this->assertSame('public', FilamentMediaResource::diskForVisibility(true));
        $this->assertSame('local', FilamentMediaResource::diskForVisibility(false));
    }

    public function test_media_resource_falls_back_to_english_localized_text(): void
    {
        $media = new Media([
            'uuid' => '22222222-2222-2222-2222-222222222222',
            'disk' => 'public',
            'path' => 'media/project.jpg',
            'type' => MediaType::Image,
            'alt_text' => ['en' => 'Project screenshot'],
            'caption' => ['en' => 'Homepage view'],
            'variants' => [],
            'metadata' => [],
        ]);
        $media->id = 11;

        $request = Request::create('/api/v1/media/11', 'GET', ['locale' => 'fr']);
        $resource = new MediaResource($media);

        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Project screenshot', $data['alt']);
        $this->assertSame('Homepage view', $data['caption']);
        $this->assertSame('fr', $meta['requestedLocale']);
        $this->assertSame('fr', $meta['resolvedLocale']);
        $this->assertTrue($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame(['alt', 'caption'], $meta['fallbackFields']);
    }

    public function test_media_resource_reports_missing_localized_fields(): void
    {
        $media = new Media([
            'uuid' => '33333333-3333-3333-3333-333333333333',
            'disk' => 'public',
            'path' => 'media/project.jpg',
            'type' => MediaType::Image,
            'alt_text' => [],
            'caption' => null,
            'variants' => [],
            'metadata' => [],
        ]);
        $media->id = 12;

        $request = Request::create('/api/v1/media/12', 'GET');
        $resource = new MediaResource($media);

        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertNull($data['alt']);
        $this->assertNull($data['caption']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame(['alt', 'caption'], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
    }
}
