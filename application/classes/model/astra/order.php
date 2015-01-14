<?php
class Model_Astra_Order extends ORM {
    protected $_table_name = 'z_astra_order';

    protected $_table_columns = array(
        'id'                => '', // * (int)       ID заказа != номеру
        'order_code'        => '', //   (int)    номер заказа в 1c (число)
        'check_number'      => '', //   (string)    номер чека в 1c (string) - то, что видит логист
        'order_id'          => '', //   (int)       номер заказа в ИМ (число)
        'status'            => '', //   (enum)      статус заказа - [N|C|F] 
        'kind_id'           => '', // * (int)       тип заказа - [1|2|3] - доставка, получение, получение + доставка
        'display_type_name' => '', //   (string)    Текстовое имя типа заказа
        'date'              => '', // * (date)      дата погрузки/доставки
        'pickup_point_id'   => '', // * (int)       Точка получения, ID существующей точки
        'pickup_from'       => '', //   (time)      временное окно грузоотправителя - начало
        'pickup_to'         => '', //   (time)      временное окно грузоотправителя - конец
        'pickup_duration'   => '', //   (int)       длительность погрузки в минутах
        'delivery_point_id' => '', // * (int)       точка доставки (ID существующей точки)
        'delivery_from'     => '', //   (time)      временное окно грузополучателя - начало
        'delivery_to'       => '', //   (time)      временное окно грузополучателя - конец
        'delivery_duration' => '', //   (int)       длительность разгрузки в минутах
        'weight'            => '', //   (double)    вес в кг
        'volume'            => '', //   (double)    объем в м3
        'volume_a'          => '', //   (double)    VolumeA - альтернативный объём, напр. в попугаях (палеты, контейнеры, клети)
        'comment'           => '', //   (string)    Комментарий
        'demand_marker'     => '', //   (string)    DemandMarker метки - разделитель ","
        'name'              => '', // * Имя клиента 
        'contacts'          => '',
        'full_address'      => '',
        'sum'               => '', // * Общая сумма заказа
        'payment'           => '', //   Данные о способе оплаты
        'short_change'      => '', //   С какой купюры сдача
        'water'             => 0,
        'milk'              => 0,
        'big'               => 0,
        'floor_pickup'      => 0,  //   Подъем на этаж
        'goods'             => '', //   (string)    Товары через |
        'to_astra_ts'       => 0,  //   (timestamp) Дата/время выгрузки в ASTRA
        'to_astra_code'     => '', //   (int)       Код результата выгрузки в Астра
        'from_1c_ts'        => 0,  //   (timestamp) Дата/время получения из 1с
        'route_id'          => 0,  //   ID маршрута
        'resource_id'       => 0,  //   ID курьера
        'arrival'           => '', //   Время прибытия курьера
        'departure'         => '', //   Время убытия курьера
        'route_number'      => ''  //   Порядковый номер в маршруте
        );
    
    protected  $_has_one = array(
        'order'          => array('model' => 'order',       'foreign_key' => 'order_id'),
        'delivery_point' => array('model' => 'astra_point', 'foreign_key' => 'delivery_point_id'),
        'pickup_point'   => array('model' => 'astra_point', 'foreign_key' => 'pickup_point_id')
    );
    
    /**
     * 
     * @param ORM $q
     */
    public function admin_list()
    {
        $return = array();
        
        $id = Request::current()->query('id');
        if ( ! empty($id)) $this->where('id','=', $id);
        
        $order_code = Request::current()->query('order_code');  
        if ( ! empty($order_code)) $this->where('order_code','=', $order_code);
        
        $check_number = Request::current()->query('check_number');  
        if ( ! empty($check_number)) $this->where('check_number','=', $check_number);
        
        $order_id = Request::current()->query('order_id');  
        if ( ! empty($order_id)) $this->where('order_id','=', $order_id);
        
        $status = Request::current()->query('status');  
        if ( ! empty($status)) $this->where('status','=', $status);
        
        $date = Request::current()->query('date');
        
        $year   = empty($date['Date_Year']) ?   date('Y')   : $date['Date_Year'];
        $month  = empty($date['Date_Month']) ?  date('m')   : $date['Date_Month'];
        $day    = empty($date['Date_Day']) ?    FALSE       : $date['Date_Day'];
            
        if($day) {
            $this->where('date','=', $year . '-' . $month . '-' . $day);
            $return['date'] = $day . '-' . $month . '-' .$year;
        }
        
        return $return;
    }
}
