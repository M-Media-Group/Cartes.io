<?php

return [

    'google_tag_id' => env('GOOGLE_TAG_ID', null),
    'fb_app_id' => env('FB_APP_ID', null),
    'logo_url' => env('LOGO_URL', '/images/logo.svg'),
    'instagram_username' => env('APP_INSTAGRAM_USERNAME', null),
    'instagram_url' => 'https://instagram.com/' . env('APP_INSTAGRAM_USERNAME', null),
    'facebook_page_username' => env('APP_FACEBOOK_PAGE_USERNAME', null),
    'facebook_page_url' => 'https://fb.me/' . env('APP_FACEBOOK_PAGE_USERNAME', null),
    'messenger_url' => 'https://m.me/' . env('APP_FACEBOOK_PAGE_USERNAME', null),
    'favicon_url' => env('FAVICON_URL', '/images/favicon.ico'),
    'super_admin_id' => env('APP_SUPER_ADMIN_ID', 1),

];
