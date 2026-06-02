<?php

namespace App\Support;

use Illuminate\Http\Request;

class PublicApiLocale
{
    public const DEFAULT_LOCALE = 'en';

    public const SUPPORTED_LOCALES = ['en', 'fr'];

    /**
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    public static function resolve(Request $request): array
    {
        $requestedLocale = $request->query('locale');
        $requestedLocale = is_string($requestedLocale) && $requestedLocale !== '' ? $requestedLocale : null;

        return self::fallbackMeta(
            requestedLocale: $requestedLocale,
            resolvedLocale: self::isSupported($requestedLocale) ? $requestedLocale : self::DEFAULT_LOCALE,
            fallbackUsed: $requestedLocale !== null && ! self::isSupported($requestedLocale),
        );
    }

    public static function isSupported(?string $locale): bool
    {
        return in_array($locale, self::SUPPORTED_LOCALES, true);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function validationErrors(?string $locale): array
    {
        return [
            'locale' => [
                sprintf(
                    'The locale must be one of: %s.',
                    implode(', ', self::SUPPORTED_LOCALES),
                ),
            ],
        ];
    }

    /**
     * @param  array<int, string>  $missingFields
     * @param  array<int, string>  $fallbackFields
     * @return array{requestedLocale: ?string, resolvedLocale: string, defaultLocale: string, fallbackLocale: string, fallbackUsed: bool, missingFields: array<int, string>, fallbackFields: array<int, string>}
     */
    public static function fallbackMeta(
        ?string $requestedLocale,
        string $resolvedLocale,
        bool $fallbackUsed = false,
        array $missingFields = [],
        array $fallbackFields = [],
    ): array {
        return [
            'requestedLocale' => $requestedLocale,
            'resolvedLocale' => $resolvedLocale,
            'defaultLocale' => self::DEFAULT_LOCALE,
            'fallbackLocale' => self::DEFAULT_LOCALE,
            'fallbackUsed' => $fallbackUsed,
            'missingFields' => $missingFields,
            'fallbackFields' => $fallbackFields,
        ];
    }
}
