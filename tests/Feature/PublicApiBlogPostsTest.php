<?php

namespace Tests\Feature;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiBlogPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_posts_list_returns_published_posts(): void
    {
        $published = $this->createBlogPost('published-post', BlogPostStatus::Published);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $published->id,
            'locale' => 'en',
            'title' => 'Published Post',
            'excerpt' => 'Visible excerpt.',
            'content' => 'Visible content.',
        ]);

        $response = $this->getJson('/api/v1/blog-posts');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'published-post')
            ->assertJsonPath('data.0.title', 'Published Post')
            ->assertJsonPath('data.0.status', 'published')
            ->assertJsonPath('meta.resolvedLocale', 'en');
    }

    public function test_blog_posts_list_hides_drafts(): void
    {
        $published = $this->createBlogPost('published-post', BlogPostStatus::Published);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $published->id,
            'locale' => 'en',
            'title' => 'Published Post',
        ]);

        $draft = $this->createBlogPost('draft-post', BlogPostStatus::Draft);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $draft->id,
            'locale' => 'en',
            'title' => 'Draft Post',
        ]);

        $response = $this->getJson('/api/v1/blog-posts');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'published-post')
            ->assertJsonMissing(['slug' => 'draft-post']);
    }

    public function test_blog_posts_detail_returns_published_post_by_slug(): void
    {
        $post = $this->createBlogPost('portfolio-cms-notes', BlogPostStatus::Published);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $post->id,
            'locale' => 'en',
            'title' => 'Portfolio CMS Notes',
            'excerpt' => 'Post excerpt.',
            'content' => 'Post content.',
            'seo_title' => 'Portfolio CMS SEO',
            'seo_description' => 'SEO description.',
            'seo_keywords' => ['portfolio'],
        ]);

        $response = $this->getJson('/api/v1/blog-posts/portfolio-cms-notes');

        $response
            ->assertOk()
            ->assertJsonPath('data.slug', 'portfolio-cms-notes')
            ->assertJsonPath('data.title', 'Portfolio CMS Notes')
            ->assertJsonPath('data.content', 'Post content.')
            ->assertJsonPath('data.seo.title', 'Portfolio CMS SEO')
            ->assertJsonPath('links.self', url('/api/v1/blog-posts/portfolio-cms-notes'));
    }

    public function test_blog_posts_detail_uses_english_fallback_for_missing_french_fields(): void
    {
        $post = $this->createBlogPost('portfolio-cms-notes', BlogPostStatus::Published);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $post->id,
            'locale' => 'en',
            'title' => 'Portfolio CMS Notes',
            'excerpt' => 'English excerpt.',
            'content' => 'English content.',
        ]);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $post->id,
            'locale' => 'fr',
            'title' => 'Notes CMS portfolio',
        ]);

        $response = $this->getJson('/api/v1/blog-posts/portfolio-cms-notes?locale=fr');

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Notes CMS portfolio')
            ->assertJsonPath('data.excerpt', 'English excerpt.')
            ->assertJsonPath('data.content', 'English content.')
            ->assertJsonPath('meta.resolvedLocale', 'fr')
            ->assertJsonPath('meta.fallbackUsed', true)
            ->assertJsonPath('meta.fallbackFields', ['excerpt', 'content']);
    }

    public function test_blog_posts_detail_returns_404_envelope_for_invalid_slug(): void
    {
        $this->createBlogPost('portfolio-cms-notes', BlogPostStatus::Published);

        $response = $this->getJson('/api/v1/blog-posts/missing-post');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('links.self', url('/api/v1/blog-posts/missing-post'));
    }

    private function createBlogPost(string $slug, BlogPostStatus $status): BlogPost
    {
        return BlogPost::query()->create([
            'slug' => $slug,
            'status' => $status,
            'is_featured' => false,
            'published_at' => $status === BlogPostStatus::Published ? now() : null,
        ]);
    }
}
