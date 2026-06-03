<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\SiteSettingTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_endpoint_returns_active_settings(): void
    {
        $settings = SiteSetting::query()->create([
            'site_name' => 'Afif Portfolio',
            'is_active' => true,
            'email' => 'hello@example.com',
            'social_links' => [['platform' => 'github', 'url' => 'https://github.com/example']],
            'contact_links' => [['type' => 'email', 'url' => 'mailto:hello@example.com']],
        ]);
        SiteSettingTranslation::query()->create([
            'site_setting_id' => $settings->id,
            'locale' => 'en',
            'tagline' => 'Software engineer',
            'description' => 'Building useful software.',
            'default_seo_title' => 'Afif Portfolio',
            'default_seo_description' => 'Portfolio and notes.',
            'default_seo_keywords' => ['portfolio'],
        ]);

        $response = $this->getJson('/api/v1/settings');

        $response
            ->assertOk()
            ->assertJsonPath('data.siteName', 'Afif Portfolio')
            ->assertJsonPath('data.tagline', 'Software engineer')
            ->assertJsonPath('data.email', 'hello@example.com')
            ->assertJsonPath('data.defaultSeo.title', 'Afif Portfolio')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonMissingPath('data.adminUrl')
            ->assertJsonMissingPath('data.createdBy');
    }

    public function test_settings_endpoint_hides_inactive_settings(): void
    {
        $settings = SiteSetting::query()->create([
            'site_name' => 'Inactive Portfolio',
            'is_active' => false,
        ]);
        SiteSettingTranslation::query()->create([
            'site_setting_id' => $settings->id,
            'locale' => 'en',
            'tagline' => 'Hidden',
        ]);

        $response = $this->getJson('/api/v1/settings');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('links.self', url('/api/v1/settings'))
            ->assertJsonMissing(['siteName' => 'Inactive Portfolio']);
    }

    public function test_settings_endpoint_returns_not_found_envelope_when_no_active_settings_exist(): void
    {
        $response = $this->getJson('/api/v1/settings');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta.locale', 'en')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('links.self', url('/api/v1/settings'));
    }

    public function test_settings_endpoint_rejects_invalid_locale_before_querying_settings(): void
    {
        $response = $this->getJson('/api/v1/settings?locale=de');

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
}
