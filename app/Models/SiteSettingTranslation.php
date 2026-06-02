<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'site_setting_id',
    'locale',
    'tagline',
    'description',
    'default_seo_title',
    'default_seo_description',
    'default_seo_keywords',
    'footer_text',
])]
class SiteSettingTranslation extends Model
{
    use HasFactory;

    public function siteSetting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_seo_keywords' => 'array',
        ];
    }
}
