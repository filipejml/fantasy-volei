<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'api_sports_volleyball' => [
        'base_url' => env('API_SPORTS_VOLLEYBALL_URL', 'https://v1.volleyball.api-sports.io'),
        'key' => env('API_SPORTS_VOLLEYBALL_KEY'),
        'season' => env('API_SPORTS_VOLLEYBALL_SEASON', now()->year),
        'cache_seconds' => (int) env('API_SPORTS_VOLLEYBALL_CACHE_SECONDS', 900),
        'leagues' => [
            'masculino' => env('API_SPORTS_VNL_MEN_LEAGUE_ID'),
            'feminino' => env('API_SPORTS_VNL_WOMEN_LEAGUE_ID'),
        ],
    ],

];
