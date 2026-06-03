<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class PublicApiValidationTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function publicEndpointsProvider(): array
    {
        return [
            'blog posts list' => ['/api/v1/blog-posts'],
            'blog posts detail' => ['/api/v1/blog-posts/example-post'],
            'experience' => ['/api/v1/experience'],
            'health' => ['/api/v1/health'],
            'hero' => ['/api/v1/hero'],
            'home' => ['/api/v1/home'],
            'labs' => ['/api/v1/labs'],
            'projects list' => ['/api/v1/projects'],
            'projects detail' => ['/api/v1/projects/example-project'],
            'settings' => ['/api/v1/settings'],
            'technologies' => ['/api/v1/technologies'],
        ];
    }

    #[DataProvider('publicEndpointsProvider')]
    public function test_public_endpoints_reject_invalid_locale(string $endpoint): void
    {
        $response = $this->getJson($endpoint.'?locale=de');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.locale.0', 'The locale must be one of: en, fr.')
            ->assertJsonPath('meta.locale', 'en')
            ->assertJsonPath('meta.requestedLocale', 'de')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('meta.fallbackUsed', true)
            ->assertJsonStructure([
                'data',
                'errors' => ['locale'],
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

    public function test_public_endpoints_reject_array_locale(): void
    {
        $response = $this->getJson('/api/v1/health?locale[]=fr');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.locale.0', 'The locale must be one of: en, fr.')
            ->assertJsonPath('meta.requestedLocale', null)
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('meta.fallbackUsed', false);
    }

    public function test_unknown_public_api_route_returns_api_404_envelope(): void
    {
        $response = $this->getJson('/api/v1/does-not-exist');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta.apiVersion', 'v1')
            ->assertJsonPath('links.self', url('/api/v1/does-not-exist'))
            ->assertJsonStructure([
                'data',
                'meta' => ['apiVersion', 'locale', 'defaultLocale', 'generatedAt'],
                'links' => ['self'],
            ]);
    }

    public function test_method_not_allowed_returns_api_envelope(): void
    {
        $response = $this->postJson('/api/v1/health');

        $response
            ->assertStatus(405)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.method.0', 'The requested method is not allowed for this endpoint.')
            ->assertJsonPath('links.self', url('/api/v1/health'));
    }

    public function test_validation_exception_returns_api_envelope(): void
    {
        Route::get('/api/v1/test-validation-exception', function (): void {
            throw ValidationException::withMessages([
                'field' => ['The field is invalid.'],
            ]);
        });

        $response = $this->getJson('/api/v1/test-validation-exception');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.field.0', 'The field is invalid.')
            ->assertJsonStructure(['data', 'errors', 'meta', 'links']);
    }

    public function test_unexpected_public_api_error_returns_safe_envelope(): void
    {
        Route::get('/api/v1/test-unexpected-error', function (): void {
            throw new RuntimeException('Sensitive internal detail');
        });

        $response = $this->getJson('/api/v1/test-unexpected-error');

        $response
            ->assertStatus(500)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.server.0', 'An unexpected error occurred.')
            ->assertJsonMissing(['Sensitive internal detail'])
            ->assertJsonStructure(['data', 'errors', 'meta', 'links']);
    }

    public function test_invalid_project_slug_format_returns_api_404_envelope(): void
    {
        $response = $this->getJson('/api/v1/projects/invalid_slug');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('links.self', url('/api/v1/projects/invalid_slug'));
    }

    public function test_invalid_blog_post_slug_format_returns_api_404_envelope(): void
    {
        $response = $this->getJson('/api/v1/blog-posts/invalid_slug');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('links.self', url('/api/v1/blog-posts/invalid_slug'));
    }

    public function test_ai_drafts_are_not_routable_from_public_api(): void
    {
        foreach (['/api/v1/ai-drafts', '/api/v1/ai-drafts/example-draft'] as $endpoint) {
            $response = $this->getJson($endpoint);

            $response
                ->assertStatus(404)
                ->assertJsonPath('data', null)
                ->assertJsonPath('meta.apiVersion', 'v1')
                ->assertJsonPath('links.self', url($endpoint));
        }
    }

    public function test_ai_request_logs_are_not_routable_from_public_api(): void
    {
        foreach (['/api/v1/ai-request-logs', '/api/v1/ai-request-logs/example-log'] as $endpoint) {
            $response = $this->getJson($endpoint);

            $response
                ->assertStatus(404)
                ->assertJsonPath('data', null)
                ->assertJsonPath('meta.apiVersion', 'v1')
                ->assertJsonPath('links.self', url($endpoint));
        }
    }
}
