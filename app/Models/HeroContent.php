<?php

namespace App\Models;

use App\Enums\HeroContentStatus;
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
    'key',
    'status',
    'is_active',
    'primary_cta_url',
    'secondary_cta_url',
    'hero_image_id',
    'og_image_id',
    'sort_order',
    'published_at',
    'created_by',
    'updated_by',
])]
class HeroContent extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $heroContent): void {
            if (blank($heroContent->uuid)) {
                $heroContent->uuid = (string) Str::uuid();
            }
        });

        static::saved(function (self $heroContent): void {
            if (! $heroContent->is_active) {
                return;
            }

            self::withoutEvents(function () use ($heroContent): void {
                self::query()
                    ->whereKeyNot($heroContent->getKey())
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            });
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(HeroContentTranslation::class);
    }

    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_image_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
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
            ->where('status', HeroContentStatus::Published)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->published();
    }

    public function translation(string $locale): ?HeroContentTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => HeroContentStatus::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }
}
