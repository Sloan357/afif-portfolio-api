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

    public function test_blog_posts_list_returns_paginated_envelope(): void
    {
        foreach (range(1, 13) as $index) {
            $post = $this->createBlogPost('post-'.$index, BlogPostStatus::Published);
            BlogPostTranslation::query()->create([
                'blog_post_id' => $post->id,
                'locale' => 'en',
                'title' => 'Post '.$index,
            ]);
        }

        $response = $this->getJson('/api/v1/blog-posts');

        $response
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('meta.pagination.currentPage', 1)
            ->assertJsonPath('meta.pagination.perPage', 12)
            ->assertJsonPath('meta.pagination.lastPage', 2)
            ->assertJsonPath('meta.pagination.total', 13)
            ->assertJsonPath('meta.pagination.from', 1)
            ->assertJsonPath('meta.pagination.to', 12)
            ->assertJsonStructure([
                'links' => ['self', 'first', 'last', 'prev', 'next'],
            ]);
    }

    public function test_blog_posts_page_changes_records(): void
    {
        foreach (range(1, 3) as $index) {
            $post = $this->createBlogPost('post-'.$index, BlogPostStatus::Published, $index);
            BlogPostTranslation::query()->create([
                'blog_post_id' => $post->id,
                'locale' => 'en',
                'title' => 'Post '.$index,
            ]);
        }

        $firstPage = $this->getJson('/api/v1/blog-posts?perPage=1&page=1');
        $secondPage = $this->getJson('/api/v1/blog-posts?perPage=1&page=2');

        $firstPage->assertJsonPath('data.0.slug', 'post-1');
        $secondPage
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'post-2')
            ->assertJsonPath('meta.pagination.currentPage', 2);
    }

    public function test_blog_posts_per_page_controls_size(): void
    {
        foreach (range(1, 3) as $index) {
            $post = $this->createBlogPost('post-'.$index, BlogPostStatus::Published, $index);
            BlogPostTranslation::query()->create([
                'blog_post_id' => $post->id,
                'locale' => 'en',
                'title' => 'Post '.$index,
            ]);
        }

        $response = $this->getJson('/api/v1/blog-posts?perPage=2');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.pagination.perPage', 2)
            ->assertJsonPath('meta.pagination.lastPage', 2);
    }

    public function test_blog_posts_invalid_page_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/blog-posts?page=0');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.page.0', 'The page must be an integer greater than or equal to 1.');
    }

    public function test_blog_posts_invalid_per_page_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/blog-posts?perPage=abc');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.perPage.0', 'The perPage must be an integer between 1 and 50.');
    }

    public function test_blog_posts_per_page_max_is_enforced(): void
    {
        $response = $this->getJson('/api/v1/blog-posts?perPage=51');

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.perPage.0', 'The perPage must be an integer between 1 and 50.');
    }


    public function test_blog_posts_list_hides_review_archived_and_future_published_posts(): void
    {
        $published = $this->createBlogPost('published-post', BlogPostStatus::Published);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $published->id,
            'locale' => 'en',
            'title' => 'Published Post',
        ]);

        foreach ([BlogPostStatus::Review, BlogPostStatus::Archived] as $status) {
            $hidden = $this->createBlogPost($status->value.'-post', $status);
            BlogPostTranslation::query()->create([
                'blog_post_id' => $hidden->id,
                'locale' => 'en',
                'title' => $status->value.' Post',
            ]);
        }

        $future = BlogPost::query()->create([
            'slug' => 'future-post',
            'status' => BlogPostStatus::Published,
            'published_at' => now()->addDay(),
        ]);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $future->id,
            'locale' => 'en',
            'title' => 'Future Post',
        ]);

        $response = $this->getJson('/api/v1/blog-posts');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'published-post')
            ->assertJsonMissing(['slug' => 'review-post'])
            ->assertJsonMissing(['slug' => 'archived-post'])
            ->assertJsonMissing(['slug' => 'future-post']);
    }

    public function test_blog_posts_detail_returns_404_envelope_for_unpublished_slug(): void
    {
        $hiddenPosts = [
            $this->createBlogPost('draft-post', BlogPostStatus::Draft),
            $this->createBlogPost('review-post', BlogPostStatus::Review),
            $this->createBlogPost('archived-post', BlogPostStatus::Archived),
            BlogPost::query()->create([
                'slug' => 'future-post',
                'status' => BlogPostStatus::Published,
                'published_at' => now()->addDay(),
            ]),
        ];

        foreach ($hiddenPosts as $post) {
            BlogPostTranslation::query()->create([
                'blog_post_id' => $post->id,
                'locale' => 'en',
                'title' => 'Hidden Post',
            ]);

            $response = $this->getJson('/api/v1/blog-posts/'.$post->slug);

            $response
                ->assertStatus(404)
                ->assertJsonPath('data', null)
                ->assertJsonPath('meta.resolvedLocale', 'en')
                ->assertJsonPath('links.self', url('/api/v1/blog-posts/'.$post->slug));
        }
    }

    public function test_blog_posts_paginated_list_includes_fallback_metadata(): void
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

        $response = $this->getJson('/api/v1/blog-posts?locale=fr&perPage=1');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Notes CMS portfolio')
            ->assertJsonPath('data.0.excerpt', 'English excerpt.')
            ->assertJsonPath('data.0.content', 'English content.')
            ->assertJsonPath('meta.fallbackUsed', true)
            ->assertJsonPath('meta.pagination.perPage', 1)
            ->assertJsonPath('links.self', url('/api/v1/blog-posts?locale=fr&perPage=1&page=1'));

        $fallbackFields = $response->json('meta.fallbackFields');

        $this->assertContains('blogPosts.0.excerpt', $fallbackFields);
        $this->assertContains('blogPosts.0.content', $fallbackFields);
    }

    public function test_blog_posts_pagination_handles_out_of_range_page(): void
    {
        $post = $this->createBlogPost('post-1', BlogPostStatus::Published);
        BlogPostTranslation::query()->create([
            'blog_post_id' => $post->id,
            'locale' => 'en',
            'title' => 'Post 1',
        ]);

        $response = $this->getJson('/api/v1/blog-posts?page=99&perPage=1');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.pagination.currentPage', 99)
            ->assertJsonPath('meta.pagination.lastPage', 1)
            ->assertJsonPath('meta.pagination.from', null)
            ->assertJsonPath('meta.pagination.to', null)
            ->assertJsonPath('links.next', null);
    }

    public function test_blog_posts_pagination_accepts_max_per_page(): void
    {
        foreach (range(1, 3) as $index) {
            $post = $this->createBlogPost('max-post-'.$index, BlogPostStatus::Published, $index);
            BlogPostTranslation::query()->create([
                'blog_post_id' => $post->id,
                'locale' => 'en',
                'title' => 'Max Post '.$index,
            ]);
        }

        $response = $this->getJson('/api/v1/blog-posts?perPage=50');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.pagination.perPage', 50);
    }

    public function test_blog_posts_pagination_rejects_array_parameters(): void
    {
        $this->getJson('/api/v1/blog-posts?page[]=1')
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.page.0', 'The page must be an integer greater than or equal to 1.');

        $this->getJson('/api/v1/blog-posts?perPage[]=12')
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.perPage.0', 'The perPage must be an integer between 1 and 50.');
    }

    private function createBlogPost(string $slug, BlogPostStatus $status, ?int $sortOrder = null): BlogPost
    {
        return BlogPost::query()->create([
            'slug' => $slug,
            'status' => $status,
            'is_featured' => false,
            'sort_order' => $sortOrder,
            'published_at' => $status === BlogPostStatus::Published ? now() : null,
        ]);
    }
}
