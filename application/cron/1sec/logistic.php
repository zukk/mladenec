<?php

require('../../../www/preload.php');

$lock_file         = APPPATH.'cache/astra_request_lock';
$request_file      = APPPATH.'cache/astra_request_on';

function l($str) {
    /*
    $log_dir  = APPPATH . 'logs/' . date('Y/m/d') . '/astra/';
    $log_file = $log_dir . date('H_m_s') . '.txt';
    
    if ( ! is_writable($log_file)) {
        if( ! is_writable($log_dir)) {
            mkdir($log_dir,0777,true);
        }
        touch($log_file);
    }
    
    file_put_contents($log_file, date('H:m:s: ') . $str . "\r\n", FILE_APPEND);
     */
}


if ( file_exists($lock_file)) exit('Lock!');

if ( file_exists($request_file)) {
    
    $data = file_get_contents($request_file);
    list($name, $port, $key, $date) = explode('|', $data);
    unlink($request_file);
    
} elseif ( ! file_exists($request_file)) {
    exit('no request');
}

/* uncomment in war
*/

touch($lock_file);
Astra_Client::params($name, $port, $key); 

try {
    
    $generic_client = new Astra_Generic();
    $order_client   = new Astra_Order();
    
    $generic_client->block_client();
    
    echo( "\n" . 'Try to upload resources to Astra...' . "\n");
    Log::instance()->add(Log::INFO, 'Begin upload resources to Astra.');
   
    $resources = ORM::factory('astra_resource')->find_all()->as_array('id');
    $cnt_res = count($resources);
    if ($cnt_res > 0) {
        $resource_client = new Astra_Resource();
        if ($res_res = $resource_client->add_resources($resources,true)) {
            if (0 === $res_res->get_code() AND $cnt_res > 0) {
                Log::instance()->add(Log::INFO, $cnt_g . ' resources uploaded to Astra.');
            }
        }
        //var_dump($resource_client->get_request());
        //var_dump($resource_client->get_response());
    }
    
    // Cancelled orders deletion.
    // echo( "\n" . 'Try to unplan cancelled orders...' . "\n");
    Log::instance()->add(Log::INFO, ' Unplanning cancelled order from Astra.');
    $cancelled_orders = ORM::factory('astra_order')
            ->where('status','=','C')
            ->where('date','=',$date)
            ->find_all()->as_array('id');
    
    $cnt_co = count($cancelled_orders);
    if ($cnt_co > 0) {
        $planning_client = new Astra_Planning();
        foreach ($cancelled_orders as $co) {
            
            $update_data = array();
            // Убираем из маршрута
            if ($pl_res = $planning_client->unplan_order($co->pk(), true) AND 0 === $pl_res->get_code()) {
                $update_data['resource_id'] = 0;
                Log::instance()->add(Log::INFO, ' cancelled order ' . $co->pk() . ' (code: ' . $co->order_code . ') unplanned in Astra.');
            }
            // Удаляем из Астры
            if ($do_res = $order_client->delete_order($co->pk(), true) AND 0 === $do_res->get_code()) {
                $update_data['to_astra_ts'] = 0;
                Log::instance()->add(Log::INFO, $cnt_o . ' cancelled order ' . $co->pk() . ' (code: ' . $co->order_code . ') deleted from Astra.');
            }
            
            if ( ! empty($update_data)) {
                DB::update('z_astra_order')
                        ->set($update_data)
                        ->where('id', '=', $co->pk())
                        ->execute();
            }
        }
        //var_dump($resource_client->get_request());
        //var_dump($resource_client->get_response());
    }
    
    /**
     * Не удалять!!!
    echo( "\n" . 'Try to upload garages to Astra...' . "\n");
        
    }
    */

    Log::instance()->add(Log::INFO, 'Try to upload orders to Astra.');
    $orders = ORM::factory('astra_order')
            ->where('date', '=', $date)
            ->where('status', '=', 'N')
            ->order_by('to_astra_ts','ASC')
            //->limit(10)
            ->find_all()->as_array('id');
    
    $point_ids = array();
    
    foreach($orders as $oid=>$o) {
        $point_ids[$o->delivery_point_id] = $o->delivery_point_id;
        $point_ids[$o->pickup_point_id] = $o->pickup_point_id;
    }
    
    $cnt_o = count($orders);
    Log::instance()->add(Log::INFO, $cnt_o . ' orders to upload to Astra.');
    echo($cnt_o . ' orders to upload to Astra.');
    
    if ($cnt_o > 0) {
        echo( "\n" . 'Try to upload points to Astra...');
        Log::instance()->add(Log::INFO, 'Try to upload points to Astra.');
        
        $points = ORM::factory('astra_point')
                ->where('id', 'IN', $point_ids)
                ->find_all()->as_array('id');
        $cnt_p = count($points);
        if ($cnt_p > 0) {
            $point_client = new Astra_Point();
            $res_p = $point_client->add_points($points,true);
            if (0 === $res_p->get_code()) {
                DB::update('z_astra_point')->set(array('to_astra_ts' => time()))->where('id', 'IN', array_keys($points))->execute();
                Log::instance()->add(Log::INFO, $cnt_p . ' points uploaded to Astra.');
            }
            var_dump($point_client->get_request());
            var_dump($point_client->get_response());
        }
        
        $res_o = $order_client->add_orders($orders,true);
        if (0 === $res_o->get_code()) {
            DB::update('z_astra_order')
                    ->set(array('to_astra_ts' => time()))
                    ->where('id', 'IN', array_keys($orders))
                    ->execute();
            Log::instance()->add(Log::INFO, $cnt_o . ' orders uploaded to Astra.');
        }
        var_dump($order_client->get_request());
        var_dump($order_client->get_response());
    }
    
    $generic_client->release_client();
    
} catch(SoapFault $e) {
    if ( ! empty($generic_client)) {
        $generic_client->release_client();
    }
    
    Log::instance()->add(Log::WARNING, 'Astra SOAP exception: #' . $e->faultcode . ' ' . $e->__toString());
    
    printf("\nError: %s\n",$e->__toString());
}


unlink($lock_file);