<?php

namespace Tests\Unit;

use App\Enums\AIDraftTaskType;
use App\Enums\AIRequestStatus;
use App\Models\AIRequestLog;
use PHPUnit\Framework\TestCase;

class AIRequestLogTest extends TestCase
{
    public function test_ai_request_log_casts_foundation_fields(): void
    {
        $aiRequestLog = new AIRequestLog([
            'task_type' => AIDraftTaskType::Seo,
            'status' => AIRequestStatus::Succeeded,
            'input_tokens' => '100',
            'output_tokens' => '50',
            'total_tokens' => '150',
            'cost_minor_units' => '2',
            'duration_ms' => '1200',
            'metadata' => ['temperature' => 0.2],
        ]);

        $this->assertSame(AIDraftTaskType::Seo, $aiRequestLog->task_type);
        $this->assertSame(AIRequestStatus::Succeeded, $aiRequestLog->status);
        $this->assertSame(100, $aiRequestLog->input_tokens);
        $this->assertSame(50, $aiRequestLog->output_tokens);
        $this->assertSame(150, $aiRequestLog->total_tokens);
        $this->assertSame(2, $aiRequestLog->cost_minor_units);
        $this->assertSame(1200, $aiRequestLog->duration_ms);
        $this->assertSame(['temperature' => 0.2], $aiRequestLog->metadata);
    }
}
