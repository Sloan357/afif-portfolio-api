<?php

namespace Tests\Unit;

use App\Models\Experience;
use App\Models\ExperienceTranslation;
use PHPUnit\Framework\TestCase;

class ExperienceTest extends TestCase
{
    public function test_experience_casts_foundation_fields(): void
    {
        $experience = new Experience([
            'is_current' => '1',
            'sort_order' => '10',
            'is_visible' => '1',
        ]);

        $this->assertTrue($experience->is_current);
        $this->assertSame(10, $experience->sort_order);
        $this->assertTrue($experience->is_visible);
    }

    public function test_experience_translation_casts_responsibilities(): void
    {
        $translation = new ExperienceTranslation([
            'responsibilities' => ['Built CMS foundation'],
        ]);

        $this->assertSame(['Built CMS foundation'], $translation->responsibilities);
    }
}
