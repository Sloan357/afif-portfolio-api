<?php

namespace App\Enums;

enum AIRequestStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case RateLimited = 'rate_limited';
    case TimedOut = 'timed_out';

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
