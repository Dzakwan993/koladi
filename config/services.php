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

    'gemini' => [
        'api_key'     => env('GEMINI_API_KEY'),
        'api_key_2'   => env('GEMINI_API_KEY_2'),
        'api_key_3'   => env('GEMINI_API_KEY_3'),
        'api_key_4'   => env('GEMINI_API_KEY_4'),
        'api_key_5'   => env('GEMINI_API_KEY_5'),
        'api_key_6'   => env('GEMINI_API_KEY_6'),
        'api_key_7'   => env('GEMINI_API_KEY_7'),
        'api_key_8'   => env('GEMINI_API_KEY_8'),
        'api_key_9'   => env('GEMINI_API_KEY_9'),
        'api_key_10'  => env('GEMINI_API_KEY_10'),
        'base_url'    => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'temperature' => (float) env('GEMINI_TEMPERATURE', 0.2),
        'timeout'     => (int) env('GEMINI_TIMEOUT', 120),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'fireflies' => [
        'key' => env('FIREFLIES_API_KEY'),
    ],

];
