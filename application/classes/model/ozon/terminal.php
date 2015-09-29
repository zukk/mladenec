<?php
class Model_Ozon_Terminal extends ORM
{
    protected $_table_name = 'ozon_terminal';

    protected $_table_columns = [
        'id' => '', 
        'address' => '',
        'lat' => '',
        'lng' => '',
        'updated' => '',
        'is_active' => '',
        'pay_cards' => ''
    ];
    
    public static function get_id_by_address($address)
    {
        return ORM::factory('ozon_terminal')
            ->where('address', '=', $address)
            ->limit(1)
            ->find()
            ->id;
    }
}
