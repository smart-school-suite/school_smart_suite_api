<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        // --- Tauri desktop app ---
        'tauri://localhost',
        'https://tauri.localhost',

        // --- React Native (Expo Go / dev builds) ---
        'http://localhost:8081',
        'http://localhost:19000',
        'http://localhost:19006',
        'exp://localhost:8081',

        // --- Your production web/API domain (add yours) ---
        // 'https://app.yourdomain.com',
        // 'https://yourdomain.com',
    ],

    'allowed_origins_patterns' => [
        // Allows any Expo dev-client URL on the local network (e.g. exp://192.168.x.x:8081)
        '#^exp://\d+\.\d+\.\d+\.\d+:\d+$#',

        // Tauri custom protocol variations across platforms
        '#^tauri://[a-zA-Z0-9\-\.]+$#',
    ],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-XSRF-TOKEN',    // Required for Sanctum CSRF cookie flow
        'Origin',
    ],

    'exposed_headers' => [
        'Authorization',   // Lets the client read the token from responses
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    'max_age' => 86400,    // Cache preflight for 24h — reduces OPTIONS requests

    'supports_credentials' => true,

];
