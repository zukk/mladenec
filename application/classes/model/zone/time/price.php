<?php
class Model_Zone_Time_Price extends ORM { // цены для интервалов зон доставки

    protected $_table_name = 'z_zone_time_price';

    protected $_belongs_to = array(
        'zone_time' => array('model' => 'zone_time', 'foreign_key' => 'time_id'),
    );

    protected $_table_columns = array(
        'id' => '', 'time_id' => '', 'min_sum' => '', 'price' => '',
    );

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'min_sum' => array(
                array('not_empty'),
            ),
        );
    }
}