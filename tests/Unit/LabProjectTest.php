<?php

namespace Tests\Unit;

use App\Enums\LabProjectStatus;
use App\Models\LabProject;
use PHPUnit\Framework\TestCase;

class LabProjectTest extends TestCase
{
    public function test_lab_project_casts_foundation_fields(): void
    {
        $labProject = new LabProject([
            'status' => LabProjectStatus::Building,
            'is_featured' => '1',
            'sort_order' => '10',
        ]);

        $this->assertSame(LabProjectStatus::Building, $labProject->status);
        $this->assertTrue($labProject->is_featured);
        $this->assertSame(10, $labProject->sort_order);
    }
}
