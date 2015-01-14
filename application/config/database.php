<?php

return array
(
    'default' => array
    (
        'type'       => 'mysql',
        'connection' => array(
            /**
             * The following options are available for MySQL:
             *
             * string   hostname     server hostname, or socket
             * string   database     database name
             * string   username     database username
             * string   password     database password
             * boolean  persistent   use persistent connections?
             * array    variables    system variables as "key => value" pairs
             *
             * Ports and sockets may be appended to the hostname.
             */
            'hostname'   => 'localhost',
            'database'   => defined('UNITTEST') ? 'zukkut' : 'zukk', // mlad
            'username'   => defined('UNITTEST') ? 'zukkut' : 'zukk',
            'password'   => defined('UNITTEST') ? '666333' : 'OKJnf42hh',
            'persistent' =>  true,
        ),
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
    ),

    'sphinx' => array
    (
        'type'       => 'mysql',
        'connection' => array(
            /**
             * The following options are available for MySQL:
             *
             * string   hostname     server hostname, or socket
             * string   database     database name
             * string   username     database username
             * string   password     database password
             * boolean  persistent   use persistent connections?
             * array    variables    system variables as "key => value" pairs
             *
             * Ports and sockets may be appended to the hostname.
             */
            'hostname'   => '127.0.0.1:9306',
            'username'   => 'dbuser',
            'password'   => 'mypassword',
            'persistent' => FALSE,
            'database'   => 'sphinx',
        ),
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
		'log' => '/home/zukk/sphinx/query.log'
    ),

);
