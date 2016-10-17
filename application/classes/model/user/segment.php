<?php
class Model_User_Segment extends ORM
{
    protected $_table_name = 'user_segment';

    protected $_belongs_to = [
        'user' => ['model' => 'user', 'foreign_key' => 'user_id'],
    ];

    protected $_table_columns = [
        'user_id' => '',
        'last_visit' => '',
        'last_order' => '',
        'arpu' => '',
        'last_order_sum' => '',
        'orders_count' => '',
        'orders_sum' => '',
        'sum_big' => '',
        'sum_diaper' => '',
        'sum_eat' => '',
        'sum_toy' => '',
        'sum_care' => '',
        'sum_dress' => '',
        'buy_big' => '',
        'buy_diaper' => '',
        'buy_eat' => '',
        'buy_toy' => '',
        'buy_care' => '',
        'sert_use' => '',
        'buy_dress' => '',
        'childs' => '',
        'has_boy' => '',
        'has_girl' => '',
        'child_birth_min' => '',
        'child_birth_max' => '',
        'pregnant' => '',
        'pregnant_terms' => '',
        'upload_ts' => '',
    ];
}
