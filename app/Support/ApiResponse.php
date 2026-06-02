<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $links
     */
    public static function make(mixed $data = null, array $meta = [], array $links = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => array_merge(self::defaultMeta(), $meta),
            'links' => (object) $links,
        ], $status);
    }

    /**
     * @return array<string, string>
     */
    public static function defaultMeta(): array
    {
        return [
            'apiVersion' => 'v1',
            'locale' => app()->getLocale(),
            'defaultLocale' => (string) config('app.locale', 'en'),
            'generatedAt' => now()->toIso8601String(),
        ];
    }
}
