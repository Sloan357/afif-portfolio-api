<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Enums\MediaUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'disk',
    'path',
    'url',
    'mime_type',
    'extension',
    'size_bytes',
    'width',
    'height',
    'duration_seconds',
    'type',
    'usage',
    'alt_text',
    'caption',
    'metadata',
    'variants',
    'sort_order',
    'is_public',
    'created_by',
])]
class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $media): void {
            if (blank($media->uuid)) {
                $media->uuid = (string) Str::uuid();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alt_text' => 'array',
            'caption' => 'array',
            'metadata' => 'array',
            'variants' => 'array',
            'is_public' => 'boolean',
            'type' => MediaType::class,
            'usage' => MediaUsage::class,
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
