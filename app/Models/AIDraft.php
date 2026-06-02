<?php

namespace App\Models;

use App\Enums\AIDraftStatus;
use App\Enums\AIDraftTaskType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'draftable_type',
    'draftable_id',
    'locale',
    'source_locale',
    'task_type',
    'status',
    'field',
    'title',
    'input_snapshot',
    'draft_value',
    'notes',
    'provider',
    'model',
    'prompt_version',
    'source_hash',
    'reviewed_by',
    'reviewed_at',
    'applied_by',
    'applied_at',
    'rejected_by',
    'rejected_at',
    'created_by',
    'updated_by',
])]
class AIDraft extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ai_drafts';

    protected static function booted(): void
    {
        static::creating(function (self $aiDraft): void {
            if (blank($aiDraft->uuid)) {
                $aiDraft->uuid = (string) Str::uuid();
            }
        });
    }

    public function draftable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'task_type' => AIDraftTaskType::class,
            'status' => AIDraftStatus::class,
            'input_snapshot' => 'array',
            'draft_value' => 'array',
            'reviewed_at' => 'datetime',
            'applied_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }
}
