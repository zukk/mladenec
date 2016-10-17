<?php
class Model_Sert_Group extends ORM {

    protected $_table_name = 'z_sert_group';

    protected $_table_columns = array('id' => '', 'name' => '');

    protected $_has_many = array(
        'serts' => array('model' => 'sert', 'foreign_key' => 'section_id'),
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
        );
    }
}
