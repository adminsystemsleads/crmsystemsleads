<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook entrante de Bitrix24
    |--------------------------------------------------------------------------
    | Configura BITRIX24_WEBHOOK_URL en .env con la URL completa hasta el "/",
    | por ejemplo: https://b24-vbmdr4.bitrix24.es/rest/4/mzwptag1bjwcxyf5/
    | NUNCA commitear el token del webhook al repositorio.
    */
    'webhook_url' => env('BITRIX24_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Embudo (CATEGORY_ID) y etapa donde caen las negociaciones nuevas
    |--------------------------------------------------------------------------
    */
    'category_id' => (int) env('BITRIX24_CATEGORY_ID', 8),
    'stage_id'    => env('BITRIX24_STAGE_ID', 'C8:NEW'),

    /*
    |--------------------------------------------------------------------------
    | SOURCE_ID compartido para contacto y negociación
    |--------------------------------------------------------------------------
    */
    'source_id' => env('BITRIX24_SOURCE_ID', 'UC_VCNIKK'),

    /*
    |--------------------------------------------------------------------------
    | Usuario de Bitrix24 al que se asigna la negociación (ASSIGNED_BY_ID)
    |--------------------------------------------------------------------------
    */
    'assigned_user_id' => (int) env('BITRIX24_ASSIGNED_USER_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Campos personalizados de País
    |--------------------------------------------------------------------------
    */
    'contact_country_field' => 'UF_CRM_1680643399679',
    'deal_country_field'    => 'UF_CRM_1681584701359',

    /*
    |--------------------------------------------------------------------------
    | Mapeo de país para NEGOCIACIÓN — prefijo telefónico → ID de opción
    |--------------------------------------------------------------------------
    | IMPORTANTE: los IDs son DISTINTOS entre deal y contact en este portal.
    | Si necesitas agregar más países, ejecuta:
    |   php artisan bitrix24:list-country-options deal
    */
    'deal_country_options' => [
        '+51'  => 198,  // Perú
        '+57'  => 200,  // Colombia
        '+593' => 202,  // Ecuador
        '+56'  => 340,  // Chile
        '+54'  => 308,  // Argentina
        '+52'  => 310,  // México
        '+591' => 930,  // Bolivia
        '+34'  => 986,  // España
        '+1'   => 872,  // Estados Unidos (también Canadá, RD, PR — todos comparten +1)
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo de país para CONTACTO — prefijo telefónico → ID de opción
    |--------------------------------------------------------------------------
    | Para agregar más países: php artisan bitrix24:list-country-options contact
    */
    'contact_country_options' => [
        '+51'  => 176,  // Perú
        '+57'  => 178,  // Colombia
        '+593' => 180,  // Ecuador
        '+56'  => 432,  // Chile
        '+54'  => 182,  // Argentina
        '+52'  => 430,  // México
        '+591' => 562,  // Bolivia
        '+34'  => 618,  // España
        '+1'   => 440,  // Estados Unidos
    ],
];
