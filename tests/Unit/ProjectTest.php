<?php

namespace Tests\Unit;

use App\Enums\ProjectStatus;
use App\Models\Project;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function test_project_casts_foundation_fields(): void
    {
        $project = new Project([
            'status' => ProjectStatus::Published,
            'is_featured' => '1',
            'sort_order' => '10',
        ]);

        $this->assertSame(ProjectStatus::Published, $project->status);
        $this->assertTrue($project->is_featured);
        $this->assertSame(10, $project->sort_order);
    }
}
