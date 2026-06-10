<?php

namespace Tests\Unit;

use App\Enums\HeroContentStatus;
use App\Filament\Resources\HeroContentResource;
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

    public function test_hero_content_cta_urls_allow_anchors_and_relative_paths(): void
    {
        foreach (['#projects', '#contact', '/en#projects', '/fr#contact', '/projects', 'https://afifelcharif.com'] as $value) {
            $this->assertHeroContentCtaUrlPasses($value);
        }
    }

    public function test_hero_content_cta_urls_reject_unsafe_values(): void
    {
        foreach (['javascript:alert(1)', '//evil.example', '#', '/en projects', "/en\n#projects"] as $value) {
            $this->assertHeroContentCtaUrlFails($value);
        }
    }

    private function assertHeroContentCtaUrlPasses(string $value): void
    {
        $this->assertFalse($this->heroContentCtaUrlFails($value));
    }

    private function assertHeroContentCtaUrlFails(string $value): void
    {
        $this->assertTrue($this->heroContentCtaUrlFails($value));
    }

    private function heroContentCtaUrlFails(string $value): bool
    {
        $method = new \ReflectionMethod(HeroContentResource::class, 'ctaUrlRule');
        $rule = $method->invoke(null);
        $failed = false;

        $rule('cta_url', $value, function () use (&$failed): void {
            $failed = true;
        });

        return $failed;
    }
}
