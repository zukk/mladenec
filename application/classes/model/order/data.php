<?php
class Model_Order_Data extends ORM {

    protected $_table_name = 'z_order_data';

    protected $_belongs_to = array(
        'order' => array('model' => 'order', 'foreign_key' => 'id'),
    );

    protected $_table_columns = array(
        'id' => '', 'last_name' => '', 'second_name' => '', 'name' => '', 'phone' => '',
        'phone2' => '', 'mobile_phone' => '', 'email' => '', 'ship_time' => '', 'ship_time_text' => '', 'ship_date' => '',
        'ship_zone' => '', 'mkad' => '', 'city' => '', 'street' => '', 'house' => '', 'corp' => '',
        'enter' => '', 'domofon' => '', 'floor' => '', 'call' => '', 'lift' => '', 'kv' => '', 'urname' => '',
        'uraddr' => '', 'postaddr' => '', 'rs' => '', 'ks' => '', 'bik' => '', 'bank' => '', 'innkpp' => '', 'ogrn' => '',
        'okpo' => '', 'gendir' => '', 'user_status' => '', 'address' => '', 'latlong' => '',
        'correct_addr' => '', 'comment' => '', 'courier' => '', 'address_id' => '',
    );

    public function filters() {
        return array(
            'phone' => array(
                array('Txt::phone_clear', array(':value')),
            ),
            'phone2' => array(
                array('Txt::phone_clear', array(':value')),
            ),
            'mobile_phone' => array(
                array('Txt::phone_clear', array(':value')),
            )
        );
    }

    public function rules()
    {
        return array(
            'email' => array(
                array('not_empty'),
                array('email'),
            ),
            /* 'phone' => array(
                array('not_empty'),
                array('phone', array(':value', 11)),
            ),
            'phone2' => array(
                array('phone', array(':value', 11)),
            ),
            'mobile_phone' => array(
                array('Txt::phone_is_mobile', array(':value')),
            ), */
            'name' => array(
                array('not_empty'),
            ),
        );
    }
}
