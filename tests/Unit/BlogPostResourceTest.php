<?php

namespace Tests\Unit;

use App\Enums\BlogPostStatus;
use App\Enums\MediaType;
use App\Http\Resources\Api\V1\BlogPostResource;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Tests\TestCase;

class BlogPostResourceTest extends TestCase
{
    public function test_blog_post_resource_serializes_public_shape(): void
    {
        $blogPost = $this->makeBlogPost();
        $blogPost->setRelation('translations', new Collection([
            new BlogPostTranslation([
                'locale' => 'en',
                'title' => 'Building a Portfolio CMS',
                'excerpt' => 'Notes on the CMS foundation.',
                'content' => 'Detailed engineering notes.',
                'seo_title' => 'Portfolio CMS notes',
                'seo_description' => 'Engineering notes about the CMS.',
                'seo_keywords' => ['cms', 'laravel'],
            ]),
        ]));
        $blogPost->setRelation('featuredImage', $this->makeMedia('Blog cover', 'Featured blog image'));
        $blogPost->setRelation('seoImage', $this->makeMedia('Blog SEO preview', 'SEO image'));

        $request = Request::create('/api/v1/blog-posts/portfolio-cms-notes', 'GET');
        $resource = new BlogPostResource($blogPost);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('11111111-1111-1111-1111-111111111111', $data['uuid']);
        $this->assertSame('portfolio-cms-notes', $data['slug']);
        $this->assertSame('Building a Portfolio CMS', $data['title']);
        $this->assertSame('Notes on the CMS foundation.', $data['excerpt']);
        $this->assertSame('Detailed engineering notes.', $data['content']);
        $this->assertSame('published', $data['status']);
        $this->assertTrue($data['isFeatured']);
        $this->assertSame(1, $data['sortOrder']);
        $this->assertNotNull($data['publishedAt']);
        $this->assertSame('Blog cover', $data['featuredImage']['alt']);
        $this->assertSame('Blog SEO preview', $data['seoImage']['alt']);
        $this->assertSame('Portfolio CMS notes', $data['seo']['title']);
        $this->assertSame('Engineering notes about the CMS.', $data['seo']['description']);
        $this->assertSame(['cms', 'laravel'], $data['seo']['keywords']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('createdBy', $data);
        $this->assertArrayNotHasKey('updatedBy', $data);
        $this->assertArrayNotHasKey('deletedAt', $data);
    }

    public function test_blog_post_resource_uses_english_fallback_for_missing_french_fields(): void
    {
        $blogPost = $this->makeBlogPost();
        $blogPost->setRelation('translations', new Collection([
            new BlogPostTranslation([
                'locale' => 'en',
                'title' => 'Building a Portfolio CMS',
                'excerpt' => 'Notes on the CMS foundation.',
                'content' => 'Detailed engineering notes.',
                'seo_title' => 'Portfolio CMS notes',
                'seo_description' => 'Engineering notes about the CMS.',
                'seo_keywords' => ['cms', 'laravel'],
            ]),
            new BlogPostTranslation([
                'locale' => 'fr',
                'title' => 'Construire un CMS de portfolio',
            ]),
        ]));
        $blogPost->setRelation('featuredImage', $this->makeMedia('Blog cover', 'Featured blog image'));
        $blogPost->setRelation('seoImage', null);

        $request = Request::create('/api/v1/blog-posts/portfolio-cms-notes', 'GET', ['locale' => 'fr']);
        $resource = new BlogPostResource($blogPost);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Construire un CMS de portfolio', $data['title']);
        $this->assertSame('Notes on the CMS foundation.', $data['excerpt']);
        $this->assertSame('Detailed engineering notes.', $data['content']);
        $this->assertSame('Portfolio CMS notes', $data['seo']['title']);
        $this->assertSame('Blog cover', $data['featuredImage']['alt']);
        $this->assertTrue($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([
            'excerpt',
            'content',
            'seo.title',
            'seo.description',
            'seo.keywords',
            'featuredImage.alt',
            'featuredImage.caption',
        ], $meta['fallbackFields']);
    }

    public function test_blog_post_resource_reports_missing_fields_and_hides_private_media(): void
    {
        $blogPost = $this->makeBlogPost();
        $blogPost->setRelation('translations', new Collection([
            new BlogPostTranslation([
                'locale' => 'en',
                'title' => 'Building a Portfolio CMS',
            ]),
        ]));
        $blogPost->setRelation('featuredImage', $this->makeMedia('Private cover', 'Private image', false));
        $blogPost->setRelation('seoImage', null);

        $request = Request::create('/api/v1/blog-posts/portfolio-cms-notes', 'GET');
        $resource = new BlogPostResource($blogPost);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Building a Portfolio CMS', $data['title']);
        $this->assertNull($data['excerpt']);
        $this->assertNull($data['content']);
        $this->assertNull($data['featuredImage']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([
            'excerpt',
            'content',
            'seo.title',
            'seo.description',
            'seo.keywords',
        ], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
    }

    private function makeBlogPost(): BlogPost
    {
        return new BlogPost([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'slug' => 'portfolio-cms-notes',
            'status' => BlogPostStatus::Published,
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
            'path' => 'media/blog.jpg',
            'url' => 'https://cdn.example.com/media/blog.jpg',
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
