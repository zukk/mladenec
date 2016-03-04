<?php
class Model_Blocklinksanchor extends ORM
{
    protected $_table_name = 'blocklinksanchor';

    protected $_table_columns = [
        'url_id' => 0,
        'title' => '',
        'url' => ''
    ];

    protected $_primary_key = 'url_id';
}