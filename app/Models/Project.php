<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'slug',
    'status',
    'featured_image_id',
    'seo_image_id',
    'is_featured',
    'sort_order',
    'published_at',
    'created_by',
    'updated_by',
])]
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $project): void {
            if (blank($project->uuid)) {
                $project->uuid = (string) Str::uuid();
            }
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function seoImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'seo_image_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ProjectStatus::Published)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function translation(string $locale): ?ProjectTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }
}
