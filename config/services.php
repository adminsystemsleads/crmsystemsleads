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
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
],

'culqi' => [
    'public_key' => env('CULQI_PUBLIC_KEY'),
    'secret_key' => env('CULQI_SECRET_KEY'),
    'webhook_secret' => env('CULQI_WEBHOOK_SECRET'),
    // Plan mensual por defecto (en céntimos de PEN). 4990 = S/ 49.90
    'plan_amount_cents' => env('CULQI_PLAN_AMOUNT_CENTS', 4990),
    'plan_currency' => env('CULQI_PLAN_CURRENCY', 'PEN'),
    'plan_name' => env('CULQI_PLAN_NAME', 'QipuCRM Mensual'),
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

];
