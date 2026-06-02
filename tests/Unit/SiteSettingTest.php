<?php

namespace Tests\Unit;

use App\Models\SiteSetting;
use App\Models\SiteSettingTranslation;
use PHPUnit\Framework\TestCase;

class SiteSettingTest extends TestCase
{
    public function test_site_setting_casts_foundation_fields(): void
    {
        $siteSetting = new SiteSetting([
            'is_active' => '1',
            'social_links' => [['platform' => 'github']],
            'contact_links' => [['type' => 'email']],
            'metadata' => ['theme' => 'default'],
        ]);

        $this->assertTrue($siteSetting->is_active);
        $this->assertSame([['platform' => 'github']], $siteSetting->social_links);
        $this->assertSame([['type' => 'email']], $siteSetting->contact_links);
        $this->assertSame(['theme' => 'default'], $siteSetting->metadata);
    }

    public function test_site_setting_translation_casts_seo_keywords(): void
    {
        $translation = new SiteSettingTranslation([
            'default_seo_keywords' => ['portfolio', 'software'],
        ]);

        $this->assertSame(['portfolio', 'software'], $translation->default_seo_keywords);
    }
}
