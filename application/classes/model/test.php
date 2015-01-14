<?php
class Model_Test extends ORM {

    protected $_table_name = 'z_test';

    protected $_table_columns = array(
        'id' => '', 'user_id' => '', 'order_id' => '', 'user_agent' => '',
        'q1' => '', 'q2' => '', 'q3' => '', 'q4' => '', 'q5' => ''
    );

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'q1' => array(
                array('not_empty'),
            ),
            'q2' => array(
                array('not_empty'),
            ),
            'q3' => array(
                array('not_empty'),
            ),
            'q4' => array(
                array('not_empty'),
            ),
            'q5' => array(
                array('not_empty'),
            ),
        );
    }

}