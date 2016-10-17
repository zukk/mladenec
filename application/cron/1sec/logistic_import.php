<?php

require('../../../www/preload.php');

$lock_file         = APPPATH.'cache/astra_import_lock';
$import_file      = APPPATH.'cache/astra_routes_ready';

if ( file_exists($lock_file)) exit('Lock!');

if ( file_exists($import_file)) {
    
    $data = file_get_contents($import_file);
    list($name, $port, $key, $date) = explode('|', $data);
    unlink($import_file);
    
} elseif ( ! file_exists($import_file)) {
    exit();
}

touch($lock_file);

Astra_Client::params($name, $port, $key);

try {
    list($year,$month,$day) = explode('-',$date);
    
    $route_client = new Astra_Route();
    /* var_dump($route_client->get_types()); */
    $routes = $route_client->get_route_list($year, $month, $day);
    
    // var_dump($route_client->get_request());
    // var_dump($route_client->get_response());
    
    //var_dump($routes);
    
    /* Всегда удаляем текущий день
     */
    DB::delete('z_astra_route')
                ->where('date', '=', $year . '-' . $month . '-' . $day)
                ->execute();
    //echo(count($routes));
    if ( ! empty($routes)) {
        foreach($routes as $route) { // $route IS NOT ORM!!!
            echo("Saving route....\n");
            $route->save();
        }
    }
    
} catch(SoapFault $e) {
    
    Log::instance()->add(Log::WARNING, 'Astra SOAP exception: #' . $e->faultcode . ' ' . $e->__toString());
    
    echo $e->faultcode;
    $code = $e->faultcode;
    
    printf("\nError: %s\n",$e->__toString());
}


unlink($lock_file);