<?php
class Model_Filter extends ORM {

    const CLOTH_BIG_TYPE = 1954; // фильтр По больщому типу в одежде, по нему распадаются подкатегории одежды
    const TOYS_BIG_TYPE = 2228; // фильтр По больщому типу в игрушках

    protected $_table_name = 'z_filter';

    protected  $_has_many = array(
        'values' => array('model' => 'filter_value', 'foreign_key' => 'filter_id'),
    );

    protected $_belongs_to = array(
        'section' => array('model' => 'section', 'foreign_key' => 'section_id'),
    );

    protected $_table_columns = array(
        'id' => '', 'code' => '', 'name' => '', 'sort' => '', 'section_id' => '',
    );

}
