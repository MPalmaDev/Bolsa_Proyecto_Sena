<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modo de operación
    |--------------------------------------------------------------------------
    | 'sandbox' -> entorno de pruebas Wompi (llaves pub_test_ / prv_test_)
    | 'production' -> producción real
    */
    'mode' => env('WOMPI_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Llaves Wompi (obtenidas en https://comercios.wompi.co)
    |--------------------------------------------------------------------------
    */
    'public_key' => env('WOMPI_PUBLIC_KEY', ''),
    'private_key' => env('WOMPI_PRIVATE_KEY', ''),
    'integrity_secret' => env('WOMPI_INTEGRITY_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | URLs base
    |--------------------------------------------------------------------------
    */
    'urls' => [
        'sandbox' => 'https://sandbox.wompi.co',
        'production' => 'https://production.wompi.co',
    ],

    'api' => [
        'sandbox' => 'https://sandbox.wompi.co/v1',
        'production' => 'https://production.wompi.co/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs de redirección
    |--------------------------------------------------------------------------
    */
    'return_url' => env('APP_URL', 'http://127.0.0.1:8000') . '/empresa/proyectos/wompi/respuesta',
    'webhook_url' => env('APP_URL', 'http://127.0.0.1:8000') . '/wompi/webhook',

    /*
    |--------------------------------------------------------------------------
    | Checkout URL
    |--------------------------------------------------------------------------
    */
    'checkout_url' => 'https://checkout.wompi.co/p/',
];
