<?php
return array(
    'memcache' => array
    (
        'driver'             => 'memcache',
        'default_expire'     => 3600,
        'compression'        => FALSE,
        'servers'            => array
        (
            'local' => array
            (
                'host'          => 'localhost',
                'port'          => 11211,
                'persistent'    => FALSE,
            ),
        ),
    ),
);