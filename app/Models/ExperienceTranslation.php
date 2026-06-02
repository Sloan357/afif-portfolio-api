<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'experience_id',
    'locale',
    'role',
    'summary',
    'responsibilities',
])]
class ExperienceTranslation extends Model
{
    use HasFactory;

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'responsibilities' => 'array',
        ];
    }
}
