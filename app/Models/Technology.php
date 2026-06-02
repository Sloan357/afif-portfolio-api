<?php

namespace App\Models;

use App\Enums\TechnologyCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'slug',
    'name',
    'category',
    'website_url',
    'icon_media_id',
    'color',
    'sort_order',
    'is_visible',
    'created_by',
    'updated_by',
])]
class Technology extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $technology): void {
            if (blank($technology->uuid)) {
                $technology->uuid = (string) Str::uuid();
            }
        });
    }

    public function iconMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'icon_media_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => TechnologyCategory::class,
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }
}
