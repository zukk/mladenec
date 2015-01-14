<?php

class Model_Stat_Monthly extends  ORM {

    protected $_table_name = 'z_stat_monthly';

    protected $_table_columns = array(
        'id' => '',  'sdate' => '', 'new' => '', 'new_card' => '', 'sum' => '', 'sum_card' => '', 'complete' => '', 'complete_card' => '', 'complete_sum' => '', 'complete_sum_card' => '', 'cancel' => '', 'cancel_card' => '', 'cancel_sum' => '', 'cancel_sum_card' => ''
    );
}