<?php

namespace Tests\Unit;

use App\Enums\MediaType;
use App\Http\Resources\Api\V1\SiteSettingResource;
use App\Models\Media;
use App\Models\SiteSetting;
use App\Models\SiteSettingTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Tests\TestCase;

class SiteSettingResourceTest extends TestCase
{
    public function test_site_setting_resource_serializes_public_shape(): void
    {
        $settings = $this->makeSettings();
        $settings->setRelation('translations', new Collection([
            new SiteSettingTranslation([
                'locale' => 'en',
                'tagline' => 'Software engineer',
                'description' => 'Building useful web software.',
                'default_seo_title' => 'Afif Portfolio',
                'default_seo_description' => 'Portfolio and engineering notes.',
                'default_seo_keywords' => ['portfolio', 'software'],
            ]),
        ]));
        $settings->setRelation('defaultOgImage', $this->makeMedia('Default social preview', 'Open graph image'));
        $settings->setRelation('faviconMedia', $this->makeMedia('Site icon', 'Favicon'));

        $request = Request::create('/api/v1/settings', 'GET');
        $resource = new SiteSettingResource($settings);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Afif Portfolio', $data['siteName']);
        $this->assertSame('Software engineer', $data['tagline']);
        $this->assertSame('Building useful web software.', $data['description']);
        $this->assertSame('hello@example.com', $data['email']);
        $this->assertSame('+1 555 0100', $data['phone']);
        $this->assertSame('Montreal, Canada', $data['location']);
        $this->assertSame('example.com', $data['primaryDomain']);
        $this->assertSame('https://example.com', $data['frontendUrl']);
        $this->assertSame([['platform' => 'github', 'url' => 'https://github.com/example']], $data['socialLinks']);
        $this->assertSame([['type' => 'email', 'label' => 'Email', 'url' => 'mailto:hello@example.com']], $data['contactLinks']);
        $this->assertSame('Afif Portfolio', $data['defaultSeo']['title']);
        $this->assertSame('Portfolio and engineering notes.', $data['defaultSeo']['description']);
        $this->assertSame(['portfolio', 'software'], $data['defaultSeo']['keywords']);
        $this->assertSame('https://cdn.example.com/media/image.jpg', $data['defaultOgImage']['src']);
        $this->assertSame('Default social preview', $data['defaultOgImage']['alt']);
        $this->assertSame('Site icon', $data['favicon']['alt']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
        $this->assertArrayNotHasKey('adminUrl', $data);
        $this->assertArrayNotHasKey('metadata', $data);
        $this->assertArrayNotHasKey('uuid', $data);
        $this->assertArrayNotHasKey('key', $data);
    }

    public function test_site_setting_resource_uses_english_fallback_for_missing_french_fields(): void
    {
        $settings = $this->makeSettings();
        $settings->setRelation('translations', new Collection([
            new SiteSettingTranslation([
                'locale' => 'en',
                'tagline' => 'Software engineer',
                'description' => 'Building useful web software.',
                'default_seo_title' => 'Afif Portfolio',
                'default_seo_description' => 'Portfolio and engineering notes.',
                'default_seo_keywords' => ['portfolio', 'software'],
            ]),
            new SiteSettingTranslation([
                'locale' => 'fr',
                'tagline' => 'Ingenieur logiciel',
            ]),
        ]));
        $settings->setRelation('defaultOgImage', $this->makeMedia('Default social preview', 'Open graph image'));
        $settings->setRelation('faviconMedia', null);

        $request = Request::create('/api/v1/settings', 'GET', ['locale' => 'fr']);
        $resource = new SiteSettingResource($settings);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Ingenieur logiciel', $data['tagline']);
        $this->assertSame('Building useful web software.', $data['description']);
        $this->assertSame('Afif Portfolio', $data['defaultSeo']['title']);
        $this->assertSame('Default social preview', $data['defaultOgImage']['alt']);
        $this->assertTrue($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([
            'description',
            'defaultSeo.title',
            'defaultSeo.description',
            'defaultSeo.keywords',
            'defaultOgImage.alt',
            'defaultOgImage.caption',
        ], $meta['fallbackFields']);
    }

    public function test_site_setting_resource_does_not_serialize_private_media(): void
    {
        $settings = $this->makeSettings();
        $settings->setRelation('translations', new Collection([
            new SiteSettingTranslation([
                'locale' => 'en',
                'tagline' => 'Software engineer',
                'description' => 'Building useful web software.',
                'default_seo_title' => 'Afif Portfolio',
                'default_seo_description' => 'Portfolio and engineering notes.',
                'default_seo_keywords' => ['portfolio'],
            ]),
        ]));
        $settings->setRelation('defaultOgImage', $this->makeMedia('Private preview', 'Private image', false));
        $settings->setRelation('faviconMedia', null);

        $request = Request::create('/api/v1/settings', 'GET');
        $data = (new SiteSettingResource($settings))->resolve($request);

        $this->assertNull($data['defaultOgImage']);
    }

    private function makeSettings(): SiteSetting
    {
        return new SiteSetting([
            'site_name' => 'Afif Portfolio',
            'email' => 'hello@example.com',
            'phone' => '+1 555 0100',
            'location' => 'Montreal, Canada',
            'primary_domain' => 'example.com',
            'frontend_url' => 'https://example.com',
            'admin_url' => 'https://admin.example.com',
            'social_links' => [['platform' => 'github', 'url' => 'https://github.com/example']],
            'contact_links' => [['type' => 'email', 'label' => 'Email', 'url' => 'mailto:hello@example.com']],
            'metadata' => ['internal' => true],
        ]);
    }

    private function makeMedia(string $alt, string $caption, bool $isPublic = true): Media
    {
        $media = new Media([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'disk' => 'public',
            'path' => 'media/image.jpg',
            'url' => 'https://cdn.example.com/media/image.jpg',
            'type' => MediaType::Image,
            'alt_text' => ['en' => $alt],
            'caption' => ['en' => $caption],
            'width' => 1200,
            'height' => 630,
            'mime_type' => 'image/jpeg',
            'size_bytes' => 204800,
            'metadata' => [],
            'variants' => [],
            'is_public' => $isPublic,
        ]);
        $media->id = 10;

        return $media;
    }
}
