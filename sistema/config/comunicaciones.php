<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Adaptador de WhatsApp
    |--------------------------------------------------------------------------
    | 'wa_link'   — Genera links wa.me para uso manual (por defecto).
    | 'meta_cloud' — Meta Cloud API (requiere configurar token, phone_id).
    */
    'whatsapp_driver' => env('COM_WHATSAPP_DRIVER', 'wa_link'),

    'meta_cloud' => [
        'token'    => env('META_WA_TOKEN', ''),
        'phone_id' => env('META_WA_PHONE_ID', ''),
        'version'  => env('META_WA_VERSION', 'v19.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Límites de contenido
    |--------------------------------------------------------------------------
    */
    'max_asunto'     => 200,
    'max_contenido'  => 2000,
    'max_push_chars' => 280,

    /*
    |--------------------------------------------------------------------------
    | Rate limit por usuario: máximo de envíos por ventana de tiempo
    |--------------------------------------------------------------------------
    */
    'rate_limit_max'    => 20,
    'rate_limit_decay'  => 60, // segundos
];
