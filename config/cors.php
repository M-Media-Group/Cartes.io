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

    'paths' => [
        'api/*', 'csrf-token', 'login', 'logout', 'register',
        /**
         * @todo verify that exposing only the following paths is safe
         */
        'oauth/personal-access-tokens',
        'password/email',
        'broadcasting/auth',
        // 'password/reset', 'password/reset/{token}', 'password/reset/{token}/{email}'
    ],

    'allowed_methods' => ['*'],

    // Probably need to move the specific urls for the spa to another middleware
    'allowed_origins' => ['localhost', config('app.url'), config('app.spa_url'), '*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
