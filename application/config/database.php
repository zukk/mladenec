<?php

/**
 * Настройки БД
 */

return [
    'default' => [
        'type' => 'mysql',
        'connection' => [
            'hostname'   => 'localhost',
            'database'   => 'db',
            'username'   => 'user',
            'password'   => 'passwd',
            'persistent' =>  TRUE,
        ],
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
    ],

    'sphinx' => [
        'type'       => 'mysql',
        'connection' => [
            'hostname'   => '127.0.0.1:9306',
            'username'   => 'dbuser',
            'password'   => 'mypassword',
            'persistent' => FALSE,
            'database'   => 'sphinx',
        ],
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
		'log' => '',
    ],
];
