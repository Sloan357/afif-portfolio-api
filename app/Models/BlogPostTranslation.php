<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blog_post_id',
    'locale',
    'title',
    'excerpt',
    'content',
    'seo_title',
    'seo_description',
    'seo_keywords',
])]
class BlogPostTranslation extends Model
{
    use HasFactory;

    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seo_keywords' => 'array',
        ];
    }
}
