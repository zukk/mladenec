<?php
class Model_Zone_Time_Price extends ORM { // цены для интервалов зон доставки

    protected $_table_name = 'z_zone_time_price';

    protected $_belongs_to = [
        'zone_time' => ['model' => 'zone_time', 'foreign_key' => 'time_id'],
    ];

    protected $_table_columns = [
        'id' => '', 'time_id' => '', 'min_sum' => '', 'price' => '',
    ];

    public function rules()
    {
        return [
            'min_sum' => [
                ['not_empty'],
            ],
        ];
    }
}