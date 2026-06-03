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
     * @param  array<string, array<int, string>>  $errors
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $links
     */
    public static function validationError(array $errors, array $meta = [], array $links = []): JsonResponse
    {
        return response()->json([
            'data' => null,
            'errors' => $errors,
            'meta' => array_merge(self::defaultMeta(), $meta),
            'links' => (object) $links,
        ], 422);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $links
     */
    public static function notFound(array $meta = [], array $links = []): JsonResponse
    {
        return self::make(null, $meta, $links, 404);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $links
     */
    public static function error(array $errors, array $meta = [], array $links = [], int $status = 500): JsonResponse
    {
        return response()->json([
            'data' => null,
            'errors' => $errors,
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
            'defaultLocale' => PublicApiLocale::DEFAULT_LOCALE,
            'generatedAt' => now()->toIso8601String(),
        ];
    }
}
