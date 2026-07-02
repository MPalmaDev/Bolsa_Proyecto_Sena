<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modo de operación
    |--------------------------------------------------------------------------
    | 'sandbox' -> simula pagos sin API real (ideal para desarrollo/pruebas)
    | 'production' -> usa la API real de Nequi Conecta
    */
    'mode' => env('NEQUI_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Credenciales Nequi Conecta (AWS Signature v4)
    |--------------------------------------------------------------------------
    | Se obtienen registrándose en https://conecta.nequi.com.co
    */
    'access_key_id' => env('NEQUI_ACCESS_KEY_ID', ''),
    'secret_key' => env('NEQUI_SECRET_KEY', ''),
    'api_key' => env('NEQUI_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Número Nequi del comercio (quien recibe el pago)
    |--------------------------------------------------------------------------
    */
    'phone_number' => env('NEQUI_PHONE_NUMBER', '3000000000'),

    /*
    |--------------------------------------------------------------------------
    | URLs de la API
    |--------------------------------------------------------------------------
    */
    'base_url' => env('NEQUI_BASE_URL', 'https://apiqa.nequi.com.co'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de la notificación push
    |--------------------------------------------------------------------------
    */
    'push' => [
        'endpoint' => '/payments/push/notifications',
        'status_endpoint' => '/payments/push/notifications/{transactionId}',
        'timeout_minutes' => 15,
        'description' => 'Pago plan visibilidad - Bolsa Proyectos SENA',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook (URL que Nequi notificará cuando cambie el estado del pago)
    |--------------------------------------------------------------------------
    */
    'webhook_url' => env('APP_URL', 'http://127.0.0.1:8000') . '/nequi/webhook',

    /*
    |--------------------------------------------------------------------------
    | Sandbox: número de teléfono para pruebas simuladas
    |--------------------------------------------------------------------------
    */
    'sandbox' => [
        'phone' => '3001234567',
        'auto_approve' => true,
        'approve_delay_seconds' => 3,
    ],
];
