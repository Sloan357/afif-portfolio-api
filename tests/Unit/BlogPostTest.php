<?php

namespace Tests\Unit;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use PHPUnit\Framework\TestCase;

class BlogPostTest extends TestCase
{
    public function test_blog_post_casts_foundation_fields(): void
    {
        $blogPost = new BlogPost([
            'status' => BlogPostStatus::Published,
            'is_featured' => '1',
            'sort_order' => '10',
        ]);

        $this->assertSame(BlogPostStatus::Published, $blogPost->status);
        $this->assertTrue($blogPost->is_featured);
        $this->assertSame(10, $blogPost->sort_order);
    }

    public function test_blog_post_translation_casts_seo_keywords(): void
    {
        $translation = new BlogPostTranslation([
            'seo_keywords' => ['laravel', 'filament'],
        ]);

        $this->assertSame(['laravel', 'filament'], $translation->seo_keywords);
    }
}
