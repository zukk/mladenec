<?php
class Model_Order_Data extends ORM {

    protected $_table_name = 'z_order_data';

    protected $_belongs_to = [
        'order' => ['model' => 'order', 'foreign_key' => 'id'],
    ];

    protected $_table_columns = [
        'id' => '', 'last_name' => '', 'second_name' => '', 'name' => '', 'phone' => '',
        'phone2' => '', 'mobile_phone' => '', 'email' => '', 'ship_time' => '', 'ship_time_text' => '', 'ship_date' => '',
        'ship_zone' => '', 'mkad' => '', 'city' => '', 'street' => '', 'house' => '', 'corp' => '',
        'enter' => '', 'domofon' => '', 'floor' => '', 'call' => '', 'no_ring' => 0, 'no_call' => 0, 'lift' => '', 'kv' => '', 'urname' => '',
        'uraddr' => '', 'postaddr' => '', 'rs' => '', 'ks' => '', 'bik' => '', 'bank' => '', 'innkpp' => '', 'ogrn' => '',
        'okpo' => '', 'gendir' => '', 'user_status' => '', 'address' => '', 'latlong' => '',
        'correct_addr' => '', 'comment' => '', 'courier' => '', 'address_id' => '', 'ozon_delivery_id' => '', 'ozon_barcode' => '', 'ozon_status' => '',
        'client_data' => '', 'source' => '',
        'num' => 1,
    ];

    public function rules()
    {
        return [
            'phone' => [
                ['not_empty'],
                ['Txt::phone_clear', [':value']],
            ],
            'phone2' => [
                ['Txt::phone_clear', [':value']],
            ],
            'mobile_phone' => [
                ['Txt::phone_is_mobile', [':value']],
            ],
        ];
    }
}
