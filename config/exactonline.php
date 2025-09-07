<?php

return [
    /**
     * The Client ID of your Exact Online application.
     */
    'exact_client_id' => env('EXACT_CLIENT_ID'),

    /**
     * The Client Secret of your Exact Online application.
     */
    'exact_client_secret' => env('EXACT_CLIENT_SECRET'),

    /**
     * The Division of your Exact Online application.
     */
    'exact_division' => env('EXACT_DIVISION'),

    /**
     * The Client country code
     */
    'exact_country_code' => 'nl',

    /**
     * Callback url for exact online
     */
    'callback_url' => 'https://app.castimize.com/exact/oauth',

    /**
     * Webhook secret
     */
    'webhook_secret' => env('EXACT_WEBHOOK_SECRET'),
];
