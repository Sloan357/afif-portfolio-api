<?php

namespace Tests\Feature;

use App\Enums\HeroContentStatus;
use App\Models\HeroContent;
use App\Models\HeroContentTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiHeroContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_endpoint_returns_active_published_hero(): void
    {
        $hero = $this->createHeroContent(HeroContentStatus::Published, true, now());
        HeroContentTranslation::query()->create([
            'hero_content_id' => $hero->id,
            'locale' => 'en',
            'badge' => 'Available',
            'headline' => 'Building practical software systems',
            'description' => 'Portfolio CMS and engineering notes.',
            'primary_cta_label' => 'View projects',
            'capabilities' => ['Laravel APIs'],
            'architecture_items' => [['title' => 'CMS']],
        ]);

        $response = $this->getJson('/api/v1/hero');

        $response
            ->assertOk()
            ->assertJsonPath('data.badge', 'Available')
            ->assertJsonPath('data.headline', 'Building practical software systems')
            ->assertJsonPath('data.primaryCta.label', 'View projects')
            ->assertJsonPath('data.primaryCta.url', '/projects')
            ->assertJsonPath('data.capabilities', ['Laravel APIs'])
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonMissingPath('data.createdBy');
    }

    public function test_hero_endpoint_hides_inactive_hero(): void
    {
        $hero = $this->createHeroContent(HeroContentStatus::Published, false, now());
        HeroContentTranslation::query()->create([
            'hero_content_id' => $hero->id,
            'locale' => 'en',
            'headline' => 'Hidden hero',
        ]);

        $response = $this->getJson('/api/v1/hero');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('links.self', url('/api/v1/hero'))
            ->assertJsonMissing(['headline' => 'Hidden hero']);
    }

    public function test_hero_endpoint_hides_draft_and_archived_heroes(): void
    {
        foreach ([HeroContentStatus::Draft, HeroContentStatus::Archived] as $status) {
            $hero = $this->createHeroContent($status, true, now(), $status->value);
            HeroContentTranslation::query()->create([
                'hero_content_id' => $hero->id,
                'locale' => 'en',
                'headline' => $status->value.' hero',
            ]);
        }

        $response = $this->getJson('/api/v1/hero');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('links.self', url('/api/v1/hero'));
    }

    public function test_hero_endpoint_hides_future_published_hero(): void
    {
        $hero = $this->createHeroContent(HeroContentStatus::Published, true, now()->addDay());
        HeroContentTranslation::query()->create([
            'hero_content_id' => $hero->id,
            'locale' => 'en',
            'headline' => 'Future hero',
        ]);

        $response = $this->getJson('/api/v1/hero');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonMissing(['headline' => 'Future hero']);
    }

    public function test_hero_endpoint_rejects_invalid_locale_before_querying_content(): void
    {
        $response = $this->getJson('/api/v1/hero?locale=de');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.locale.0', 'The locale must be one of: en, fr.')
            ->assertJsonPath('meta.locale', 'en')
            ->assertJsonPath('meta.requestedLocale', 'de')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('meta.defaultLocale', 'en')
            ->assertJsonPath('meta.fallbackLocale', 'en')
            ->assertJsonPath('meta.fallbackUsed', true);
    }

    private function createHeroContent(HeroContentStatus $status, bool $isActive, mixed $publishedAt = null, string $key = 'homepage'): HeroContent
    {
        return HeroContent::query()->create([
            'key' => $key,
            'status' => $status,
            'is_active' => $isActive,
            'primary_cta_url' => '/projects',
            'published_at' => $publishedAt,
        ]);
    }
}
