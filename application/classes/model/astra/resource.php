<?php
class Model_Astra_Resource extends ORM {
    protected $_table_name = 'z_astra_resource';

    protected $_table_columns = array(
        'id'                => '', // * ID ресурса
        'name'              => '', //   Наименование ресурса
        'start'             => '', //   начало работы (TIME ЧЧ:ММ:СС)
        'finish'            => '', //   конец рабочего дня (TIME ЧЧ:ММ:СС)
        'weight_capacity'   => '', // * Грузоподъемность в кг
        'volume_capacity'   => '', // * Грузовместимость в м3
        'volume_a_capacity' => '', //   Грузовместимость в альтернативных единицах
        'mileage_rate'      => '', //   Тариф на использование руб/км
        'time_rate'         => '', //   Тариф на использование руб/час
        'garage_id'         => '', // * id cклада - не точки, а _склада_
        'demand_marker'     => '', //   Маркер(ы) требования, разделитель ","
        'hired'             => '', //   Признак наемного ресурса
        'to_astra_ts'       => 0,  //   Timestamp выгрузки в ASTRA
        'from_1c_ts'        => 0   //   timestamp получения из 1с
        );
    
    protected  $_has_one = array(
        'garage' => array('model' => 'astra_garage', 'foreign_key' => 'garage_id'),
    );
}