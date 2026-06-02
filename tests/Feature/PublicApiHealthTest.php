<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicApiHealthTest extends TestCase
{
    public function test_health_endpoint_returns_successful_api_envelope(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('meta.apiVersion', 'v1')
            ->assertJsonPath('meta.locale', 'en')
            ->assertJsonPath('meta.defaultLocale', 'en')
            ->assertJsonPath('meta.requestedLocale', null)
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('meta.fallbackLocale', 'en')
            ->assertJsonPath('meta.fallbackUsed', false)
            ->assertJsonPath('meta.missingFields', [])
            ->assertJsonPath('meta.fallbackFields', [])
            ->assertJsonStructure([
                'data' => [
                    'status',
                ],
                'meta' => [
                    'apiVersion',
                    'locale',
                    'defaultLocale',
                    'generatedAt',
                    'requestedLocale',
                    'resolvedLocale',
                    'fallbackLocale',
                    'fallbackUsed',
                    'missingFields',
                    'fallbackFields',
                ],
                'links',
            ]);
    }

    public function test_health_endpoint_accepts_valid_french_locale(): void
    {
        $response = $this->getJson('/api/v1/health?locale=fr');

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('meta.locale', 'fr')
            ->assertJsonPath('meta.requestedLocale', 'fr')
            ->assertJsonPath('meta.resolvedLocale', 'fr')
            ->assertJsonPath('meta.defaultLocale', 'en')
            ->assertJsonPath('meta.fallbackLocale', 'en')
            ->assertJsonPath('meta.fallbackUsed', false)
            ->assertJsonPath('meta.missingFields', [])
            ->assertJsonPath('meta.fallbackFields', []);
    }

    public function test_health_endpoint_rejects_invalid_locale(): void
    {
        $response = $this->getJson('/api/v1/health?locale=de');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.locale.0', 'The locale must be one of: en, fr.')
            ->assertJsonPath('meta.locale', 'en')
            ->assertJsonPath('meta.requestedLocale', 'de')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('meta.defaultLocale', 'en')
            ->assertJsonPath('meta.fallbackLocale', 'en')
            ->assertJsonPath('meta.fallbackUsed', true)
            ->assertJsonStructure([
                'data',
                'errors' => [
                    'locale',
                ],
                'meta' => [
                    'apiVersion',
                    'locale',
                    'defaultLocale',
                    'generatedAt',
                    'requestedLocale',
                    'resolvedLocale',
                    'fallbackLocale',
                    'fallbackUsed',
                    'missingFields',
                    'fallbackFields',
                ],
                'links',
            ]);
    }

    public function test_health_endpoint_does_not_expose_admin_or_draft_data(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonMissingPath('data.drafts')
            ->assertJsonMissingPath('data.admin')
            ->assertJsonMissingPath('data.aiDrafts');
    }
}
