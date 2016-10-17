<?php
class Model_Call extends ORM {

    protected $_table_name = 'z_call';

    protected $_table_columns = array('id' => '', 'user_id' => '', 'name' => '', 'phone' => '', 'created' => '', 'in1c' => '');

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            ),
            'phone' => array(
                array('not_empty'),
                array('phone', array(':value', 11)),
            ),
        );
    }

    /**
     * проставить пачке что она в 1с ушла
     * @param $idz
     */
    public static function in1c($idz)
    {
        DB::update('z_call')->set(array('in1c' => '1'))->where('id', 'IN', $idz)->execute();
    }

}