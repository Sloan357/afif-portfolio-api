<?php

namespace Tests\Unit;

use App\Enums\AIDraftStatus;
use App\Enums\AIDraftTaskType;
use App\Models\AIDraft;
use PHPUnit\Framework\TestCase;

class AIDraftTest extends TestCase
{
    public function test_ai_draft_casts_foundation_fields(): void
    {
        $aiDraft = new AIDraft([
            'task_type' => AIDraftTaskType::Translation,
            'status' => AIDraftStatus::PendingReview,
            'input_snapshot' => ['source' => 'English copy'],
            'draft_value' => ['headline' => 'French copy'],
        ]);

        $this->assertSame(AIDraftTaskType::Translation, $aiDraft->task_type);
        $this->assertSame(AIDraftStatus::PendingReview, $aiDraft->status);
        $this->assertSame(['source' => 'English copy'], $aiDraft->input_snapshot);
        $this->assertSame(['headline' => 'French copy'], $aiDraft->draft_value);
    }
}
