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
    'company',
    'company_url',
    'location',
    'start_date',
    'end_date',
    'is_current',
    'sort_order',
    'is_visible',
    'created_by',
    'updated_by',
])]
class Experience extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $experience): void {
            if (blank($experience->uuid)) {
                $experience->uuid = (string) Str::uuid();
            }
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ExperienceTranslation::class);
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

    public function translation(string $locale): ?ExperienceTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }
}
