<?php
class Model_Astra_Route_Item extends ORM {
    
    /**
     *
     * @var Model_Astra_Route_Order
     */
    public $r_ordz = array();
    
    protected $_table_name = 'z_astra_route_item';

    protected $_table_columns = array(
        'id'            => '', // * ID пункта маршрута
        'route_id'      => '', //   id маршрута
        'point_id'      => '', //   id точки
        'sec_no'        => '', //   Последовательный номер пункта в МЛ
        'arrival'       => '', //   Время прибытия в точку в миллисекундах UTC Arrival <= Departure
        'departure'     => '', //   Время убытия из точки в миллисекундах UTC Arrival <= Departure
        'weight'        => '', //   Суммарный вес перевозимого груза в точке в кг
        'volume'        => '', //   Суммарный объем груза в точке в м3
        'volume_a'      => '', //   Суммарный объем груза в альтернативных единицах
        'prev_distance' => '', //   Расстояние от предыдущего пункта до текущего. Для первого пункта полагается равным 0.
        'to_astra_ts'   => 0,  //   Timestamp выгрузки в ASTRA
        'to_astra_code' => '', //   Код результата выгрузки в Астра
        'from_1c_ts'    => 0   //   timestamp получения из 1с
        );
    
    protected  $_has_one = array(
        'address' => array('model' => 'user_address', 'foreign_key' => 'address_id'),
    );
}
