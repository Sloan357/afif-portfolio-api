<?php

namespace Tests\Unit;

use App\Enums\LabProjectStatus;
use App\Enums\MediaType;
use App\Http\Resources\Api\V1\LabProjectResource;
use App\Models\LabProject;
use App\Models\LabProjectTranslation;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Tests\TestCase;

class LabProjectResourceTest extends TestCase
{
    public function test_lab_project_resource_serializes_public_shape(): void
    {
        $labProject = $this->makeLabProject();
        $labProject->setRelation('translations', new Collection([
            new LabProjectTranslation([
                'locale' => 'en',
                'title' => 'AI Sketchpad',
                'summary' => 'A lab for AI-assisted ideation.',
                'content' => 'Detailed lab notes.',
                'problem' => 'Exploring better workflows.',
                'approach' => 'Prototype small tools.',
                'architecture_notes' => 'Laravel API with Next.js UI.',
                'seo_title' => 'AI Sketchpad Lab',
                'seo_description' => 'Lab project notes.',
                'seo_keywords' => ['ai', 'lab'],
            ]),
        ]));
        $labProject->setRelation('featuredImage', $this->makeMedia('Lab screenshot', 'Featured lab image'));
        $labProject->setRelation('seoImage', $this->makeMedia('Lab SEO preview', 'SEO image'));

        $request = Request::create('/api/v1/labs', 'GET');
        $resource = new LabProjectResource($labProject);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('11111111-1111-1111-1111-111111111111', $data['uuid']);
        $this->assertSame('ai-sketchpad', $data['slug']);
        $this->assertSame('AI Sketchpad', $data['title']);
        $this->assertSame('A lab for AI-assisted ideation.', $data['summary']);
        $this->assertSame('Detailed lab notes.', $data['content']);
        $this->assertSame('Exploring better workflows.', $data['problem']);
        $this->assertSame('Prototype small tools.', $data['approach']);
        $this->assertSame('Laravel API with Next.js UI.', $data['architectureNotes']);
        $this->assertSame('building', $data['status']);
        $this->assertTrue($data['isFeatured']);
        $this->assertSame(2, $data['sortOrder']);
        $this->assertNotNull($data['startedAt']);
        $this->assertNotNull($data['publishedAt']);
        $this->assertSame('Lab screenshot', $data['featuredImage']['alt']);
        $this->assertSame('Lab SEO preview', $data['seoImage']['alt']);
        $this->assertSame('AI Sketchpad Lab', $data['seo']['title']);
        $this->assertSame('Lab project notes.', $data['seo']['description']);
        $this->assertSame(['ai', 'lab'], $data['seo']['keywords']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('createdBy', $data);
        $this->assertArrayNotHasKey('updatedBy', $data);
        $this->assertArrayNotHasKey('deletedAt', $data);
    }

    public function test_lab_project_resource_uses_english_fallback_for_missing_french_fields(): void
    {
        $labProject = $this->makeLabProject();
        $labProject->setRelation('translations', new Collection([
            new LabProjectTranslation([
                'locale' => 'en',
                'title' => 'AI Sketchpad',
                'summary' => 'A lab for AI-assisted ideation.',
                'content' => 'Detailed lab notes.',
                'problem' => 'Exploring better workflows.',
                'approach' => 'Prototype small tools.',
                'architecture_notes' => 'Laravel API with Next.js UI.',
                'seo_title' => 'AI Sketchpad Lab',
                'seo_description' => 'Lab project notes.',
                'seo_keywords' => ['ai', 'lab'],
            ]),
            new LabProjectTranslation([
                'locale' => 'fr',
                'title' => 'Carnet IA',
                'summary' => 'Un laboratoire IA.',
            ]),
        ]));
        $labProject->setRelation('featuredImage', $this->makeMedia('Lab screenshot', 'Featured lab image'));
        $labProject->setRelation('seoImage', null);

        $request = Request::create('/api/v1/labs', 'GET', ['locale' => 'fr']);
        $resource = new LabProjectResource($labProject);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Carnet IA', $data['title']);
        $this->assertSame('Un laboratoire IA.', $data['summary']);
        $this->assertSame('Detailed lab notes.', $data['content']);
        $this->assertSame('Exploring better workflows.', $data['problem']);
        $this->assertSame('Prototype small tools.', $data['approach']);
        $this->assertSame('Laravel API with Next.js UI.', $data['architectureNotes']);
        $this->assertSame('Lab screenshot', $data['featuredImage']['alt']);
        $this->assertTrue($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([
            'content',
            'problem',
            'approach',
            'architectureNotes',
            'seo.title',
            'seo.description',
            'seo.keywords',
            'featuredImage.alt',
            'featuredImage.caption',
        ], $meta['fallbackFields']);
    }

    public function test_lab_project_resource_reports_missing_fields_and_hides_private_media(): void
    {
        $labProject = $this->makeLabProject();
        $labProject->setRelation('translations', new Collection([
            new LabProjectTranslation([
                'locale' => 'en',
                'title' => 'AI Sketchpad',
            ]),
        ]));
        $labProject->setRelation('featuredImage', $this->makeMedia('Private lab image', 'Private image', false));
        $labProject->setRelation('seoImage', null);

        $request = Request::create('/api/v1/labs', 'GET');
        $resource = new LabProjectResource($labProject);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('AI Sketchpad', $data['title']);
        $this->assertNull($data['summary']);
        $this->assertNull($data['content']);
        $this->assertNull($data['featuredImage']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([
            'summary',
            'content',
            'problem',
            'approach',
            'architectureNotes',
            'seo.title',
            'seo.description',
            'seo.keywords',
        ], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
    }

    private function makeLabProject(): LabProject
    {
        return new LabProject([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'slug' => 'ai-sketchpad',
            'status' => LabProjectStatus::Building,
            'is_featured' => true,
            'sort_order' => 2,
            'started_at' => now()->subMonth(),
            'published_at' => now(),
        ]);
    }

    private function makeMedia(string $alt, string $caption, bool $isPublic = true): Media
    {
        $media = new Media([
            'uuid' => '22222222-2222-2222-2222-222222222222',
            'disk' => 'public',
            'path' => 'media/lab.jpg',
            'url' => 'https://cdn.example.com/media/lab.jpg',
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
