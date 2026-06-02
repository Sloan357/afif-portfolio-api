<?php

namespace Tests\Unit;

use App\Enums\MediaType;
use App\Enums\ProjectStatus;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProjectResourceTest extends TestCase
{
    public function test_project_resource_serializes_public_shape(): void
    {
        $project = $this->makeProject();
        $project->setRelation('translations', new Collection([
            new ProjectTranslation([
                'locale' => 'en',
                'title' => 'Portfolio CMS',
                'summary' => 'A Laravel Filament CMS.',
                'content' => 'Detailed project write-up.',
                'seo_title' => 'Portfolio CMS case study',
                'seo_description' => 'How the CMS was built.',
                'seo_keywords' => ['laravel', 'filament'],
            ]),
        ]));
        $project->setRelation('featuredImage', $this->makeMedia('Project screenshot', 'Featured image'));
        $project->setRelation('seoImage', $this->makeMedia('SEO preview', 'SEO image'));

        $request = Request::create('/api/v1/projects/portfolio-cms', 'GET');
        $resource = new ProjectResource($project);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('11111111-1111-1111-1111-111111111111', $data['uuid']);
        $this->assertSame('portfolio-cms', $data['slug']);
        $this->assertSame('Portfolio CMS', $data['title']);
        $this->assertSame('A Laravel Filament CMS.', $data['summary']);
        $this->assertSame('Detailed project write-up.', $data['content']);
        $this->assertSame('published', $data['status']);
        $this->assertTrue($data['isFeatured']);
        $this->assertSame(1, $data['sortOrder']);
        $this->assertNotNull($data['publishedAt']);
        $this->assertSame('Project screenshot', $data['featuredImage']['alt']);
        $this->assertSame('SEO preview', $data['seoImage']['alt']);
        $this->assertSame('Portfolio CMS case study', $data['seo']['title']);
        $this->assertSame('How the CMS was built.', $data['seo']['description']);
        $this->assertSame(['laravel', 'filament'], $data['seo']['keywords']);
        $this->assertSame(url('/api/v1/projects/portfolio-cms'), $data['links']['self']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('createdBy', $data);
        $this->assertArrayNotHasKey('updatedBy', $data);
        $this->assertArrayNotHasKey('deletedAt', $data);
    }

    public function test_project_resource_uses_english_fallback_for_missing_french_fields(): void
    {
        $project = $this->makeProject();
        $project->setRelation('translations', new Collection([
            new ProjectTranslation([
                'locale' => 'en',
                'title' => 'Portfolio CMS',
                'summary' => 'A Laravel Filament CMS.',
                'content' => 'Detailed project write-up.',
                'seo_title' => 'Portfolio CMS case study',
                'seo_description' => 'How the CMS was built.',
                'seo_keywords' => ['laravel', 'filament'],
            ]),
            new ProjectTranslation([
                'locale' => 'fr',
                'title' => 'CMS de portfolio',
            ]),
        ]));
        $project->setRelation('featuredImage', $this->makeMedia('Project screenshot', 'Featured image'));
        $project->setRelation('seoImage', null);

        $request = Request::create('/api/v1/projects/portfolio-cms', 'GET', ['locale' => 'fr']);
        $resource = new ProjectResource($project);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('CMS de portfolio', $data['title']);
        $this->assertSame('A Laravel Filament CMS.', $data['summary']);
        $this->assertSame('Detailed project write-up.', $data['content']);
        $this->assertSame('Portfolio CMS case study', $data['seo']['title']);
        $this->assertSame('Project screenshot', $data['featuredImage']['alt']);
        $this->assertTrue($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([
            'summary',
            'content',
            'seo.title',
            'seo.description',
            'seo.keywords',
            'featuredImage.alt',
            'featuredImage.caption',
        ], $meta['fallbackFields']);
    }

    public function test_project_resource_reports_missing_fields_and_hides_private_media(): void
    {
        $project = $this->makeProject();
        $project->setRelation('translations', new Collection([
            new ProjectTranslation([
                'locale' => 'en',
                'title' => 'Portfolio CMS',
            ]),
        ]));
        $project->setRelation('featuredImage', $this->makeMedia('Private screenshot', 'Private image', false));
        $project->setRelation('seoImage', null);

        $request = Request::create('/api/v1/projects/portfolio-cms', 'GET');
        $resource = new ProjectResource($project);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Portfolio CMS', $data['title']);
        $this->assertNull($data['summary']);
        $this->assertNull($data['content']);
        $this->assertNull($data['featuredImage']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([
            'summary',
            'content',
            'seo.title',
            'seo.description',
            'seo.keywords',
        ], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
    }

    private function makeProject(): Project
    {
        return new Project([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'slug' => 'portfolio-cms',
            'status' => ProjectStatus::Published,
            'is_featured' => true,
            'sort_order' => 1,
            'published_at' => now(),
        ]);
    }

    private function makeMedia(string $alt, string $caption, bool $isPublic = true): Media
    {
        $media = new Media([
            'uuid' => '22222222-2222-2222-2222-222222222222',
            'disk' => 'public',
            'path' => 'media/project.jpg',
            'url' => 'https://cdn.example.com/media/project.jpg',
            'type' => MediaType::Image,
            'alt_text' => ['en' => $alt],
            'caption' => ['en' => $caption],
            'width' => 1200,
            'height' => 800,
            'mime_type' => 'image/jpeg',
            'size_bytes' => 240000,
            'metadata' => [],
            'variants' => [],
            'is_public' => $isPublic,
        ]);
        $media->id = 10;

        return $media;
    }
}
