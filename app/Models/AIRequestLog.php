<?php

namespace App\Models;

use App\Enums\AIDraftTaskType;
use App\Enums\AIRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'ai_draft_id',
    'requestable_type',
    'requestable_id',
    'task_type',
    'status',
    'provider',
    'model',
    'prompt_version',
    'locale',
    'source_locale',
    'input_tokens',
    'output_tokens',
    'total_tokens',
    'cost_minor_units',
    'currency',
    'duration_ms',
    'request_hash',
    'input_summary',
    'output_summary',
    'error_code',
    'error_message',
    'metadata',
    'started_at',
    'finished_at',
    'created_by',
])]
class AIRequestLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ai_request_logs';

    protected static function booted(): void
    {
        static::creating(function (self $aiRequestLog): void {
            if (blank($aiRequestLog->uuid)) {
                $aiRequestLog->uuid = (string) Str::uuid();
            }
        });
    }

    public function aiDraft(): BelongsTo
    {
        return $this->belongsTo(AIDraft::class);
    }

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'task_type' => AIDraftTaskType::class,
            'status' => AIRequestStatus::class,
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
            'cost_minor_units' => 'integer',
            'duration_ms' => 'integer',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
