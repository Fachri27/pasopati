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

        // Pakai "test key" Cloudflare yang selalu lolos di hostname mana pun,
        // menggantikan key di atas. Hanya untuk pengembangan: widget-nya
        // memasang spanduk "Hanya untuk pengujian", dan verifikasinya tidak
        // menyaring bot sama sekali.
        //
        // Harus dinyalakan sengaja lewat TURNSTILE_TEST_KEYS=true. Dulu ini
        // ikut APP_ENV=local, jadi server yang APP_ENV-nya salah setel diam-diam
        // memakai captcha yang selalu lolos.
        'test_keys' => env('TURNSTILE_TEST_KEYS', false),
    ],

    // Integrasi GeoServer untuk pencarian lokasi pada CRUD Event/Kejadian.
    //
    // GeoServerService membaca data lokasi LANGSUNG dari database PostGIS
    // (koneksi `geo`) yang juga menjadi sumber layer WFS di GeoServer — bukan
    // HTTP WFS. Alasannya: data identik, tanpa bergantung pada uptime/URL
    // GeoServer, dan bisa pakai PostGIS (ST_AsGeoJSON) secara native.
    // Kredensial DB hanya di .env (DB_*_GEO), tidak pernah ter-expose ke
    // frontend.
    //
    // Contoh default layer: proteus."POLITICAL_LEVEL_6_dissolved" (level 6 /
    // desa) dengan kolom "NAME", latitude, longtitude, geom (PostGIS, 4326).
    'geoserver' => [
        // Tabel PostGIS yang berisi layer lokasi (GEOSERVER_LOCATION_TABLE).
        // Boleh ber-schema, mis. proteus.POLITICAL_LEVEL_6_dissolved.
        'table' => env('GEOSERVER_LOCATION_TABLE', 'proteus.POLITICAL_LEVEL_6_dissolved'),

        // Kolom yang dipakai sebagai nama tampilan lokasi.
        'name_property' => env('GEOSERVER_NAME_PROPERTY', 'NAME'),

        // Kolom tambahan opsional yang digabung ke nama tampilan.
        'label_property' => env('GEOSERVER_LABEL_PROPERTY'),

        // Kolom yang ikut dicari (dipisah koma di .env), dipakai untuk
        // filter ILIKE '%q%'.
        'search_properties' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('GEOSERVER_SEARCH_PROPERTIES', 'NAME,NAME_EN_US'))
        ))),

        // Kolom latitude/longitude pra-hitung pada tabel layer. Bila kosong,
        // centroid dihitung dari kolom geometri (geometry_column).
        'latitude_column' => env('GEOSERVER_LATITUDE_COLUMN', 'latitude'),
        'longitude_column' => env('GEOSERVER_LONGITUDE_COLUMN', 'longtitude'),

        // Kolom geometri (PostGIS) pada tabel layer.
        'geometry_column' => env('GEOSERVER_GEOMETRY_COLUMN', 'geom'),

        // SRID keluaran geometry (dipakai untuk ST_Transform). Nilai default
        // 4326 (WGS84).
        'geometry_srid' => (int) env('GEOSERVER_GEOMETRY_SRID', 4326),

        // Jumlah maksimal hasil pencarian per request.
        'limit' => (int) env('GEOSERVER_SEARCH_LIMIT', 20),
    ],

    // Konfigurasi thumbnail otomatis dari video (screenshot/frame grab).
    // Dipakai VideoThumbnailService saat event menyertakan file video.
    'video' => [
        // Path biner ffmpeg (bisa "ffmpeg" bila sudah ada di PATH).
        'ffmpeg_path' => env('FFMPEG_PATH', 'ffmpeg'),

        // Detik video yang diambil sebagai thumbnail.
        'thumbnail_seek' => (int) env('VIDEO_THUMBNAIL_SEEK', 1),

        // Timeout proses ffmpeg (detik).
        'timeout' => (int) env('FFMPEG_TIMEOUT', 30),
    ],

    // Konfigurasi integrasi Deforestory antar-web.
    'deforestory_api' => [
        // Bearer token untuk endpoint GET sindikasi yang CMS EXPOSE ke web lain
        // (DeforestoryApiAuth). Dikirim via header Authorization: Bearer atau ?token=.
        'key' => env('DEFORESTORY_API_KEY'),

        // Webhook keluar: saat laporan di-publish, CMS POST payload laporan ke
        // URL web lain agar langsung update tanpa polling. Boleh beberapa URL
        // dipisah koma. Payload ditandatangani pakai HMAC SHA256 (header
        // X-Deforestory-Signature) pakai secret di bawah supaya web lain bisa
        // verifikasi pengirimnya.
        'webhook_url' => env('DEFORESTORY_WEBHOOK_URL'),
        'webhook_secret' => env('DEFORESTORY_WEBHOOK_SECRET'),
        'webhook_timeout' => env('DEFORESTORY_WEBHOOK_TIMEOUT', 10),

        // Sync keluar ke simontini: saat laporan di-publish/unpublish, CMS POST
        // payload laporan ke endpoint simontini (deforestory/sync). Beda dari
        // webhook di atas — simontini pakai Bearer token (bukan HMAC) + shape
        // body sendiri (lihat DeforestorySyncJob). Gak dikonfigurasi → skip.
        'sync_url' => env('DEFORESTORY_SYNC_URL'),
        'sync_token' => env('DEFORESTORY_SYNC_TOKEN'),
    ],

    // Login Google untuk kolom komentar (Laravel Socialite). Commenter login
    // pakai Google supaya nama + avatar otomatis terisi; tamu tetap boleh
    // komentar tanpa login. Credential dari Google Cloud Console (OAuth
    // client). Authorized redirect URI = GOOGLE_REDIRECT_URI.
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'scopes' => ['openid', 'profile', 'email'],
    ],

];
