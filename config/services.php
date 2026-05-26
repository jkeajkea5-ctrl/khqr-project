<?php

return [
    'bakong' => [
        'token' => env('BAKONG_TOKEN'),
        'api_url' => env('BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh'),
        'verify_url' => env('BAKONG_VERIFY_URL'),
        'verify_secret' => env('BAKONG_VERIFY_SECRET'),
        'account_id' => env('BAKONG_ACCOUNT_ID'),
        'merchant_name' => env('BAKONG_MERCHANT_NAME'),
        'merchant_city' => env('BAKONG_MERCHANT_CITY', 'Phnom Penh'),
        'currency' => env('BAKONG_CURRENCY', 'USD'),
        'store_label' => env('BAKONG_STORE_LABEL', 'KHQR Shop'),
        'terminal_label' => env('BAKONG_TERMINAL_LABEL', 'WEB1'),
        'purpose' => env('BAKONG_PURPOSE', 'Order payment'),
        'expiry_seconds' => (int) env('BAKONG_QR_EXPIRY_SECONDS', 900),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => trim((string) env('TELEGRAM_CHAT_ID', ''), " \t\n\r\0\x0B;"),
    ],

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

];
