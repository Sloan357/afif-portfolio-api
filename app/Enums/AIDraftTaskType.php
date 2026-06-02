<?php

namespace App\Enums;

enum AIDraftTaskType: string
{
    case Translation = 'translation';
    case Seo = 'seo';
    case Summary = 'summary';
    case ArchitectureNotes = 'architecture_notes';
    case QualitySuggestions = 'quality_suggestions';
    case Rewrite = 'rewrite';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $taskType): array => [$taskType->value => str($taskType->value)->headline()->toString()])
            ->all();
    }
}
