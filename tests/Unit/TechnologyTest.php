<?php

namespace Tests\Unit;

use App\Enums\TechnologyCategory;
use App\Models\Technology;
use PHPUnit\Framework\TestCase;

class TechnologyTest extends TestCase
{
    public function test_technology_casts_foundation_fields(): void
    {
        $technology = new Technology([
            'category' => TechnologyCategory::Framework,
            'sort_order' => '10',
            'is_visible' => '1',
        ]);

        $this->assertSame(TechnologyCategory::Framework, $technology->category);
        $this->assertSame(10, $technology->sort_order);
        $this->assertTrue($technology->is_visible);
    }
}
