<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hero_content_id',
    'locale',
    'badge',
    'headline',
    'description',
    'primary_cta_label',
    'secondary_cta_label',
    'capabilities',
    'architecture_items',
])]
class HeroContentTranslation extends Model
{
    use HasFactory;

    public function heroContent(): BelongsTo
    {
        return $this->belongsTo(HeroContent::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'architecture_items' => 'array',
        ];
    }
}
