<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Enums\TechnologyCategory;
use App\Models\Media;
use App\Models\Technology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTechnologiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_technologies_list_returns_visible_technologies(): void
    {
        Technology::query()->create([
            'slug' => 'laravel',
            'name' => 'Laravel',
            'category' => TechnologyCategory::Framework,
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/technologies');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'laravel')
            ->assertJsonPath('data.0.name', 'Laravel')
            ->assertJsonPath('data.0.category', 'framework')
            ->assertJsonMissingPath('data.0.createdBy')
            ->assertJsonMissingPath('data.0.updatedBy');
    }

    public function test_technologies_list_hides_invisible_technologies(): void
    {
        Technology::query()->create([
            'slug' => 'laravel',
            'name' => 'Laravel',
            'is_visible' => true,
        ]);
        Technology::query()->create([
            'slug' => 'internal-tool',
            'name' => 'Internal Tool',
            'is_visible' => false,
        ]);

        $response = $this->getJson('/api/v1/technologies');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'laravel')
            ->assertJsonMissing(['slug' => 'internal-tool']);
    }

    public function test_technologies_list_orders_by_sort_order_then_name(): void
    {
        Technology::query()->create([
            'slug' => 'z-null',
            'name' => 'Z Null',
            'sort_order' => null,
            'is_visible' => true,
        ]);
        Technology::query()->create([
            'slug' => 'vue',
            'name' => 'Vue',
            'sort_order' => 2,
            'is_visible' => true,
        ]);
        Technology::query()->create([
            'slug' => 'alpine',
            'name' => 'Alpine',
            'sort_order' => 2,
            'is_visible' => true,
        ]);
        Technology::query()->create([
            'slug' => 'laravel',
            'name' => 'Laravel',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/technologies');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'laravel')
            ->assertJsonPath('data.1.slug', 'alpine')
            ->assertJsonPath('data.2.slug', 'vue')
            ->assertJsonPath('data.3.slug', 'z-null');
    }

    public function test_technologies_list_serializes_icon_media(): void
    {
        $icon = Media::query()->create([
            'disk' => 'public',
            'path' => 'media/laravel.svg',
            'url' => 'https://cdn.example.com/media/laravel.svg',
            'type' => MediaType::Image,
            'alt_text' => ['en' => 'Laravel icon'],
            'caption' => [],
            'metadata' => [],
            'variants' => [],
            'is_public' => true,
        ]);

        Technology::query()->create([
            'slug' => 'laravel',
            'name' => 'Laravel',
            'icon_media_id' => $icon->id,
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/v1/technologies');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.icon.src', 'https://cdn.example.com/media/laravel.svg')
            ->assertJsonPath('data.0.icon.alt', 'Laravel icon')
            ->assertJsonMissingPath('data.0.icon.disk')
            ->assertJsonMissingPath('data.0.icon.path');
    }
}
