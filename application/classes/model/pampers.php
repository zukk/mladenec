<?php
class Model_Pampers extends ORM {

    protected $_table_name = 'pampers';

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'weight' => '', 'age' => '', 'index' => '', 'address' => '', 'phone' => '', 'email' => '', 'site' => ''
    );


    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            ),
            'weight' => array(
                array('not_empty'),
            ),
            'age' => array(
                array('not_empty'),
            ),
            'index' => array(
                array('not_empty'),
                array('exact_length', array(':value', 6)),
            ),
            'address' => array(
                array('not_empty'),
            ),
            'phone' => array(
                array('not_empty'),
                array('phone', array(':value')),
            ),
            'email' => array(
                array('not_empty'),
                array('email'),
            ),
            'site' => array(
                array('not_empty'),
            ),
        );
    }
}
