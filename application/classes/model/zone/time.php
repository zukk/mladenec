<?php
class Model_Zone_Time extends ORM { // интервалы для зон доставки

    // двоичные коды для дней недели
    static $week_days = array(
        'Пн' => 1, // 2^0
        'Вт' => 2,
        'Ср' => 4,
        'Чт' => 8,
        'Пт' => 16,
        'Cб' => 32,
        'Вс' => 64, // 2^6
    );

    protected $_table_name = 'z_zone_time';

    protected $_belongs_to = array(
        'zone' => array('model' => 'zone', 'foreign_key' => 'zone_id'),
    );

    protected $_has_many = array(
        'prices' => array(
            'model' => 'zone_time_price',
            'foreign_key'   => 'time_id',
        )
    );

    protected $_table_columns = array(
        'id' => '', 'zone_id' => '', 'name' => '', 'week_day' => '', 'morning' => '', 'active' => '', 'sort' => '', 'price' => ''
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
            'week_day' => array(
                array('not_empty'),
            ),
        );
    }

    /**
     * Получить цену в интервале в зависимости от суммы заказа
     * @param $sum
     * @return int
     */
    public function get_price($sum)
    {
        $price = $this->prices->where('min_sum', '<', $sum)->order_by('min_sum', 'DESC')->limit(1)->find();

        if ( ! $price->id) return $this->price; // базовая цена для интервала
        return $price->price;
    }

    /**
     * Получить строку с именем интервала доставки по ид
     * @param $id
     * @return string
     */
    static public function name($id)
    {
        $time = new self($id);
        if ( ! $time->loaded()) return 'Не определено';
        return $time->name;
    }

    public function flag()
    {
        return array('morning', 'active');
    }
}