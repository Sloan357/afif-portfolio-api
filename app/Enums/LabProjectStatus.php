<?php

namespace App\Enums;

enum LabProjectStatus: string
{
    case Idea = 'idea';
    case Building = 'building';
    case Paused = 'paused';
    case Shipped = 'shipped';
    case Archived = 'archived';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => str($status->value)->headline()->toString()])
            ->all();
    }
}
