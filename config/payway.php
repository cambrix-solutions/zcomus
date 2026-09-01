<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ABA PayWay credentials
    |--------------------------------------------------------------------------
    | From your sandbox signup email at https://developer.payway.com.kh/.
    | Never commit real values — these come from .env only.
    */
    'merchant_id' => env('ABA_PAYWAY_MERCHANT_ID'),
    'api_key' => env('ABA_PAYWAY_API_KEY'),

    /*
    | Base URL — sandbox vs production. Confirmed from ABA's own
    | "Ecommerce Checkout" developer page.
    */
    'base_url' => env('ABA_PAYWAY_BASE_URL', 'https://checkout-sandbox.payway.com.kh'),

    /*
    | Where ABA redirects the customer's browser after they complete
    | payment on ABA's hosted checkout page, and where ABA pushes the
    | server-to-server payment result. Must be whitelisted in your
    | merchant profile if you override the default.
    */
    'return_url' => env('ABA_PAYWAY_RETURN_URL'),
];
