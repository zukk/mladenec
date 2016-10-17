<?php
class Model_Seotemplates extends ORM
{
    protected $_table_name = 'z_seotemplates';

    protected $_primary_key = 'id';

    protected $seo_auto = 0;

    protected $_table_columns = array(
        'id' => '',
        'title' => '',
        'rule' => '',
        'active' => '',
        'type' => ''
    );
}