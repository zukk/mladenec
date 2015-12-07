<?php

return [
    'memcache' => [
        //'driver'             => 'memcache',
        'driver'             => 'file',
        'default_expire'     => 3600,
        'compression'        => FALSE,
        'servers'            => [
            'local' => [
                'host'          => 'localhost',
                'port'          => 11211,
                'persistent'    => FALSE,
            ],
        ],
    ],
];