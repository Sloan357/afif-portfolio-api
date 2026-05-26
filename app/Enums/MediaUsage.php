<?php

namespace App\Enums;

enum MediaUsage: string
{
    case General = 'general';
    case Project = 'project';
    case Lab = 'lab';
    case Blog = 'blog';
    case Seo = 'seo';
    case Gallery = 'gallery';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $usage): array => [$usage->value => str($usage->value)->headline()->toString()])
            ->all();
    }
}
