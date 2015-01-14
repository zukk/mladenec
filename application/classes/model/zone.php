<?php
class Model_Zone extends ORM { // зоны доставки

    const ZAMKAD = 1; // id зоны замкадья, для точек не попавших ни в одну зону
    const MKAD = 10; // id зоны мкад (технической)
    const DEFAULT_ZONE = 2; // id зоны поумолчанию (центральная зона)

    protected $_table_name = 'z_zone';

    protected $_has_many = array(
        'times' => array('model' => 'zone_time', 'foreign_key' => 'zone_id'),
    );

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'short' => '', 'text' => '', 'poly' => '', 'priority' => '', 'color' => '', 'active' => '',
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
            'poly' => array(
                array('not_empty'),
            ),
        );
    }

    public function flag()
    {
        return array('active');
    }

    /**
     * Определить зону доставки по координатам
     * @param string $latlong - координаты зоны через пробел, запятую или запятую+пробел
     * @return Model_Zone
     */
    static public function locate($latlong)
    {
        $latlong = str_replace(',', ' ', $latlong); // координаты через пробел

        $zone = DB::query(Database::SELECT,
            "SELECT * FROM z_zone
            WHERE active = 1 AND GISWithin(GeomFromText('Point(".$latlong.")'), polygon)
            ORDER BY priority DESC
            LIMIT 1")
            ->as_object('Model_Zone')
            ->execute()
            ->current();

        if (empty($zone)) return false; // никуда не попали - замкадье

        return $zone; // вернём зону куда попали
    }

    /**
     * поиск ближайшей точки на мкаде, для расчёта маршрута от мкад
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
     * Получить возможные даты доставки для зоны
     * @param Cart $cart Если не передана - то возвращает 10 дат(с учётом сдвига для возможной крупногб.)
     * @return array ts => morning
     */
    public function allowed_date(Cart $cart = NULL)
    {
        $add = 0;
        if ($cart instanceof Cart && $cart->get_total() > 0) {
            if ($cart->big_to_wait(FALSE)) {
                $add = 3;   // + 3 дня если нет на складе
            } elseif ($cart->big) {
                $add = 1;   // + 1 день если просто крупногабаритка
            }
        }

        // получение списка возможных дат доставки
        $exclude_dates = DB::select('id', 'morning')
            ->from('z_no_delivery')
            ->where('id', '>=', strftime('%Y-%m-%d'))
            ->where('zone_id', '=', $this->id)
            ->execute()
            ->as_array('id', 'morning'); // эти даты надо исключить

        $hm = intval(date('Gi')); // часыминуты
        $i =  $hm >= 1200 ? 1 : 0; // c 1200 часов доставка на сегодня вырубается
        $dates = is_null($cart) ? 10 : 7; // число выдаваемых дат
        $return = array();

        do { // набиваем $dates дат, кроме исключённых
            $d = strtotime('+ '.($i).' days midnight');
            $i++;
            if ($i < $add) continue;
            $key = strftime('%Y-%m-%d', $d);
            if ( ! isset($exclude_dates[$key]) || $exclude_dates[$key] == 1) {
                $return[$key] = ! empty($exclude_dates[$key]); // TRUE for morning
                $dates--;
            }
        } while($dates > 0);

        return $return;
    }

    /**
     * Получить возможные интервалы доставки для даты в формате (Y-m-d)
     * @param string $date - дата
     * @param float $sum - сумма заказа, если передана - будет расчёт стоимости доставки
     * @return array
     */
    public function allowed_time($date, $sum = NULL)
    {
        $no_time = array(0 => 'Нет доступных интервалов на эту дату');

		if (strtotime($date) < strtotime('today midnight')) return $no_time;

        $excluded = DB::select('morning')
            ->from('z_no_delivery')
            ->where('zone_id', '=', $this->id)
            ->where('id', '=', $date)
            ->execute()
            ->current();

        // c 1800 часов доставка на завта утро - вырубается
        if (
			strtotime( $date ) == strtotime('+1 days midnight')
            && intval(date('Gi')) >= 1800
            && !isset($excluded))
        {
            $excluded = 1;
        }

        if ($excluded === 0) return $no_time;

        $q = $this->times
            ->order_by('morning')
            ->order_by('sort')
            ->where('active', '=', 1)
            ->where('week_day', '&', 1 << (date('N', strtotime($date)) - 1)); // учёт дня недели

        if ($excluded == 1) { // утреннее время исключено
            $q->where('morning', '=', 0);
        }

        $times = $q->find_all();
        if (empty($times)) return $no_time;

        $return = array();
        $price = ($date == '2014-12-31') ? 500 : 0; // +500 рублей 31.12

        foreach($times as $time) {
            if ($sum) {
                $return[$time->id] = $time->name.' ['.($time->get_price($sum) + $price).' руб.] ';
            } else {
                $return[$time->id] = $time->name;
            }
        }
        return $return;
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
}