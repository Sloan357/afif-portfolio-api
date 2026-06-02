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
            ->assertJsonPath('meta.defaultLocale', 'en')
            ->assertJsonStructure([
                'data' => [
                    'status',
                ],
                'meta' => [
                    'apiVersion',
                    'locale',
                    'defaultLocale',
                    'generatedAt',
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
