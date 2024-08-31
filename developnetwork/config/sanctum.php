<?php

use Laravel\Sanctum\Sanctum;


return [

    'stateful' => [
        'localhost',
        '127.0.0.1',
        '::1',
    ],

    'guard' => 'web',

    'prefix' => 'api',

];
