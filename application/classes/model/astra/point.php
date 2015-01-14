<?php
class Model_Astra_Point extends ORM {
    protected $_table_name = 'z_astra_point';

    protected $_table_columns = array(
        'id'                => '', // * (int)       ID точки
        'user_address_id'   => '', //   (int)       Привязанный к пользователю адрес в ИМ
        'latitude'          => '', // * (double)    Широта
        'longitude'         => '', // * (double)    Долгота
        'address'           => '', //   (string)    Адрес
        'to_astra_ts'       => 0,  //   (timestamp) Timestamp выгрузки в ASTRA
        'to_astra_code'     => '', //   (int)       Код результата выгрузки в Астра
        'from_1c_ts'        => 0   //   (timestamp) timestamp получения из 1с
        );
    
    protected  $_has_one = array(
        'user_address' => array('model' => 'user_address', 'foreign_key' => 'user_address_id'),
    );
}
