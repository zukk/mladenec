<?php
class Model_Terminal extends ORM
{
    const TYPE_OZON = 'ozon';
    const TYPE_DPD = 'dpd';
    const TYPE_YA = 'ya';

    protected $_table_name = 'terminal';

    protected $_table_columns = [
        'id' => '',
        'type'  => '',
        'name' => '',
        'code' => '',
        'city_id' => '',
        'address' => '',
        'worktime' => '',
        'latlong' => '',
        //'point' => '',
        'updated' => '',
        'is_active' => '',
        'pay_cards' => ''
    ];

    public static function get_id_by_address($address)
    {
        return ORM::factory('terminal')
            ->where('type', '=', self::TYPE_OZON)
            ->where('address', '=', $address)
            ->limit(1)
            ->find()
            ->id;
    }


}
