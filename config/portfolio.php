<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portfolio URLs
    |--------------------------------------------------------------------------
    |
    | Central URLs used by the CMS and frontend. Keep direct env() reads inside
    | config files so production config caching remains reliable.
    |
    */

    'urls' => [
        'cms' => env('CMS_URL', env('APP_URL', 'http://localhost')),
        'frontend' => env('FRONTEND_URL'),
    ],

    'storage' => [
        'cdn_url' => env('CDN_URL'),
        'asset_url' => env('ASSET_URL'),

        // Private media must stay on a private disk and must not be stored on
        // public disk/CDN paths. Public API resources will not emit private
        // media URLs, but storage placement must enforce the same boundary.
        'private_media_disk' => env('PRIVATE_MEDIA_DISK', 'local'),
    ],

    'media' => [
        'public_metadata_keys' => ['blurhash'],
        'public_variant_keys' => [
            'src',
            'url',
            'path',
            'width',
            'height',
            'mimeType',
            'sizeBytes',
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'project' => env('OPENAI_PROJECT'),
        'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 30),
    ],

    'cloudflare' => [
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
        'zone_id' => env('CLOUDFLARE_ZONE_ID'),
        'api_token' => env('CLOUDFLARE_API_TOKEN'),

        'r2' => [
            'bucket' => env('CLOUDFLARE_R2_BUCKET'),
            'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
            'public_url' => env('CLOUDFLARE_R2_PUBLIC_URL'),
        ],
    ],

];
