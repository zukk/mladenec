<?php
class Model_User_Phone extends ORM {

    protected $_table_name = 'z_user_phone';

    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
    );

    public function rules()
    {
        return array(
            'phone' => array(
                array('not_empty'),
                array('phone', array(':value', 11)),
            )
		);
    }
}
