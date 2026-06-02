<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lab_project_id',
    'locale',
    'title',
    'summary',
    'content',
    'problem',
    'approach',
    'architecture_notes',
    'seo_title',
    'seo_description',
    'seo_keywords',
])]
class LabProjectTranslation extends Model
{
    use HasFactory;

    public function labProject(): BelongsTo
    {
        return $this->belongsTo(LabProject::class);
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
