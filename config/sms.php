<?php

return [
    'enabled'   => env('SMS_ENABLED', false),
    'url'       => env('SMS_GATEWAY_URL'),
    'bulk_url'   => env('SMS_BULK_GATEWAY_URL'),
    'username'  => env('SMS_GATEWAY_USERNAME'),
    'password'  => env('SMS_GATEWAY_PASSWORD'),
    'api_key'   => env('SMS_GATEWAY_API_KEY'),
    'sender_id' => env('SMS_SENDER_ID'),
];

