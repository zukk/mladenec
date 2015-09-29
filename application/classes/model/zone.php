<?php

class Model_Zone extends ORM { // зоны доставки

    const ZAMKAD = 1; // id зоны замкадья (к цене нужно расстояние от мкад)
    const MYTISHI = 3; // id зоны МЫТИЩИ (технической?)
    const MKAD = 10; // id зоны мкад (технической)
    const DEFAULT_ZONE = 2; // id зоны поумолчанию (центральная зона)
    const NAME_REGION = 'Региональная доставка'; // название зоны вне зоны

    protected $_table_name = 'z_zone';

    protected $_has_many = [
        'times' => array('model' => 'zone_time', 'foreign_key' => 'zone_id'),
    ];

    protected $_table_columns = [
        'id' => '', 'name' => '', 'short' => '', 'text' => '', 'poly' => '', 'priority' => '', 'color' => '', 'active' => '',
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                ['not_empty'],
            ],
            'poly' => [
                ['not_empty'],
            ],
        ];
    }

    public function flag()
    {
        return array('active');
    }

    public function __toString()
    {
        return strval($this->id);
    }

    /**
     * Определить зону доставки по координатам, если один параметр,
     * если 2 - проверка что координаты в зоне (не учитывая активности зоны)
     * @param string $latlong - координаты зоны через пробел, запятую или запятую+пробел
     * @param int $zone_id - ид зоны
     * @return Model_Zone
     */
    static public function locate($latlong, $zone_id = FALSE)
    {
        $latlong = str_replace(',', ' ', $latlong); // координаты через пробел

        $z = ORM::factory('zone')
            ->where(DB::expr('GISWithin(GeomFromText(\'Point('), $latlong, Db::expr(')\'), polygon)'))
            ->order_by('priority', 'DESC')
            ->limit(1);

        if (empty($zone_id)) {
            $z->where('active', '=', '1');
        } else {
            $z->where('id', '=', $zone_id);
        }
        $zone = $z->find();

        if ( ! $zone->loaded()) return FALSE; // никуда не попали
        if ( ! empty($zone_id)) return TRUE; // проверяли попадание в конкретную зону - и попали
        return $zone; // вернём зону куда попали
    }

    /**
     * расстояние в километрах между двумя точками
     */
    static function distance($latlon1, $latlon2)
    {
        list($lat1, $lon1) = preg_split('~(, ?| )~', $latlon1);
        list($lat2, $lon2) = preg_split('~(, ?| )~', $latlon2);

        $R = 6371;
        $a = 0.5 - cos(($lat2 - $lat1) * pi() / 180) / 2 + cos($lat1 * pi() / 180) * cos($lat2 * pi() / 180) * (1 - cos(($lon2 - $lon1) * pi() / 180)) / 2;

        return $R * 2 * asin(sqrt($a));

    }

    /**
     * поиск ближайшей точки на мкаде, для расчёта маршрута от мкад ( актуально только для зоны ZAMKAD)
     * @param $latlong
     * @return array
     */
    static public function closest_mkad($latlong)
    {
        $closest = array();
        list($lat, $long) = preg_split('~(, ?| )~', $latlong);

        $mkad_points = new self(self::MKAD);
        $points = explode(',', $mkad_points->poly);
        foreach($points as $p) {
            $point = explode(' ', $p);
            $range = abs($lat - $point[0]) + abs($long - $point[1]);
            if (empty($closest) OR $range < $closest['range']) {
                $closest = array('point' => $point, 'range' => $range);
            }
        }

        return $closest['point'];
    }

    /**
     * Получить строку с именем зоны доставки по ид
     */
    static public function name($id)
    {
        $zone = new self($id);
        if ( ! $zone->loaded()) return 'Не определено';
        return $zone->name;
    }

    /**
     * Если изменился полигон - меням его в GEO поле
     * @param null|\Validation $validation
     * @return ORM|void
     */
    function save(Validation $validation = NULL)
    {
        if ($this->changed('poly')) $update_poly = TRUE;
        parent::save($validation);
        if ( ! empty($update_poly)) {
            DB::update($this->_table_name)
                ->set(array('polygon' => DB::expr("GeomFromText(CONCAT('POLYGON((', poly, '))'))")))
                ->where('id', '=', $this->id)
                ->execute();
        }
    }

    /**
     * Минимальная цена доставки в зоне, не работает для регионов, не учитывает мкад
     * @param $zone_id
     * @param $price
     * @return bool
     */
    static function min_price($zone_id, $price)
    {
        $z = new Model_Zone($zone_id);
        if ( ! $z->loaded() && ! $z->active) return FALSE;

        $zt = $z->times->where('active', '=', 1)
            ->order_by('price')
            ->order_by('sort')
            ->limit(1)
            ->find();  // cамое дешевое время

        if ( ! $zt->loaded()) return FALSE;

        return $zt->get_price($price);
    }
}