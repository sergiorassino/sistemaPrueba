<?php

return [

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'profesores',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'profesores',
        ],
    ],

    'providers' => [
        'profesores' => [
            'driver' => 'profesor',
        ],
    ],

    'passwords' => [
        'profesores' => [
            'provider'  => 'profesores',
            'table'     => 'password_reset_tokens',
            'expire'    => 60,
            'throttle'  => 60,
        ],
    ],

    'password_timeout' => 10800,

];
