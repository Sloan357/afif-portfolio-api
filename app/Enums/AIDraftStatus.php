<?php

namespace App\Enums;

enum AIDraftStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Applied = 'applied';
    case Rejected = 'rejected';
    case Stale = 'stale';

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
