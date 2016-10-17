<?php
class Model_User_Address extends ORM {

    protected $_table_name = 'z_user_address';

    protected $_belongs_to = [
        'user' => ['model' => 'user', 'foreign_key' => 'user_id'],
    ];

    protected $_table_columns = [
        'id' => '', 'user_id' => '', 'active' => '', 'mkad' => '', 'city' => '', 'street' => '', 'house' => '', 'corp' => '', 'enter' => '', 'domofon' => '', 'floor' => '',
        'lift' => '', 'kv' => '', 'latlong' => '', 'correct_addr' => '', 'approved' => '', 'comment' => '', 'last_used' => '', 'zone_id' => '',
    ];

    public function rules()
    {
        return [
            'city' => [
                ['not_empty'],
            ],
            'street' => [
                ['not_empty'],
            ],
            'house' => [
                ['not_empty'],
            ],
            /*
            'latlong' => [
                ['not_empty'],
            ],
            */
        ];
    }
}
