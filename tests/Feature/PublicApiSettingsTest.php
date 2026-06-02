<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicApiSettingsTest extends TestCase
{
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
