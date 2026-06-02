<?php

namespace Tests\Unit;

use App\Enums\HeroContentStatus;
use App\Models\HeroContent;
use App\Models\HeroContentTranslation;
use PHPUnit\Framework\TestCase;

class HeroContentTest extends TestCase
{
    public function test_hero_content_casts_foundation_fields(): void
    {
        $heroContent = new HeroContent([
            'status' => HeroContentStatus::Published,
            'is_active' => '1',
            'sort_order' => '10',
        ]);

        $this->assertSame(HeroContentStatus::Published, $heroContent->status);
        $this->assertTrue($heroContent->is_active);
        $this->assertSame(10, $heroContent->sort_order);
    }

    public function test_hero_content_translation_casts_json_fields(): void
    {
        $translation = new HeroContentTranslation([
            'capabilities' => ['Laravel APIs'],
            'architecture_items' => [['title' => 'CMS']],
        ]);

        $this->assertSame(['Laravel APIs'], $translation->capabilities);
        $this->assertSame([['title' => 'CMS']], $translation->architecture_items);
    }
}
