<?php

require('../../../www/preload.php');

$name   = 'astrapro';
$port   = '9090';
$key    = '81edd896-5312-4db9-9943-318b85715ca5';
$domain = '213.247.143.81';
$date   = date('Y-m-d');

Astra_Client::params($name, $port, $key, $domain); 

try {
    
    $generic_client = new Astra_Generic();
    //$order_client   = new Astra_Order();
    
    $generic_client->block_client();
    Log::instance()->add(Log::INFO, 'Astra test lock.');
    
    sleep(1);
    
    $generic_client->release_client();
    
} catch(SoapFault $e) {
    if ( ! empty($generic_client)) {
        $generic_client->release_client();
    }
    
    Log::instance()->add(Log::WARNING, 'Astra SOAP exception: #' . $e->faultcode . ' ' . $e->__toString());
    
    printf("\nError: %s\n",$e->__toString());
}

