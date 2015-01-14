<?php
class Model_Astra_Garage extends ORM {
    protected $_table_name = 'z_astra_garage';

    protected $_table_columns = array(
        'id'            => '', // * ID склада
        'name'          => '', // * Название склада
        'point_id'      => '', // * id точки склада
        'to_astra_ts'   => 0,  //   Timestamp выгрузки в ASTRA
        'to_astra_code' => '', //   Код результата выгрузки в Астра
        'from_1c_ts'    => 0   //   timestamp получения из 1с
        );
    
    protected  $_has_one = array(
        'point' => array('model' => 'astra_point', 'foreign_key' => 'point_id'),
    );
}