<?php

namespace Tests\Unit;

use App\Enums\HeroContentStatus;
use App\Enums\MediaType;
use App\Http\Resources\Api\V1\HeroContentResource;
use App\Models\HeroContent;
use App\Models\HeroContentTranslation;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Tests\TestCase;

class HeroContentResourceTest extends TestCase
{
    public function test_hero_content_resource_serializes_public_shape(): void
    {
        $heroContent = $this->makeHeroContent();
        $heroContent->setRelation('translations', new Collection([
            new HeroContentTranslation([
                'locale' => 'en',
                'badge' => 'Available for selected work',
                'headline' => 'Building practical software systems',
                'description' => 'Portfolio CMS and engineering notes.',
                'primary_cta_label' => 'View projects',
                'secondary_cta_label' => 'Read notes',
                'capabilities' => ['Laravel APIs', 'Next.js frontends'],
                'architecture_items' => [['title' => 'CMS', 'body' => 'Filament managed content']],
            ]),
        ]));
        $heroContent->setRelation('heroImage', $this->makeMedia('Hero preview', 'Homepage hero image'));
        $heroContent->setRelation('ogImage', $this->makeMedia('Social preview', 'Open graph image'));

        $request = Request::create('/api/v1/hero', 'GET');
        $resource = new HeroContentResource($heroContent);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Available for selected work', $data['badge']);
        $this->assertSame('Building practical software systems', $data['headline']);
        $this->assertSame('Portfolio CMS and engineering notes.', $data['description']);
        $this->assertSame(['label' => 'View projects', 'url' => '/projects'], $data['primaryCta']);
        $this->assertSame(['label' => 'Read notes', 'url' => '/blog'], $data['secondaryCta']);
        $this->assertSame(['Laravel APIs', 'Next.js frontends'], $data['capabilities']);
        $this->assertSame([['title' => 'CMS', 'body' => 'Filament managed content']], $data['architectureItems']);
        $this->assertSame('https://cdn.example.com/media/hero.jpg', $data['heroImage']['src']);
        $this->assertSame('Hero preview', $data['heroImage']['alt']);
        $this->assertSame('Social preview', $data['ogImage']['alt']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('uuid', $data);
        $this->assertArrayNotHasKey('status', $data);
        $this->assertArrayNotHasKey('isActive', $data);
        $this->assertArrayNotHasKey('publishedAt', $data);
    }

    public function test_hero_content_resource_uses_english_fallback_for_missing_french_fields(): void
    {
        $heroContent = $this->makeHeroContent();
        $heroContent->setRelation('translations', new Collection([
            new HeroContentTranslation([
                'locale' => 'en',
                'badge' => 'Available for selected work',
                'headline' => 'Building practical software systems',
                'description' => 'Portfolio CMS and engineering notes.',
                'primary_cta_label' => 'View projects',
                'secondary_cta_label' => 'Read notes',
                'capabilities' => ['Laravel APIs', 'Next.js frontends'],
                'architecture_items' => [['title' => 'CMS']],
            ]),
            new HeroContentTranslation([
                'locale' => 'fr',
                'headline' => 'Construire des systemes logiciels pratiques',
                'primary_cta_label' => 'Voir les projets',
            ]),
        ]));
        $heroContent->setRelation('heroImage', $this->makeMedia('Hero preview', 'Homepage hero image'));
        $heroContent->setRelation('ogImage', null);

        $request = Request::create('/api/v1/hero', 'GET', ['locale' => 'fr']);
        $resource = new HeroContentResource($heroContent);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Construire des systemes logiciels pratiques', $data['headline']);
        $this->assertSame('Available for selected work', $data['badge']);
        $this->assertSame('Portfolio CMS and engineering notes.', $data['description']);
        $this->assertSame(['label' => 'Voir les projets', 'url' => '/projects'], $data['primaryCta']);
        $this->assertSame(['label' => 'Read notes', 'url' => '/blog'], $data['secondaryCta']);
        $this->assertSame(['Laravel APIs', 'Next.js frontends'], $data['capabilities']);
        $this->assertSame('Hero preview', $data['heroImage']['alt']);
        $this->assertTrue($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([
            'badge',
            'description',
            'secondaryCta.label',
            'capabilities',
            'architectureItems',
            'heroImage.alt',
            'heroImage.caption',
        ], $meta['fallbackFields']);
    }

    public function test_hero_content_resource_reports_missing_localized_fields_and_hides_private_media(): void
    {
        $heroContent = $this->makeHeroContent();
        $heroContent->setRelation('translations', new Collection([
            new HeroContentTranslation([
                'locale' => 'en',
                'headline' => 'Building practical software systems',
                'capabilities' => [],
                'architecture_items' => [],
            ]),
        ]));
        $heroContent->setRelation('heroImage', $this->makeMedia('Private hero', 'Private image', false));
        $heroContent->setRelation('ogImage', null);

        $request = Request::create('/api/v1/hero', 'GET');
        $resource = new HeroContentResource($heroContent);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Building practical software systems', $data['headline']);
        $this->assertNull($data['badge']);
        $this->assertNull($data['description']);
        $this->assertNull($data['heroImage']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([
            'badge',
            'description',
            'primaryCta.label',
            'secondaryCta.label',
            'capabilities',
            'architectureItems',
        ], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
    }

    private function makeHeroContent(): HeroContent
    {
        return new HeroContent([
            'status' => HeroContentStatus::Published,
            'is_active' => true,
            'primary_cta_url' => '/projects',
            'secondary_cta_url' => '/blog',
            'sort_order' => 1,
            'published_at' => now(),
        ]);
    }

    private function makeMedia(string $alt, string $caption, bool $isPublic = true): Media
    {
        $media = new Media([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'disk' => 'public',
            'path' => 'media/hero.jpg',
            'url' => 'https://cdn.example.com/media/hero.jpg',
            'type' => MediaType::Image,
            'alt_text' => ['en' => $alt],
            'caption' => ['en' => $caption],
            'width' => 1600,
            'height' => 900,
            'mime_type' => 'image/jpeg',
            'size_bytes' => 250000,
            'metadata' => [],
            'variants' => [],
            'is_public' => $isPublic,
        ]);
        $media->id = 10;

        return $media;
    }
}
