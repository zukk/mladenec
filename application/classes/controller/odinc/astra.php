<?php
/**
 * Description of astra
 *
 * @author pks
 */
class Controller_Odinc_Astra extends Controller_Odinc {
    
    public function before() {
        $this->log_dir_suffix = '/astra';
        parent::before();
    }
    
    /**
     * Прием точек для логистики из 1с
     */
    public function action_points_import() {
        $strings = explode("\n", $this->body);
        $n = 1;
        foreach($strings as $s) {
            $s = trim($s);
            
            if (empty($s)) continue;

            list(
                    $id,         // 1
                    $address_id, // 2
                    $latitude,   // 3
                    $longitude,  // 4 
                    $address     // 5 вес
                    ) = $this->parse('@', $s, 5, array(0,2,3));

            $astra_point = new Model_Astra_Point($id);
            if ( ! $astra_point->loaded()) {
                $astra_point->id = $id;
            }
            
            $astra_point->user_address_id   = $address_id;
            $astra_point->latitude          = $latitude;
            $astra_point->longitude         = $longitude;
            $astra_point->address           = $address;
            $astra_point->from_1c_ts        = time();
            
            try {
                $astra_point->save();
            } catch (ORM_Validation_Exception $e) {
                $this->error('STRING ' . $n . ': Cannot save astra_point ' . $e->getMessage());
            }
            $n ++;
        }
        if (empty($this->errors)) $this->view->ok = TRUE;
        else $this->view->ok = FALSE;
    }
    
    public function action_orders_check() {
        $date   = $this->body;
        if (empty($date)) $date = date('Y-m-d');
        $this->view->orders = ORM::factory('astra_order')->where('date','=',$date)->find_all()->as_array('id');
    }
    
    /** 
     * Прием заказов для логистики из 1с
     */
    public function action_orders_import() {
        $strings = explode("\n", $this->body);
        $n = 1;
        
        $astra_order = FALSE;
        $goods_arr = array();
        
        foreach($strings as $s) {
            $s = trim($s);
            if (empty($s)) continue;
            
            if (mb_strpos($s, 'ЗАКАЗ:') === 0) { // Новый заказ
            
                if ( ! ($list = $this->parse('@', trim(mb_substr($s, 6)), 4, array(1)))) continue;
                list( 
                    $tracking_id,  //   1
                    $order_code,   // * 2
                    $check_number, // * 3
                    $order_id,     //   4
                        ) = $list;
            }
            
            if (mb_strpos($s, 'СТАТУС:') === 0) {
                
                $order_status = trim(mb_substr($s, 7));
                switch($order_status) {
                    case 'N' :
                        $new_order = true;
                        $status = 'N';
                        break;
                    case 'C':
                        $status = 'C';
                        break;
                    case 'F':
                        $status = 'F';
                        break;
                    default:
                       Log::instance()->add(Log::INFO, 
                               'Astra orders import: wrong status for order, code: ' . $order_code 
                               . ' check_number: ' . $check_number 
                               . ', id: ' . $order_id
                               ); 
                }
                
            } 
            
            if( ! empty($new_order)) {
                if(mb_strpos($s, 'ТИП:') === 0) { 

                    list($kind_id, $display_type_name) = $this->parse('@', trim(mb_substr($s, 4)), 2, array(0));

                } elseif(mb_strpos($s, 'ДАТА:') === 0) {

                    list($date_day,   $date_month,   $date_year)   = sscanf(str_replace(' ', '', trim(mb_substr($s, 5))),   '%d-%d-%d');
                    $date              = '20' . $date_year . '-' . $date_month . '-' . $date_day;

                } elseif(mb_strpos($s, 'ВЕС:')         === 0) { $weight        = floatval(trim(mb_substr($s, 4)));
                } elseif(mb_strpos($s, 'ОБЪЕМ:')       === 0) { $volume        = floatval(trim(mb_substr($s, 6)));
                } elseif(mb_strpos($s, 'АОБЪЕМ:')      === 0) { $volume_a      = floatval(trim(mb_substr($s, 7)));
                } elseif(mb_strpos($s, 'МАРКЕР:')      === 0) { $demand_marker = trim(mb_substr($s, 7));
                } elseif(mb_strpos($s, 'КОММЕНТАРИЙ:') === 0) { $comment       = trim(mb_substr($s, 12));
                } elseif(mb_strpos($s, 'ДОСТАВКА:')    === 0) {

                    list($delivery_point_id, $delivery_time, $delivery_duration) = $this->parse('@', trim(mb_substr($s, 9)), 3, array(0));

                } elseif(mb_strpos($s, 'ПОГРУЗКА:') === 0) {

                    list($pickup_point_id, $pickip_time, $pickup_duration ) = $this->parse('@', trim(mb_substr($s, 9)), 3, array(0));

                } elseif(mb_strpos($s, 'КЛИЕНТ:') === 0) {

                    list(
                        $name,         // 1
                        $contacts ,    // 2
                        $full_address, // 3
                        $sum,          // 4
                        $payment,      // 5
                        $short_change, // 6
                        $water,        // 7
                        $milk,         // 8
                        $big,          // 9
                        $floor_pickup, // 10
                            ) = $this->parse('@', trim(mb_substr($s, 7)), 10);

                } elseif(mb_strpos($s, 'ТОВАР:') === 0) {

                    list($good_code, $good_qty) = $this->parse('@', trim(mb_substr($s, 6)), 2, array(0));
                    $goods_arr[$good_code] = $good_qty;
                }
            }
            if('КОНЕЦ ЗАКАЗА' == $s) {

                if ( ! empty($tracking_id)) {
                    
                    $astra_order = new Model_Astra_Order($tracking_id);
                    
                    if ( ! $astra_order->loaded()) {
                        $astra_order->id = $tracking_id;
                    }
                    
                } else {
                    $astra_order = ORM::factory('astra_order')
                            ->where('order_code', '=', $order_code)
                            ->where('date',       '=', $date)
                            ->find();
                }
                
                if ( ! ($astra_order instanceof Model_Astra_Order)) continue;
                
                $astra_order->order_code   = $order_code;
                $astra_order->check_number = $check_number;
                $astra_order->order_id     = $order_id;
                $astra_order->status       = $status;
                $astra_order->kind_id      = $kind_id;
                
                if (empty($display_type_name)) {
                    switch($astra_order->kind_id) {
                        case 1: 
                            $astra_order->display_type_name = 'PICKUP';
                            break;
                        case 2: 
                            $astra_order->display_type_name = 'DELIVERY';
                            break;
                        case 3: 
                            $astra_order->display_type_name = 'P&D';
                            break;
                    }
                } else {
                     $astra_order->display_type_name = $display_type_name;
                }
                
                $astra_order->weight        = $weight;
                $astra_order->date          = $date;
                $astra_order->volume        = $volume;
                $astra_order->volume_a      = $volume_a;
                $astra_order->demand_marker = $demand_marker;
                $astra_order->comment       = $comment;
                
                // Погрузка
                list($pickup_from_h,   $pickup_from_m,   $pickup_to_h,   $pickup_to_m)   = sscanf(str_replace(' ', '', $pickip_time),   '%d:%d-%d:%d');
                
                $pickup_to_h = empty($pickup_to_h)?23:$pickup_to_h;
                $pickup_to_m = empty($pickup_to_m)?59:$pickup_to_m;
                
                $astra_order->pickup_from = $pickup_from_h . ':' . $pickup_from_m . ':00';
                $astra_order->pickup_to   = $pickup_to_h   . ':' . $pickup_to_m   . ':59';
                
                $pickup_duration = empty($pickup_duration)?30:$pickup_duration;
                
                $astra_order->pickup_point_id = $pickup_point_id;
                $astra_order->pickup_duration = $pickup_duration;
                
                // Доставка
                $astra_order->delivery_point_id = $delivery_point_id;
                
                $astra_order->delivery_duration = empty($delivery_duration)?12:$delivery_duration;
                list($delivery_from_h, $delivery_from_m, $delivery_to_h, $delivery_to_m) = sscanf(str_replace(' ', '', $delivery_time), '%d:%d-%d:%d');
                
                $astra_order->delivery_from = $delivery_from_h . ':' . $delivery_from_m . ':00';
                $astra_order->delivery_to   = $delivery_to_h   . ':' . $delivery_to_m   . ':00';
                
                // КЛИЕНТ
                $astra_order->name         = $name;
                $astra_order->contacts     = $contacts;
                $astra_order->full_address = $full_address;
                $astra_order->sum          = $sum;
                $astra_order->payment      = $payment;
                $astra_order->short_change = $short_change;
                
                $astra_order->water        = ('Y' == $water)        ? 1 : 0;
                $astra_order->milk         = ('Y' == $milk)         ? 1 : 0;
                $astra_order->big          = ('Y' == $big)          ? 1 : 0;
                $astra_order->floor_pickup = ('Y' == $floor_pickup) ? 1 : 0;
                
                
                if ( ! empty($goods_arr) AND is_array($goods_arr)) {
                    $goods = ORM::factory('good')->where('code','IN',array_keys($goods_arr))->find_all()->as_array('code');
                }
                
                $astra_order->goods = '';
                
                foreach($goods_arr as $good_code => $good_qty) {
                    
                    if (empty($good_qty)) continue;
                    
                    if ( ! empty($goods[$good_code]) AND ($goods[$good_code] instanceof Model_Good)) {
                        $astra_order->goods .= $goods[$good_code]->group_name . ' ' . $goods[$good_code]->name . ' ' . $good_qty . ' шт. |';
                    } else {
                        $astra_order->goods .= '#' . $good_code . ' ' . $good_qty . '|';
                    }
                }
                
                $goods_arr = array();
                $astra_order->from_1c_ts        = time();

                try {
                    $astra_order->save();
                    Log::instance()->add(Log::INFO, 'Save order, 1C code:' . $order_code . ', site id: ' . $order_id . ', date: ' . $date);
                    
                    if ('N' == $status) {
                        $old_orders = ORM::factory('astra_order')
                                ->where('id',         '!=', $astra_order->pk())
                                ->where('order_code', '=',  $astra_order->order_code)
                                ->find_all()->as_array('id');
                        
                        foreach($old_orders as $oo) {
                            $oo->status = 'C';
                            $oo->save();
                        }
                        
                    }
                    
                    
                } catch (ORM_Validation_Exception $e) {
                    $this->error('STRING ' . $n . ': Cannot save astra_order ' . $e->getMessage());
                }
                $astra_order = FALSE;
            }
        }
        
        if (empty($this->errors)) $this->view->ok = TRUE;
        else $this->view->ok = FALSE;
    }
    
    public function action_routes_export() {

        $date = $this->request->query('date');
        $routes = ORM::factory('astra_route')->where('date','=',$date)->find_all()->as_array('id');
        if ( ! empty($routes)) {
            $orders = ORM::factory('astra_order')
                    ->where('route_id','IN',array_keys($routes))
                    ->order_by('route_number','ASC')
                    ->find_all()->as_array('id');
        } else {
            $orders = array();
        }
        
        $route_orders = array();
        
        foreach($orders as $o) {
            $route_orders[$o->route_id][$o->id] = $o->id;
        }
        
        $this->view->routes         = $routes;
        $this->view->orders         = $orders;
        $this->view->route_orders   = $route_orders;
    }
    
    /** 
     * Прием складов для логистики из 1с
     */
    public function action_garages_import() {
        $strings = explode("\n", $this->body);
        $n = 1;
        foreach($strings as $s) {
            $s = trim($s);
            if (empty($s)) continue;
            
            if ( ! ($list = $this->parse('@', $s, 3))) continue;
            
            list(
                    $id,        // 1 ID склада
                    $name,      // 2 Название склада
                    $point_id,  // 3 id точки склада
                    ) = $list;
            
            $astra_garage = new Model_Astra_Garage($id);
            if ( ! $astra_garage->loaded()) {
                $astra_garage->id = $id;
            }
            
            $astra_garage->name         = $name;
            $astra_garage->point_id     = $point_id;
            $astra_garage->from_1c_ts   = time();
            
            try {
                $astra_garage->save();
            } catch (ORM_Validation_Exception $e) {
                $this->error('STRING ' . $n . ': Cannot save astra_garage '.$e->getMessage());
            }
        }
        if (empty($this->errors)) $this->view->ok = TRUE;
        else $this->view->ok = FALSE;
    }
    
    /** 
     * Прием ресурсов для логистики из 1с
     */
    public function action_resources_import() {
        $strings = explode("\n", $this->body);
        $n = 1;
        foreach($strings as $s) {
            $s = trim($s);
            
            if (empty($s)) continue;
            
            if ( ! ($list = $this->parse('@', $s, 11,array(0,1,3,4,8)))) continue;
            
            list(
                    $id,                // 1
                    $name,              // 2
                    $time,              // 3
                    $weight_capacity,   // 4
                    $volume_capacity,   // 5
                    $volume_a_capacity, // 6
                    $mileage_rate,      // 7
                    $time_rate,         // 8
                    $garage_id,         // 9
                    $demand_marker,     // 10
                    $hired,             // 11
                    ) = $list;
            
            
            $astra_resource = new Model_Astra_Resource($id);
            if ( ! $astra_resource->loaded()) {
                $astra_resource->id = $id;
            }
            
            list($start_h, $start_m, $finish_h, $finish_m) = sscanf(str_replace(' ', '', $time), '%d:%d-%d:%d');
            
            $finish_h = empty($finish_h) ? 23 : $finish_h;
            $finish_m = empty($finish_m) ? 59 : $finish_m;
            
            $astra_resource->name               = $name;
            $astra_resource->start              = $start_h  . ':' . $start_m  . ':00';
            $astra_resource->finish             = $finish_h . ':' . $finish_m . ':59';
            $astra_resource->weight_capacity    = intval($weight_capacity);
            $astra_resource->volume_capacity    = $volume_capacity;
            $astra_resource->volume_a_capacity  = $volume_a_capacity;
            $astra_resource->mileage_rate       = $mileage_rate;
            $astra_resource->time_rate          = $time_rate;
            $astra_resource->garage_id          = $garage_id;
            $astra_resource->demand_marker      = $demand_marker;
            $astra_resource->hired              = $hired;
            $astra_resource->from_1c_ts         = time();
            $astra_resource->to_astra_ts        = time();
            
            try {
                $astra_resource->save();
            } catch (ORM_Validation_Exception $e) {
                $this->error('STRING ' . $n . ': Cannot save astra_resource ' . $e->getMessage());
            }
        }
        if (empty($this->errors)) $this->view->ok = TRUE;
        else $this->view->ok = FALSE;
    }
    
    protected function get_view() {
        return View::factory('smarty:odinc/astra/'.$this->request->action());
    }
}
