<?php
class Model_City extends ORM
{
    protected $_table_name = 'city';

    protected $_belongs_to = [
        'region' => array('model' => 'region', 'foreign_key' => 'region_id')
    ];

    protected $_table_columns = [
        'id' => '', 'name' => '', 'region_id' => '', 'zone' => '', 'tariff' => ''
    ];
}

