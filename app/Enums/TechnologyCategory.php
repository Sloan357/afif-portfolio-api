<?php

namespace App\Enums;

enum TechnologyCategory: string
{
    case Language = 'language';
    case Framework = 'framework';
    case Library = 'library';
    case Database = 'database';
    case Tool = 'tool';
    case Platform = 'platform';
    case Cloud = 'cloud';
    case Cms = 'cms';
    case Design = 'design';
    case Testing = 'testing';
    case Other = 'other';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $category): array => [$category->value => str($category->value)->headline()->toString()])
            ->all();
    }
}
