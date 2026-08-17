<?php

return [
    'site_name' => env('APP_NAME', 'Laravel'),
    'default_title' => env('SEO_DEFAULT_TITLE', env('APP_NAME', 'Laravel')),
    'default_description' => env('SEO_DEFAULT_DESCRIPTION', 'Website'),
    'twitter_site' => env('SEO_TWITTER_SITE'),
    'default_image' => env('SEO_DEFAULT_IMAGE'), // absolute URL recommended
];

