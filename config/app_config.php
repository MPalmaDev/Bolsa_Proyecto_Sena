<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Proyectos
    |--------------------------------------------------------------------------
    */
    'project' => [
        'default_duration_months' => 6,
        'closure_buffer_days' => 0,
        'min_duration_days' => 7,
        'max_duration_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad - Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'login' => '5,1',
        'password_reset' => '3,5',
        'registration' => '5,1',
        'postulacion' => '10,1',
        'evidencia' => '5,1',
        'cambio_estado' => '30,1',
        'api_infinite' => '60,1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Sesión
    |--------------------------------------------------------------------------
    */
    'session' => [
        'lifetime_minutes' => 120,
        'regeneration_minutes' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Validación
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'search_max_length' => 100,
        'file_upload_max_size_kb' => 4096,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Contraseña
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 8,
        'max_length' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Planes de Publicación (Monetización)
    |--------------------------------------------------------------------------
    */
    'publicacion' => [
        'destacado' => [
            'precio' => 50000,
            'dias' => 7,
            'label' => 'Destacado',
            'descripcion' => 'Aparece en los primeros lugares de su categoría por 7 días',
        ],
        'patrocinado' => [
            'precio' => 150000,
            'dias' => 7,
            'label' => 'Patrocinado',
            'descripcion' => 'Aparece en primeros lugares, en la página principal y notificaciones a aprendices',
        ],
    ],
];
