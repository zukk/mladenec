<?php
class Model_User_Address extends ORM {

    protected $_table_name = 'z_user_address';

    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
    );

    public function rules()
    {
        return array(
            'city' => array(
                array('not_empty'),
            ),
            'street' => array(
                array('not_empty'),
            ),
            'house' => array(
                array('not_empty'),
            ),
            'floor' => array(
                array('not_empty'),
            ),
            'kv' => array(
                array('not_empty'),
            ),
            'latlong' => array(
                array('not_empty'),
            ),
        );
    }
}
