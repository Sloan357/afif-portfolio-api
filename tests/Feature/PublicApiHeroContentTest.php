<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicApiHeroContentTest extends TestCase
{
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
}
