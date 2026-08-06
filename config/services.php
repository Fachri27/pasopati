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

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    // Sumber daftar kartu kasus di halaman /deforestory (index).
    // Saat ini memakai mock lokal; ganti DEFORESTORY_API_URL ke endpoint web lain.
    'deforestory_api' => [
        'url' => env('DEFORESTORY_API_URL', '/api/deforestory-cases'),
        'timeout' => env('DEFORESTORY_API_TIMEOUT', 8),
        'cache_minutes' => env('DEFORESTORY_API_CACHE', 10),
        'key' => env('DEFORESTORY_API_KEY'),

        // Webhook keluar: saat laporan di-publish, CMS POST payload laporan ke
        // URL web lain agar langsung update tanpa polling. Boleh beberapa URL
        // dipisah koma. Payload ditandatangani pakai HMAC SHA256 (header
        // X-Deforestory-Signature) pakai secret di bawah supaya web lain bisa
        // verifikasi pengirimnya.
        'webhook_url' => env('DEFORESTORY_WEBHOOK_URL'),
        'webhook_secret' => env('DEFORESTORY_WEBHOOK_SECRET'),
        'webhook_timeout' => env('DEFORESTORY_WEBHOOK_TIMEOUT', 10),
    ],

];
