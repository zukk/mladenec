<?php
/**
 * Заказ, выполняемый в пункте маршрута
 */
class Model_Astra_Route_Order extends ORM {
    protected $_table_name = 'z_astra_route_order';

    protected $_table_columns = array(
        'id'            => '', // * ID пункта маршрута
        'route_item_id' => '', //   id точки маршрута
        'route_id'      => '', //   id маршрута
        'point_id'      => '', //   id точки
        'start'         => '', //   Время прибытия в точку в миллисекундах UTC Arrival <= Departure
        'finish'        => '', //   Время убытия из точки в миллисекундах UTC Arrival <= Departure
        'comment'       => '', //   Комментарий
        'operation'     => '', //  
        'from_astra_ts' => 0,  //   Timestamp выгрузки в ASTRA
        'to_1c_ts'      => 0   //   timestamp получения из 1с
        );
    
    protected  $_has_one = array(
        'address' => array('model' => 'user_address', 'foreign_key' => 'address_id'),
    );
}
