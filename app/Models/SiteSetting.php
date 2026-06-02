<?php

namespace App\Models;

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
    'site_name',
    'is_active',
    'email',
    'phone',
    'location',
    'primary_domain',
    'frontend_url',
    'admin_url',
    'default_og_image_id',
    'favicon_media_id',
    'social_links',
    'contact_links',
    'metadata',
    'created_by',
    'updated_by',
])]
class SiteSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $siteSetting): void {
            if (blank($siteSetting->uuid)) {
                $siteSetting->uuid = (string) Str::uuid();
            }
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SiteSettingTranslation::class);
    }

    public function defaultOgImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'default_og_image_id');
    }

    public function faviconMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'favicon_media_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function translation(string $locale): ?SiteSettingTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'social_links' => 'array',
            'contact_links' => 'array',
            'metadata' => 'array',
        ];
    }
}
