<?php

return [

    'google_tag_id' => env('GOOGLE_TAG_ID', null),
    'fb_app_id' => env('FB_APP_ID', null),
    'logo_url' => env('APP_LOGO_URL', '/images/logo.svg'),
    'instagram_username' => env('APP_INSTAGRAM_USERNAME', null),
    'instagram_url' => 'https://instagram.com/' . env('APP_INSTAGRAM_USERNAME', null),

];
