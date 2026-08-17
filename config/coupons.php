<?php

return [
    'default_valid_days' => (int) env('COUPON_DEFAULT_VALID_DAYS', 15),
    'telegram_secret' => env('COUPON_TELEGRAM_SECRET'),
    'telegram_bot_username' => env('COUPON_TELEGRAM_BOT_USERNAME'),
];
