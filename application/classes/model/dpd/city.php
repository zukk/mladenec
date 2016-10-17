<?php
class Model_Dpd_City extends ORM
{
    protected $_table_name = 'dpd_city';

    protected $_belongs_to = [
        'region' => array('model' => 'dpd_region', 'foreign_key' => 'region_id')
    ];

    protected $_table_columns = [
        'id' => '', 'name' => '', 'region_id' => '', 'zone' => '', 'tariff' => ''
    ];
}

