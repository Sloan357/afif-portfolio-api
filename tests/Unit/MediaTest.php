<?php

namespace Tests\Unit;

use App\Enums\MediaType;
use App\Enums\MediaUsage;
use App\Models\Media;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    public function test_media_casts_foundation_fields(): void
    {
        $media = new Media([
            'type' => MediaType::Image,
            'usage' => MediaUsage::General,
            'is_public' => '1',
            'alt_text' => ['en' => 'Project screenshot'],
            'caption' => ['fr' => 'Capture du projet'],
            'metadata' => ['source' => 'upload'],
            'variants' => ['thumbnail' => ['width' => 320]],
        ]);

        $this->assertSame(MediaType::Image, $media->type);
        $this->assertSame(MediaUsage::General, $media->usage);
        $this->assertTrue($media->is_public);
        $this->assertSame(['en' => 'Project screenshot'], $media->alt_text);
        $this->assertSame(['fr' => 'Capture du projet'], $media->caption);
        $this->assertSame(['source' => 'upload'], $media->metadata);
        $this->assertSame(['thumbnail' => ['width' => 320]], $media->variants);
    }
}
