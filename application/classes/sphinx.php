<?php

class SphinxException extends Kohana_Exception {}

/**
 * Class sphinx
 * Обеспечивает общение со сфинксом, получение товаров для показа по различным запросам поиска, получение и кеш наборов данных для расширенного поиска
 */
class Sphinx {

    const SHOP_MLADENEC_ID = 1;
    const SHOP_EATMART_ID = 2;
    const SHOP_DEFAULT_ID = 1; // !!!used!!! Должен быть равен или SHOP_MLADENEC_ID или SHOP_EATMART_ID
    
    const HREF_INIT = 8;
    public $total = 0; // число результатов последнего поиска

    private $_mode = '';
    private $_query = '';
    private $_section = NULL; // если запрос относится к одной категории - тут категория
    private $_tag = NULL; // если запрос относится к одному тегу тут тег
    private $_menu_params = []; // параметры для меню,
    private $_params = []; // параметры поиска,
    private $_menu = []; // меню для поиска [бренды, категории, фильтры, цены]

    public $goods   = []; // результаты поиска по товарам
    public $price   = []; // спец. цены для товаров из результатов поиска
    public $actions = []; // акции для товаров из результатов поиска
    public $images  = []; // картинки для товаров из результатов поиска
    public $pager   = []; // пажинатор для результатов поиска
    public $filters   = []; // ab
    public $found   = 0; // число найденных результатов
    public $qs      = FALSE; // флаг наличия дополнительных параметров в запросе

    public $other_shop_goods            = []; // Товары, найденные на других витринах
    public $other_shop_goods_counter    = 0; // Сколько товаров найдено на других витринах

    /**
     * получить метаданные после запроса к сфинксу
     */
    public function meta()
    {
        return Database::instance('sphinx')
            ->query(Database::SELECT, 'SHOW META')
            ->as_array('Variable_name', 'Value'); // meta to get total found for pager
    }

    /**
     * Пытаемся исправить опечатки и раскладку по триграммам
     * @param $word - одно слово!
     * @return bool
     * @throws Kohana_Exception
     */
    public static function correct($word)
    {
        //echo 'correct['.$word;

		$len = mb_strlen($word);
        $words = Txt::en_ru($word);
        $trigrams = '"'.implode('"/2 | "',  Txt::trigrams($words)).'"/2';

        $q = DB::query(Database::SELECT, "
                SELECT id, weight() + 2 - ABS(len - :len) AS myrank
                FROM suggest WHERE len BETWEEN :min AND :max
                AND MATCH(:trigrams)
                ORDER BY myrank DESC, freq DESC
                OPTION ranker=expr('sum(hit_count*user_weight)')
            ")
            ->param(':trigrams', $trigrams)
            ->param(':len', $len)
            ->param(':min', $len - 2)
            ->param(':max', $len + 2);

        $id = Database::instance('sphinx')
            ->query(Database::SELECT, $q)
            ->as_array('id', 'myrank'); //('id', 0); // meta to get total found for pager?

        if ( ! empty($id)) {

			$result = DB::select('*')
                ->from('z_suggest')
                ->where('id', '=', key($id))
                ->limit(1)
                ->execute()
                ->as_array();

			if ( ! empty($result)) return $result[0]['keyword'];
		}
		
		return FALSE;
	}

    /**
     * Shop id for vitrina search
     * @return int
     */
    public static function shop_id()
    {
        switch(Kohana::$server_name)
        {
            case 'mladenec':
                return self::SHOP_MLADENEC_ID;
            case 'ogurchik':
                return self::SHOP_EATMART_ID;
            default:
                return self::SHOP_DEFAULT_ID;
        }
    }
    
    /**
     * Возвращает ID другой витрины
     * 
     * @return int
     */
    public static function get_other_shop_id()
    {
        $current = self::shop_id();
        
        switch($current) {
            case self::SHOP_MLADENEC_ID: 
                return self::SHOP_EATMART_ID;
            case self::SHOP_EATMART_ID:
                return self::SHOP_MLADENEC_ID;
            default:
                return self::SHOP_DEFAULT_ID;
        }
    }

    /**
     * Читает настройки категории
     * return Model_Section
     */
    private function _read_section($c)
    {
        $this->_section = new Model_Section($c);
        if ( ! $this->_section->loaded()) return FALSE;

        if ( ! empty($this->_section->settings['orderByItems'])) $this->_params['sorts'] = $this->_section->settings['orderByItems'];        
        $this->_params['s'] = ! empty($this->_section->settings['s']) ? $this->_section->settings['s'] : 'rating';

        if ( ! empty($this->_section->settings['per_page'])) $this->_params['per_page'] = $this->_section->settings['per_page'];
        $this->_params['pp'] = $this->_params['per_page'][0];

        $this->_menu_params['c'] = [$this->_section->id];

        $this->_section->tags();
        return $this->_section;
    }

    /**
     * инициализация параметров поиска по типу и запросу
     * @param $type - тип поиска (word, section, discount)
     * @param $query - поисковый запрос
     * @param $read_query bool - флаг чтения query_string
     * @throws Cache_Exception
     * @throws Kohana_Exception
     * @return \Sphinx
     */
    function __construct($mode, $q = NULL, $read_query = TRUE)
    {
        if ( ! in_array($mode, [
            'section', 'section_filter', 'word', 'suggest', 'discount', 'superprice',
            'pampers', 'tag', 'new', 'hitz', 'action', 'brand'
        ])) {
            throw new SphinxException('Not supported mode: '.$mode);
        }

        $this->_mode = $mode;
        $this->_query = $q;

        $this->_params['sorts'] = ['rating', 'new', 'price', 'pricedesc']; // def(aut params
        $this->_params['per_page'] = [30, 60, 90];
        $this->_params['pp'] = $this->_params['per_page'][0];
        $this->_params['s'] = 'rating';

        switch ($this->_mode) {
            case 'word':
            case 'suggest':
                $this->_menu_params['q'] = $this->_params['q'] = $q;
                break;

            case 'section':
            case 'section_filter':
                @list($sid, $f) = explode('_', $this->_query);
                $sid = intval($sid);

                if ( ! $this->_read_section($sid)) { // menu_params set inside _read_section
                    echo 'no section for ' . $this->_mode.':'.$sid;
                    throw new HTTP_Exception_404;
                }
                $this->_params['c'] = $this->_menu_params['c'] = [intval($sid)];

                if ($this->_mode == 'section_filter') {

                    $val = new Model_Filter_Value($f);
                    if ( ! $val->loaded() || ! Model_Filter::big($val->filter_id)) { // и только по большому типу
                        echo 'no filter for ' . $this->_mode.':'.$f;
                        throw new HTTP_Exception_404;
                    }
                    $this->_menu_params['f'] = $this->_params['f'] = [$val->filter_id => [$f]];
                }

                break;

            case 'discount':
                $this->_params['d'] = $this->_menu_params['d'] = 1;
                $this->_params['x'] = $this->_menu_params['x'] = 1;
                break;

            case 'superprice':
                $this->_params['su'] = $this->_menu_params['su'] = 1;
                $this->_params['x'] = $this->_menu_params['x'] = 1;
                break;

            case 'new':
                $this->_params['sorts'] = ['new', 'rating', 'price', 'pricedesc'];
                $this->_params['n'] = $this->_menu_params['n'] = 1;
                $this->_params['x'] = $this->_menu_params['x'] = 1;
                break;

            case 'hitz':
                $this->_params['h'] = $this->_menu_params['h'] = 1;
                break;

            case 'tag':
                $this->_tag = $tag = new Model_Tag($this->_query);
                if ( ! $tag->loaded()) return FALSE;

                $tag_params = $tag->parse_query();
                $this->_params += $tag_params;

                if ($tag->section_id) { // новые теговые - меню как у категории
                    if ( ! $this->_read_section($tag->section_id)) { // menu_params set inside _read_section
                        echo 'no section for ' . $this->_mode . ':' . $this->_query;
                        throw new HTTP_Exception_404;
                    }
                    if ($this->_section->settings['list'] == Model_Section::LIST_FILTER) { // теговая от категории с большим фильтром
                        if ( ! empty($tag_params['f'])) {
                            foreach($tag_params['f'] as $fid => $vals) {
                                if (Model_Filter::big($fid) && count($vals) == 1) { // в параметрах есть большой фильтр и он только один
                                    $this->_menu_params['f'][$fid] = $vals;
                                }
                            }
                        }
                    }
                    ///print_r($this->_menu_params);

                } else { // старые теговые - меню из условий
                    $this->_menu_params = $tag_params; // старые теговые
                }

                break;

            case 'action':
                $action = new Model_Action($this->_query);
                if ( ! $action->loaded() || ! $action->active) return FALSE; //throw new HTTP_Exception_404;
                $this->_params['a'] = $this->_menu_params['a'] = intval($this->_query);
                $this->_params['x'] = $this->_menu_params['x'] = 1;

                break;

            case 'brand':
                $brand = new Model_Brand($this->_query);
                if ( ! $brand->loaded() || ! $brand->active) return FALSE; //throw new HTTP_Exception_404;
                $this->_params['b'] = $this->_menu_params['b'] = [intval($this->_query)];

                break;

            case 'pampers':
                $this->_read_section(Model_Good::PAMPERS_SECTION);
                $this->_params['b'] = $this->_menu_params['b'] = [Model_Good::PAMPERS_BRAND];
                $this->_params['c'] = $this->_menu_params['c'] = [Model_Good::PAMPERS_SECTION];

                break;
        }
        if ($read_query) $this->read_params();
    }

    /**
     * Очистить кеш статистики для текущего запроса сфинкса
     * @throws Cache_Exception
     */
    public function clear_stats_cache()
    {
        $href = $this->href();
        $stats_key = md5($href.DOCROOT).Kohana::$server_name;
        Cache::instance()->delete($stats_key);
    }

    /**
     * Получить параметры-ограничения для запроса
     * получает список брендов, [категорий], фильтров, мин и макс цену, кеширует на час
     * @param $full - получить все параметры для меню (не учитывать доп параметры из query_string)
     * @return array|mixed
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function stats()
    {
        $href = $this->href();
        $stats_key = md5($href.DOCROOT).Kohana::$server_name;
        $return = Cache::instance()->get($stats_key);
        
        if (empty($return)) {
            $sphinx_db = Database::instance('sphinx');

            $q = DB::select(DB::expr('###'))
                ->from('goods_zukk');

            if ($this->_mode !== 'action') $q->where('shop_id', 'IN', [self::shop_id()]); // везде кроме акций учитывается витрина

            $this->_apply_params($q, $this->_menu_params, TRUE); //$full ?: $this->_params

            // узнаём первым запросом а есть ли что-то вообще
            $max = $min = $total = $has_actions = 0;
            $prices = $sphinx_db->query(Database::SELECT, str_replace('###', 'MAX(price) as `max`, MIN(price) as `min`, COUNT(*) as `total`', $q))->as_array();
            if ( ! empty($prices)) extract($prices[0]); // max, min, total

            $brands = $sections = $countries = $fvals = [];
            if ($total > 0) {

                $cloneq = clone $q;
                $brands = $sphinx_db->query(Database::SELECT, str_replace('###', 'brand_id, COUNT(*) as qty', $cloneq->group_by('brand_id')->limit(250)))
                    ->as_array('brand_id', 'qty');

                if (empty($this->_section)) {
                    $cloneq = clone $q;
                    $sections = $sphinx_db
                        ->query(Database::SELECT, str_replace('###', 'section_id, COUNT(*) as qty ', $cloneq->group_by('section_id')->limit(250)))
                        ->as_array('section_id', 'qty');
                } else {
                    $sections = [$this->_section->id => $total];
                }

                if ( ! empty($this->_section) || in_array($this->_mode, ['tag'])) { // для одной категории или тега - получаем ещё фильтры
                    $cloneq = clone $q;
                    $fvals = $sphinx_db
                        ->query(Database::SELECT, str_replace('###', '@groupby as fvalue, COUNT(*) as qty', $cloneq->group_by('fvalue')->limit(250)))
                        ->as_array('fvalue', 'qty');
                }

                // всегда получаем страны
                $cloneq = clone $q;
                $countries = $sphinx_db->query(Database::SELECT, str_replace('###', 'country_id, COUNT(*) as qty', $cloneq->where('country_id', '!=', 0)->group_by('country_id')->limit(250)))
                    ->as_array('country_id', 'qty');
                if (isset($countries[0])) unset($countries[0]);

                // сколько акций
                $cloneq = clone $q;
                $has_actions = $sphinx_db->query(Database::SELECT, str_replace('###', 'COUNT(DISTINCT action_id) as qty', $cloneq->where('action_id', '>', 0)->limit(250)))
                    ->as_array('qty', 'qty');
                $has_actions = current($has_actions);
            }

            if ( ! empty($sections)) {
                foreach(Model_Section::id_name(array_keys($sections)) as $id => $name) {
                    $sections[$id] = [
                        'id' => $id,
                        'name' => $name['name'],
                        'qty' => $sections[$id]
                    ];
                }
            }

            if ( ! empty($fvals)){
                list($filters, $vals, $roditi) = Model_Filter_Value::filter_val(array_keys($fvals));
			    foreach($vals as $fid => $values) {
                    foreach($values as $vid => $name) {
                        $vals[$fid][$vid] = [
                            'name'  => $name,
                            'qty'   => $fvals[$vid],
                            'roditi'   => $roditi[$fid][$vid]
                        ];
                    }
                    if ($fid == Model_Filter::STROLLER_WEIGHT) { // вес в колясках - статистика по интервалам + минимум и максимум
                        $stroller_weight = [];
                        foreach($vals[$fid] as $val) {
                            $float = floatval($val['name']);
                            if (empty($stroller_weight['min']) || $stroller_weight['min'] > $float) {
                                $stroller_weight['min'] = $float;
                            }
                            if (empty($stroller_weight['max']) || $stroller_weight['max'] < $float) {
                                $stroller_weight['max'] = $float;
                            }

                            foreach(Model_Filter::begunok($fid)['settings'] as $id => $data) {
                                if (empty($stroller_weight[$id])) $stroller_weight[$id] = 0;
                                if ($data['max'] >= $float and $data['min'] <= $float) {
                                    $stroller_weight[$id] += $val['qty'];
                                }
                            }
                        }
                    }

                    if ($fid == Model_Filter::STROLLER_SHASSI) { // шасси в колясках - статистика по интервалам + минимум и максимум
                        $stroller_shassi = [];
                        foreach($vals[$fid] as $val) {
                            $float = intval($val['name']);
                            if (empty($stroller_shassi['min']) || $stroller_shassi['min'] > $float) {
                                $stroller_shassi['min'] = $float;
                            }
                            if (empty($stroller_shassi['max']) || $stroller_shassi['max'] < $float) {
                                $stroller_shassi['max'] = $float;
                            }

                            foreach(Model_Filter::begunok($fid)['settings'] as $id => $data) {
                                if (empty($stroller_shassi[$id])) $stroller_shassi[$id] = 0;
                                if ($data['max'] >= $float and $data['min'] <= $float) {
                                    $stroller_shassi[$id] += $val['qty'];
                                }
                            }
                        }
                    }
                    
                    if ($fid == Model_Filter::VOLUME_KG) { // вес в быт хим
                        $volume_kg = [];
                        foreach($vals[$fid] as $val) {
                            $float = floatval($val['name']);
                            if (empty($volume_kg['min']) || $volume_kg['min'] > $float) {
                                $volume_kg['min'] = $float;
                            }
                            if (empty($volume_kg['max']) || $volume_kg['max'] < $float) {
                                $volume_kg['max'] = $float;
                            }
                        }
                    }
                    if ($fid == Model_Filter::VOLUME_LITR) { // объем в быт хим
                        $volume_litr = [];
                        foreach($vals[$fid] as $val) {
                            $float = floatval($val['name']);
                            if (empty($volume_litr['min']) || $volume_litr['min'] > $float) {
                                $volume_litr['min'] = $float;
                            }
                            if (empty($volume_litr['max']) || $volume_litr['max'] < $float) {
                                $volume_litr['max'] = $float;
                            }
                        }
                    }
                }
            }

            if ( ! empty($countries)) {
                foreach(Model_Country::id_name(array_keys($countries)) as $id => $name) {
                    $countries[$id] = [
                        'id' => $id,
                        'name' => $name['name'],
                        'qty' => $countries[$id]
                    ];
                }
                $countries = array_filter($countries, function ($item) { return ! empty($item['name']);} );
                if ( ! empty($this->_section) && $this->_section->id == Model_Section::DIAPERS_ID) {
                    uasort($countries, function($a, $b) { // список стран - обратно алфавиту !!! ключи сохраняем !!!
                        return -strcmp($a['name'], $b['name']);
                    });
                } else {
                    uasort($countries, function($a, $b) { // список стран - по алфавиту !!! ключи сохраняем !!!
                        return strcmp($a['name'], $b['name']);
                    });
                }
            }

            if ( ! empty($brands)) {
                foreach(Model_Brand::id_name(array_keys($brands)) as $id => $name) {
                    $brands[$id] = [
                        'id' => $id,
                        'name' => $name['name'],
                        'qty' => $brands[$id]
                    ];
                }
                if ( ! empty($this->_section)) {

                    $r = array(); // сюда собираем начало списка брендов

                    if ( ! empty( $this->_section->settings['brands'] )) { // в секции проставлен порядок брендов

                        $brand_order = $this->_section->settings['brands'];

                        if ($this->_section->settings['list'] == Model_Section::LIST_FILTER && ! empty($this->_menu_params['f']) && count($this->_menu_params['f']) == 1) { // может быть порядок брендов задан в фильтре

                            reset($this->_menu_params['f']);
                            $fid = key($this->_menu_params['f']);
                            $val = $this->_menu_params['f'][$fid][0];

                            if (Model_Filter::big($fid) && ! empty(Model_Filter::$_brand_order[$val])) {
                                $brand_order = Model_Filter::$_brand_order[$val];
                            }
                        }

                        foreach($brand_order as $bId) { // начало списка брендов - как в настройках
                            if ( ! empty($brands[$bId])) {
                                $r[$bId] = $brands[$bId];
                                unset($brands[$bId]);
                            }
                        }
                    }

                    uasort($brands, function($a, $b) { // продолжение списка брендов - по алфавиту !!! ключи сохраняем !!!
                        return strcmp($a['name'], $b['name']);
                    });

                    $brands = $r + $brands;
                }
            }
            $return = [
                'sections'  => ! empty($sections) ? $sections : [],
                'brands'    => $brands,
                'countries' => $countries,
                'actions'   => $has_actions,
                'max'       => ceil($max / 100),
                'min'       => floor($min / 100),
                'total'     => $total,
                'filters'   => ! empty($filters) ? $filters : [],
                'vals'      => ! empty($vals) ? $vals : [],
            ];
            if ( ! empty($stroller_weight)) $return['stroller_weight'] = $stroller_weight;
            if ( ! empty($stroller_shassi)) $return['stroller_shassi'] = $stroller_shassi;
            if ( ! empty($volume_kg)) $return['volume_kg'] = $volume_kg;
            if ( ! empty($volume_litr)) $return['volume_litr'] = $volume_litr;

            Cache::instance()->set($stats_key, $return); // кэшируем результат на час
        }
        return $return;
    }

    /**
     * Умные фильтры - вычисляем что нужно скрыть из меню, так как приведёт к 0 товаров
     * Тут же прячем фильтры с условиями, добавляем hide и binded для свяанных фильтров
     * Для этого идём по всем параметрам c,b,f
     */
    private function _exclude_empty($menu)
    {
        $sphinx_db = Database::instance('sphinx');
        $q = DB::select(DB::expr('###'))->from('goods_zukk');

        $params = $this->_params;
        $params_copy = $params;

        // сколько акций
        $cloneq = clone $q;
        $this->_apply_params($cloneq, $params_copy + $this->_menu_params);
        $has_actions = $sphinx_db->query(Database::SELECT, str_replace('###', 'COUNT(DISTINCT action_id) as qty', $cloneq->where('action_id', '>', 0)->limit(250)))
            ->as_array('qty', 'qty');
        $menu['actions'] = current($has_actions);

        $params_copy = $params; // для категорий
        $cloneq = clone $q;
        if (isset($params_copy['c'])) unset($params_copy['c']);
        $this->_apply_params($cloneq, $params_copy + $this->_menu_params);

        $sections = $sphinx_db->query(Database::SELECT, str_replace('###', 'section_id, COUNT(*) as qty', $cloneq->group_by('section_id')->limit(250)))
            ->as_array('section_id', 'qty');

        foreach($menu['sections'] as $id => $data) { // заполняем количества согласно найденному
            if ( ! empty($sections[$id])) {
                $menu['sections'][$id]['qty'] = $sections[$id];
            } else {
                $menu['sections'][$id]['qty'] = 0;
                //unset($menu['sections'][$id]);
            }
        }

        $params_copy = $params;
        $cloneq = clone $q;  // для брендов
        if (isset($params_copy['b'])) unset($params_copy['b']);

        if ( ! empty($params_copy['f'])) { // исключим параметры связанных с брендами фильтров если есть
            foreach ($params_copy['f'] as $f_id => $data) {
                if ($bid = Model_Filter::binded_to($f_id)) {
                    unset($params_copy['f'][$f_id]);
                }
            }
        }

        $this->_apply_params($cloneq, $params_copy + $this->_menu_params);
        $brands = $sphinx_db->query(Database::SELECT, str_replace('###', 'brand_id, COUNT(*) as qty', $cloneq->group_by('brand_id')->limit(250)))
            ->as_array('brand_id', 'qty');

        foreach($menu['brands'] as $id => $data) {
            if ( ! empty($brands[$id])) {
                $menu['brands'][$id]['qty'] = $brands[$id];
            } else {
                $menu['brands'][$id]['qty'] = 0;
                // unset($menu['brands'][$id]);
            }
        }

        $params_copy = $params;
        $cloneq = clone $q; // для стран
        if (isset($params_copy['co'])) unset($params_copy['co']);
        $this->_apply_params($cloneq, $params_copy + $this->_menu_params);

        $countries = $sphinx_db->query(Database::SELECT, str_replace('###', 'country_id, COUNT(*) as qty', $cloneq->group_by('country_id')->limit(250)))
            ->as_array('country_id', 'qty');

        foreach($menu['countries'] as $id => $data) {
            if ( ! empty($countries[$id])) {
                $menu['countries'][$id]['qty'] = $countries[$id];
            } else {
                $menu['countries'][$id]['qty'] = 0;
                // unset($menu['countries'][$id]);
            }
        }

        $vals = $fids = $filters = $filter_sections = [];
        if ($this->_mode == 'tag' and empty($this->_section) and ! empty($this->_menu_params['f'])) { // перекрестная теговая - показываем и считаем только упомянутые в отборе фильтры
            $fids = array_intersect_key($this->_params, $this->_menu_params['f']);
        } else {
            $fids = array_keys($menu['vals']);
        }
        if ( ! empty($fids)) {
            $filters = ORM::factory('filter')
                ->where('id', 'IN', $fids)
                ->find_all()
                ->as_array('id', 'section_id');
            foreach($filters as $id => $section_id) {
                $filter_sections[$section_id][$id] = $id;
            }
        }

        foreach($fids as $fid) { // для каждого фильтра делаем тоже самое
            $params_copy = $params;
            $cloneq = clone $q;
            if ( ! empty($params_copy['f'][$fid])) { // сбрасываем текущий фильтр если есть другие этой же категории
                $section_id = $filters[$fid];
                if (count($filter_sections[$section_id]) >= 1) {
                    unset($params_copy['f'][$fid]);
                }
            }

            $this->_apply_params($cloneq, $params_copy + $this->_menu_params);

            $vals[$fid] = $sphinx_db
                ->query(Database::SELECT, str_replace('###', '@groupby as fvalue, COUNT(*) as qty', $cloneq->group_by('fvalue')->limit(250)))
                ->as_array('fvalue', 'qty');

            foreach ($menu['vals'][$fid] as $id => $data) {
                if ( ! empty($vals[$fid][$id])) {
                    $menu['vals'][$fid][$id]['qty'] = $vals[$fid][$id];
                } else {
                    $menu['vals'][$fid][$id]['qty'] = 0;
                    //unset($menu['vals'][$fid][$id]);
                }
            }
        }

        // исключаем фильтры если там не осталось значений
        foreach(array_keys($menu['filters']) as $fid) {

            if (empty($menu['vals'][$fid])) {

                unset($menu['filters'][$fid]);

            } elseif ($id = Model_Filter::binded_to($fid)) { // связанный фильтр - исключаем есдли хозяин не выбран

                if ( ! empty($this->_params['b']) && in_array($id, $this->_params['b'])) { // ищем в брендах

                    $menu['binded']['b'][$id] = $fid;
                    $menu['hide'][$fid] = TRUE;

                } elseif ( ! empty($this->_params['f'])) { // ищем в фильтрах

                    $found = FALSE;
                    foreach ($this->_params['f'] as $f_id => $values) {
                        if (is_array($values) && in_array($id, $values)) {
                            $found = $f_id;
                            break;
                        }
                    }
                    if ($found) {
                        $menu['binded']['v'][$id] = $fid;
                        $menu['hide'][$fid] = TRUE;
                    } else {
                        unset($menu['filters'][$fid]);
                    }
                }
            } elseif($this->_mode == 'tag' and empty($this->_section) and empty($this->_menu_params['f'][$fid])) { // или если в теговой они не из тега
               //echo 'unset'.$fid;
                unset($menu['filters'][$fid]);
            }
        }
        $menu['hide'][Model_Filter::WEIGHT] = TRUE; // фильтр по весу - всегда скрыт
        $menu['hide'][Model_Filter::TOYS_AGE] = TRUE; // фильтр в игрушках для плашки - всегда скрыт

        return $menu;
    }

    /**
     * Получить левое меню с помеченными параметрами поиска
     * @return string html левого меню
     */
    function menu()
    {
        if (empty($this->_menu)) {
            $this->_menu = $this->stats();
        }
        $menu = $this->_exclude_empty($this->_menu);

        return View::factory('smarty:common/menu', $menu + [
            'mode' => $this->_mode,
            'query' => $this->_query,
            'params' => $this->_params,
            'sphinx' => $this,
            'is_checked' => $this->_mode == 'tag' && Request::current()->query(), // скрыть прямые ссылки
            'section' => $this->_section,
            'toggler' => json_decode(Session::instance()->get('toggle_state'), TRUE),
        ])->render();
    }

    /**
     * Получить параметры текущего запроса
     * @return array
     */
    function params()
    {
        return $this->_params;
    }

    /**
     * чтение и подмешивание параметров поиска из $_GET
     * тут же происходит редирект на теговую ЧПУ ссылку, если такая существует
     */
    private function read_params()
    {
        static $fv_redirect = [ // старые фильтры => новые (с сохранением ид фильтра)
            19917 => 18684,
            19915 => 18688,
            19914 => 18688,
            19913 => 18688,
            19864 => 18688,
            19865 => 18688,
            19918 => 18688,
            19866 => 18682,
            19916 => 18683,
            19912 => 18684,
            19853 => 18684,
            18681 => 18685,
            18689 => 18685,
            18690 => 18682,
            18904 => 18683,
            18693 => 18684,
            18691 => 18684,
            18694 => 18688,
            18903 => 18688,
            18902 => 18688,
            18687 => 18688,
        ];

        $request = Request::current();
        $redir = FALSE;
        $stats = $this->stats();

        foreach($request->query() as $k => $v) {
            switch ($k) {
                case 'b': // брэнд
                    $this->_params['b'] = array_filter(explode('_', $v), 'ctype_digit');
                    sort($this->_params['b']);

                    foreach ($this->_params['b'] as $key => $b) {
                        if (empty($stats['brands'][$b])) {
                            $redir = TRUE;
                            unset($this->_params['b'][$key]);
                        }
                    }
                    if ( ! empty($this->_params['b'])) $this->qs = TRUE;
                    break;

                case 'c': // раздел
                    $this->_params['c'] = array_filter(explode('_', $v), 'ctype_digit');
                    sort($this->_params['c']);

                    foreach ($this->_params['c'] as $key => $c) {
                        if (empty($stats['sections'][$c])) {
                            $redir = TRUE;
                            unset($this->_params['c'][$key]);
                        }
                    }
                    if ( ! empty($this->_params['c'])) $this->qs = TRUE;
                    break;

                case 'co': // страна
                    $this->_params['co'] = array_filter(explode('_', $v), 'ctype_digit');
                    sort($this->_params['co']);

                    foreach ($this->_params['co'] as $key => $c) {
                        if (empty($stats['countries'][$c])) {
                            $redir = TRUE;
                            unset($this->_params['co'][$key]);
                        }
                    }
                    if ( ! empty($this->_params['co'])) $this->qs = TRUE;
                    break;

                case 'pr': // цена - принимаем всегда
                    $p = array_filter(explode('-', $v, 2), 'ctype_digit');
                    if ( ! empty($p)) {
                        if (empty($p[1])) {
                            $this->_params['pr'] = [0, $p[0]]; // только макс цена
                        } elseif (empty($p[0])) {
                            $this->_params['pr'] = [0, $p[1]]; // только макс цена
                        } elseif ($p[1] >= $p[0]) {
                            $this->_params['pr'] = [$p[0], $p[1]];
                        }
                    }
                    break;

                case 'weight': // вес - бывает в подгузниках
                    if ( ! empty($this->_section) && $this->_section->id == Model_Section::DIAPERS_ID) {
                        $p = array_filter(explode('-', $v, 2), 'ctype_digit');
                        if ( ! empty($p)) {
                            if (empty($p[0])) $p[0] = 0;
                            if (empty($p[1])) $p[1] = 0;

                            if ($p[0] > $p[1]) { // swap values
                                $swap = $p[1];
                                $p[1] = $p[0];
                                $p[0] = $swap;
                            }
                            if ($p[1] == 0) { // пустой вес
                                $redir = TRUE;
                            } else {
                                $this->_params['weight'] = array($p[0], $p[1]);

                                if ($this->_params['weight'][0] < 0) {
                                    $this->_params['weight'][0] = 0;
                                    $redir = TRUE;
                                }
                                if ($this->_params['weight'][1] > 35) {
                                    $this->_params['weight'][1] = 35;
                                    $redir = TRUE;
                                }
                                if (($this->_params['weight'][0] == 0) and ($this->_params['weight'][1] == 35)) {
                                    $redir = TRUE;
                                    unset($this->_params['weight']);
                                }
                            }
                        }
                    }
                    break;

                case 'pp': // на странице
                    $this->_params['pp'] = intval($v);
                    if ( ! in_array($this->_params['pp'], $this->_params['per_page'])) { // на странице стоит неразрешённое число - сбрасываем
                        $redir = TRUE;
                        unset($this->_params['pp']);
                    }
                    break;

                case 's': // сортировка
                    if (in_array($v, $this->_params['sorts'])) {
                        $this->_params['s'] = $v;
                    } else { // не разрешённая сортировка - сбрасываем
                        $redir = TRUE;
                        unset($this->_params['s']);
                    }
                    break;

                case 'a': // акция
                    if ( ! empty($this->_params['a']) && ctype_digit($this->_params['a'])) { // мы уже на странице акции - сбросим параметр a
                        $redir = TRUE;
                        unset($this->_params['a']);
                    } else {
                        if ($v == 1) {
                            $this->_params['a'] = TRUE;
                        } else { // не разрешённый флаг акции - сбрасываем
                            $redir = TRUE;
                            unset($this->_params['a']);
                        }
                    }
                    break;

                default:
                    if (preg_match('~^f(\d+)$~', $k, $matches)) { // фильтры
                        $fid = $matches[1];
                        if (Model_Filter::begunok($fid) && preg_match('~^([0-9\.]+)-([0-9\.]+)$~', $v)) {
                            $this->_params['f'][$fid] = $v;
                        } else {
                            $vals = array_filter(explode('_', $v), 'ctype_digit');
                            if ( ! empty($vals)) $this->_params['f'][$fid] = $vals;
                        }
                    }
                    if ( ! empty($this->_params['f'])) $this->qs = TRUE;
                    break;
            }
        }

        if ( ! empty($this->_params['f'])) {
            foreach ($this->_params['f'] as $fid => $vals) { // выкидываем некорректные фидьтры

                if ($fid == Model_Filter::TASTE) continue; // эти пропускаем

                if (Model_Filter::begunok($fid)) { // в бегунках проверяем чтобы не вышли за границы возможного
                    if (is_string($vals)) {

                        $min = $max = FALSE;
                        foreach ($stats['vals'][$fid] as $fv) {
                            if ($min === FALSE) $min = floatval($fv['name']);
                            if ($max === FALSE) $max = floatval($fv['name']);
                            $max = max($max, floatval($fv['name']));
                            $min = min($min, floatval($fv['name']));
                        }

                        // проверим максимум и минимум
                        list($from, $to) = explode('-', $vals);
                        if ($from < $min) {
                            $from = $min;
                            $redir = TRUE;
                        }
                        if ($to > $max) {
                            $to = $max;
                            $redir = TRUE;
                        }
                        $this->_params['f'][$fid] = $from . '-' . $to;

                        if ($from == $min && $to == $max) {
                            $redir = TRUE;
                            unset($this->_params['f'][$fid]);
                        }
                    }
                    continue;
                }

                // в урле категории один подфильтр от фильтра-категории - редирект на страницу фильтра-категории
                if ($this->_mode == 'section'
                    && $this->_section->settings['list'] == Model_Section::LIST_FILTER
                    && $fid == $this->_section->settings['sub_filter']
                    && count($vals) == 1
                ) {
                    $fv = current($vals);
                    $this->_mode = 'section_filter';
                    $this->_query = $fid.'_'.$fv;
                    $redir = TRUE;
                    unset($this->_params['f'][$fid]);
                };

                foreach ($vals as $key => $fv) {
                    if (empty($stats['vals'][$fid][$fv]) && $fid != Model_Filter::TASTE) {  // выбран фильтр которого нет в категории
                        $redir = TRUE;
                        unset($this->_params['f'][$fid][$key]);
                    }

                    if ( ! empty($fv_redirect[$fv])) { // редиректим старые
                        $redir = TRUE;
                        unset($this->_params['f'][$fid][$key]);
                        $this->_params['f'][$fid][$fv_redirect[$fv]] = $fv_redirect[$fv];
                    }

                    // если выбран зависимый фильтр а хозяин не выбран - скинем такой фильтр
                    if ($id = Model_Filter::binded_to($fid)) {

                        $unset = $fid;
                        if ( ! empty($this->_params['b']) && in_array($id, $this->_params['b'])) $unset = FALSE;

                        if ( ! empty($this->_params['f'])) { // ищем в фильтрах

                            $found = FALSE;
                            foreach ($this->_params['f'] as $f_id => $values) {
                                if (in_array($id, $values)) {
                                    $found = $f_id;
                                    break;
                                }
                            }
                            if ($found) $unset = FALSE;
                        }

                        if ($unset) {
                            $redir = TRUE;
                            unset($this->_params['f'][$unset]);
                        }
                    }
                }
            }
        }

        // значения по умолчанию
        if ( ! isset($this->_params['pp'])) {
            if ( ! empty($this->_params['per_page'][0])) {
                $this->_params['pp'] = $this->_params['per_page'][0];
            } else {
                $this->_params['pp'] = 20;
            }
        }
        if ( ! isset($this->_params['s'])) $this->_params['s'] = $this->_params['sorts'][0];

        $href = $this->href();

        if ($redir || (in_array($this->_mode, ['section', 'section_filter']) && parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != parse_url($href, PHP_URL_PATH))) {
            $request->redirect($href, 301); // редирект
        }

        return $this->_params;
    }

    /**
     * Добавить параметр сфинксу
     * @param $name
     * @param $value
     */
    public function param($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     *  Добавить параметры поиска к запросу
     */
    private function _apply_params(Database_Query_Builder_Select $q, $params, $no_order = FALSE)
    {
        // секции
        if ( ! empty($params['c'])) $q->where('section_id', 'IN', array_map('intval', $params['c']));

        // брэнды
        if ( ! empty($params['b'])) $q->where('brand_id', 'IN', array_map('intval', $params['b']));

        // страна
        if ( ! empty($params['co'])) $q->where('country_id', 'IN', array_map('intval', $params['co']));

        // цена (в сфинксе в копейках)
        if ( ! empty($params['pr'])) {
            if (isset($params['pr'][0] )) $q->where('price', '>=', $params['pr'][0] * 100);
            $q->where('price', '<=', $params['pr'][1] * 100);
        }

        // вес для подгузников - преобразовывается в значения самодельного (нет в 1с) фильтра Model_Filter::WEIGHT
        if ( ! empty($params['weight'])) {
            $q->where('fvalue', 'IN', array_map('intval', Model_Filter_Value::weight($params['weight']))); // группируем по фильтрам
        }

        // значения фильтров
        $filter_groups = $binded_values = $filter_section = []; // параметры от связанных фильтров собираем в одну группу where, иначе условия поиска будут взаимоисключающими
        if ( ! empty($params['f'])) {

            // для всех фильтров найдем их категории, фильтры в разных категориях нужно соединять в одном запросе (нужно для перекрестных теговых)
            $filters = ORM::factory('filter')
                ->where('id', 'IN', array_keys($params['f']))
                ->find_all()
                ->as_array('id');


            foreach($params['f'] as $fid => $values) {

                if ($fid == Model_Filter::TASTE) { // только выбранные вкусы
                    $tastes = [];
                    foreach ($this->stats()['vals'] as $tid => $tvals) {
                        if (Model_Filter::taste($tid)) {
                            foreach ($tvals as $id => $val) {
                                if ( ! empty($params['f'][$tid]) && in_array($id, $params['f'][$tid])) { // этот вкус выбран
                                    unset($tvals[$id]);
                                }
                            }
                            $tastes = array_merge_recursive($tastes, array_keys($tvals));
                        }
                    }

                    if ( ! empty($tastes)) {
                        foreach ($tastes as $t) {
                            $q->where('fvalue', 'NOT IN', [intval($t)]);
                        }
                    }
                } elseif ($fid == Model_Filter::STROLLER_WEIGHT) { // вес для колясок

                    if (is_string($values)) { // передан интервал от - до - умножаем на 10 - в индексе int

                        $sw = explode('-', $values);
                        $sw[0] = empty($sw[0]) ? 1 : round(floatval($sw[0]) * 10);
                        $sw[1] = empty($sw[1]) ? 1000 : round(floatval($sw[1]) * 10);

                        $q  ->where('stroller_weight', '>=', intval($sw[0]))
                            ->where('stroller_weight', '<=', intval($sw[1]));

                    } elseif (is_array($values)) { // искусственные интервалы - надо заменить на набор id значений фильтров попадающих в интервал

                        $vals_ids = [];
                        $intervals = Model_Filter::begunok($fid)['settings'];
                        foreach($values as $int_id) {
                            if ( ! empty(Model_Filter::begunok($fid)['settings'][$int_id])) {
                                $min = $intervals[$int_id]['min'];
                                $max = $intervals[$int_id]['max'];
                                //echo $min.' < '.$max.' = ';
                                foreach(ORM::factory('filter_value')->where('filter_id', '=', $fid)->find_all()->as_array('id', 'name') as $id => $name) {
                                    if (floatval($name) >= $min && floatval($name) <= $max) {
                                        $vals_ids[] = $id;
                                    }
                                }
                            }
                        }
                        if ( ! empty($vals_ids)) {
                            $q->where('fvalue', 'IN', array_map('intval', $vals_ids));
                        }
                    }

                } elseif ($fid == Model_Filter::STROLLER_SHASSI) { // ширина шасси для колясок


                    if (is_string($values)) { // передан интервал от - до - умножаем на 10 - в индексе int

                        $ss = explode('-', $values);
                        $ss[0] = empty($ss[0]) ? 1 : intval($ss[0]);
                        $ss[1] = empty($ss[1]) ? 100 : intval($ss[1]);

                        $q  ->where('stroller_shassi', '>=', intval($ss[0]))
                            ->where('stroller_shassi', '<=', intval($ss[1]));

                    } elseif (is_array($values)) { // искусственные интервалы - надо заменить на набор id значений фильтров попадающих в интервал

                        $vals_ids = [];
                        $intervals = Model_Filter::begunok($fid)['settings'];
                        foreach($values as $int_id) {
                            if ( ! empty($intervals[$int_id])) {
                                $min = $intervals[$int_id]['min'];
                                $max = $intervals[$int_id]['max'];
                                //echo $min.' < '.$max.' = ';
                                foreach(ORM::factory('filter_value')->where('filter_id', '=', $fid)->find_all()->as_array('id', 'name') as $id => $name) {
                                    if (floatval($name) >= $min && floatval($name) <= $max) {
                                        $vals_ids[] = $id;
                                    }
                                }
                            }
                        }
                        if ( ! empty($vals_ids)) {
                            $q->where('fvalue', 'IN', array_map('intval', $vals_ids));
                        }
                    }

                } elseif ($fid == Model_Filter::VOLUME_KG) { // вес в быт-химии

                    if (is_string($values)) { // передан интервал от - до - умножаем на 100 - в индексе int

                        $ss = explode('-', $values);
                        $ss[0] = empty($ss[0]) ? 1 : intval($ss[0] * 100);
                        $ss[1] = empty($ss[1]) ? 10000 : intval($ss[1] * 100); // максимум 100 кг

                        $q->where('v_kg', '>=', $ss[0])
                            ->where('v_kg', '<=', $ss[1]);
                    }


                } elseif ($fid == Model_Filter::VOLUME_LITR) { // объем в быт-химии

                    if (is_string($values)) { // передан интервал от - до - умножаем на 100 - в индексе int

                        $ss = explode('-', $values);
                        $ss[0] = empty($ss[0]) ? 1 : intval($ss[0] * 100);
                        $ss[1] = empty($ss[1]) ? 10000 : intval($ss[1] * 100);  // максимум 100 л

                        $q->where('v_litr', '>=', $ss[0])
                            ->where('v_litr', '<=', $ss[1]);
                    }


                } else {

                    $values = array_filter($values);
                    if ( ! empty($values)) {
                        if (($bid = Model_Filter::binded_to($fid)) && ($bid > 50000)) {
                            // если выбран зависимый от бренда фильтр то для всех других брендов прикрепить фейковый фильтр = id бренла или они не появятся в выборке
                            if ( ! empty($params['b'])) {
                                foreach ($params['b'] as $b) {
                                    if ($b != $bid) $binded_values[] = $b;
                                }
                            }

                            $binded_values = array_merge($binded_values, $values);

                        } else {
                            // отделим значения типа AND
                            foreach ($values as $k => $v) {
                                if (Model_Filter::and_val($v)) {
                                    $q->where('fvalue', 'IN', [intval($v)]);
                                    unset($values[$k]);
                                }
                            }
                            if ( ! empty($values)) {
                                if ($filters[$fid]->section_id && empty($filter_section[$filters[$fid]->section_id]) && ! empty($filter_groups)) {
                                    $last_group = count($filter_groups) - 1;
                                    $filter_groups[$last_group] = array_merge($filter_groups[$last_group], array_map('intval', $values)); // добавляем в последнюю группу
                                } else { // эта категория уже была, добавляем новую
                                    $filter_groups[] = array_map('intval', $values); // добавляем в последнюю группу
                                }
                                $filter_section[$filters[$fid]->section_id] = $filters[$fid]->section_id;
                            } // обычные фильтры, группируем значения по фильтрам - будет поиск (vid OR vid) AND (vid OR vid)
                        }
                    }
                }
            }
        }

        foreach($filter_groups as $values) {
            $q->where('fvalue', 'IN', array_map('intval', $values));
        }

        if ( ! empty($binded_values)) {
            $q->where('fvalue', 'IN', array_map('intval', $binded_values)); // группируем собранные отдельно
        }

        // флаг акции
        if ( ! empty($params['a'])) {
            if ($params['a'] === TRUE) {
                $q->where('action_id', '>', 0);
            } else {
                $q->where('action_id', '=', $params['a']);
            }
        }

        // скидка
        if ( ! empty($params['d'])) $q->where('discount', '=', 1);

        // суперцена
        if ( ! empty($params['su'])) $q->where('superprice', '=', 1);

        // хиты продаж
        if ( ! empty($params['h'])) $q->where('hit', '=', 1);

        // новинка
        if ( ! empty($params['n'])) $q->where('nt', '!=', 0);

        // наличие
        if ( ! empty($params['x'])) $q->where('x', '=', 1);

        // сортировка
        if ( ! $no_order) {         
            //показывать сначала товары в наличии
            $q->order_by('x', 'DESC');
           
            if ( ! empty($params['s'])) {
                $sorts = [
                    'new'       => '-nt',
                    'rating'    => '-popularity',
                    'name'      => 'alphabet',
                    'price'     => 'price',
                    'pricedesc' => '-price',
                    'pricepack'     => 'price',
                    'pricepackdesc' => '-price',
                    'priceitem'     => 'priceitem',
                    'priceitemdesc' => '-priceitem',
                ];
                if ( ! empty($sorts[$params['s']])) {
                    $sort = $sorts[$params['s']];
                    $q->order_by(trim($sort, '-'), substr($sort, 0, 1) === '-' ? 'DESC' : 'ASC');
                };
            }
        }

        if ( ! empty($params['g'])) $q->where('id', 'IN', $params['g']);  // есть список ID товаров  - проверить где используется (памперс?)

        // поисковое слово
        if ( ! empty($params['q'])) {

            if ($this->_mode != 'suggest') {
                $_q = $params['q'];
            } else {
                $aq = explode(' ', $params['q']);
                if (strlen($aq[count($aq) - 1]) < 3) {
                    $_q = $params['q'];
                } else {
                    $_q = $params['q'].'*';
                }

            }
            $q->where(DB::expr('MATCH('), DB::query(Database::SELECT, ':query')->param(':query', Txt::escapeSphinx($_q)), DB::expr(')'));
        }
        return $q;
    }

    /**
     * Получение списка групп и товаров, для показа результатов поиска, товаров удовлетворяющих запросу ( получаем из сфинкса )
     * @return string HTML c результатами поиска
     */
    function search()
    {
        if (empty($this->_menu)) { // отсюда мы берем списки возможных брендов-категорий-фильтров-цен
            $this->_menu = $this->stats();
        }

        $pager = new Pager(FALSE, $this->_params['pp']);

        $q = DB::select('id', 'grouped', 'group_id')
            ->from('goods_zukk')
            ->limit($pager->offset . ',' . $pager->per_page);

        if ($this->_mode !== 'action') $q->where('shop_id', 'IN', [self::shop_id()]); // везде кроме акций учитывается витрина

        $q = $this->_apply_params($q, $this->_params);

        $result = Database::instance('sphinx')
            ->query(Database::SELECT, strval($q))
            ->as_array('id');

		$ga_list = empty($this->_params['q']) ? 'category' : 'search';

//        echo Database::instance('sphinx')->last_query;

		$grouped = FALSE;
        $gids = $group_goods = array(); // ид товаров и списки групп
        if ( ! empty($result)) {

            $meta = $this->meta();
            $this->found = $meta['total'];
            $this->pager = new Pager($meta['total'], $this->_params['pp']);

            foreach ($result as $id => $data) {
                if ($data['grouped']) {
					$grouped = TRUE;
                    $group_goods[$data['group_id']][] = $id;
                    $gids[] = $id;
                } else {
                    $gids[] = $id;
                }
            }
            $grids = array_keys($group_goods);

            if ( ! empty($grids)) { // есть товары - группы, получим их группы
                $groups = ORM::factory('group')->where('id', 'IN', $grids)->find_all()->as_array('id'); // группы для вывода
            }

            $goods = ORM::factory('good')->where('id', 'IN', $gids)->find_all()->as_array('id'); // товары для вывода

            // собираем результат в том же порядке, в каком был результат запроса
            $this->goods = array();
            foreach($result as $id => $data) {
                $g = $goods[$id];
                if ($data['grouped']) { // это товар-группа (одежда)
                    $g->review_qty = $groups[$g->group_id]->review_qty; // рейтинги группы
                    $g->rating = $groups[$g->group_id]->rating; // рейтинги группы
                    $g->grouped = $groups[$g->group_id]->qty; // число товаров в группе
                }
                $this->goods[$id] = $g;
            }
        }

        if ( ! empty($with_other_shop)) {// результаты такого же запроса на другой витрине, 4 товара
            $q_ov = DB::select('id', 'grouped')
                ->from('goods_zukk')
                ->where('shop_id', 'IN', [self::get_other_shop_id()])
                ->where('x', '!=', 0)
                ->where(DB::expr('MATCH('), DB::expr(DB::query(Database::SELECT, ':query')->param(':query', $this->_params['q'])), DB::expr(')'))
                ->limit('0,4');
            
            $result_other_shops = Database::instance('sphinx')->query(Database::SELECT, strval($q_ov))->as_array('id', 'id');

            if ( ! empty($result_other_shops)) {
                $meta = $this->meta();
            
                $this->other_shop_goods = ORM::factory('good')
                    ->where('id', 'IN', $result_other_shops)
                    ->find_all()
                    ->as_array('id');

                $this->other_shop_goods_counter = $meta['total'];
            }
        }
        
		$gids = array_merge($gids, array_keys($this->other_shop_goods));
        $this->load_data($gids);

        $has_section = ! empty($this->_section->id) ? $this->_section->id : 0;

        return View::factory('smarty:common/groups', [
            'sphinx'    => $this,
            'goods'     => $this->goods,
            'images'    => $this->images,
            'good_action' => $this->actions,
            'price'     => $this->price,
            'pager'     => $this->pager,
            'params'    => $this->_params,
            'section'   => $has_section,
            'row'       => $has_section ? $this->_section->settings['row'] : 3, // число в ряд
            'menu'      => $this->_menu,
            'mode'      => $this->_mode,
            'is_cloth'  => $this->is_cloth(),
            'colorsize' => ( ! empty($grouped)) ? Model_Good::get_color_size(array_keys($group_goods), $gids) : [],
			'ga_list'   => $ga_list,
            'is_diapers'=> $has_section == Model_Section::DIAPERS_ID,
            'per_pack'  => in_array($has_section, [Model_Section::DIAPERS_ID, Model_Section::LINEN_ID]),
            'show_qty'  => $this->_mode == 'action' && $this->_query == 192982,
        ])->render();
    }

    /**
     * Попытка автокомплита слова
     * @return grouped
     */
    function suggestion()
    {
        $q = DB::select('id')
            ->from('goods_suggest')
            ->limit(10)
            ->where('shop_id', 'IN', [self::shop_id()]);

        // print_r($this->_params);

        $q = $this->_apply_params($q, $this->_params);

        $result = Database::instance('sphinx')
            ->query(Database::SELECT, strval($q))
            ->as_array('id');

        // echo Database::instance('sphinx')->last_query;

		$grouped = FALSE;
        $gids = $group_goods = array(); // ид товаров и списки групп

        if ( ! empty($result)) {

            $meta = $this->meta();

            foreach ($result as $id => $data) $gids[] = $id;

            $goods = ORM::factory('good')->where('id', 'IN', $gids)->find_all()->as_array('id'); // товары для вывода

			$ws = [];
			foreach( $meta as $key => $value ){
				
				if (preg_match('#^keyword\[#iu', $key)) $ws[] = $value;
			}
			
			$ws = implode( '|', $ws );
			
            // собираем результат в том же порядке, в каком был результат запроса
            $this->goods = array();
            foreach($result as $id => $data) {
                $g = $goods[$id];
				
				if ( ! empty($ws)) {
					$g->name = preg_replace('#(' . $ws . ')#ius', '<span style="background: rgb(255, 255, 125)">\\1</span>', $g->name );
					$g->group_name = preg_replace('#(' . $ws . ')#ius', '<span style="background: rgb(255, 255, 125)">\\1</span>', $g->group_name );
				}
                $this->goods[$id] = $g;
            }
        }

        $this->load_data($gids);

		return View::factory('smarty:common/suggestions', [
            'sphinx'    => $this,
            'goods'     => $this->goods,
            'images'    => $this->images,
            'price'     => $this->price,
            'mode'      => $this->_mode,
            'is_cloth'  => $this->is_cloth(),
            'colorsize' => ( ! empty($grouped)) ? Model_Good::get_color_size(array_keys($group_goods), $gids) : [],
        ])->render();
    }
	
	/**
     * Получение данных по ценам для любимых, картинкам, участию в акциях - для списка id товаров
     * @param array $good_ids
     */
    private function load_data($good_ids)
    {
        if ( empty($good_ids)) return;
        
        $this->price    = Model_Good::get_status_price(1, $good_ids);           // цены для любимых - одним запросом
        $this->images   = Model_Good::many_images(array(255, 70, '173x255'), $good_ids);   // картинки товаров - одним запросом
        $this->actions  = Model_Action::for_icons($good_ids);
    }

    /**
     * Получение данных по фильтрам в одежде - для показа названий позиций
     * @param $groups
     * @return array
     */
    private function _load_cloth($groups, $gids)
    {
        $result = DB::select()
            ->from('z_good_filter')
            ->where('filter_id', 'IN', [
                Controller_Product::FILTER_COLOR,
                Controller_Product::FILTER_GROWTH,
                Controller_Product::FILTER_SIZE,
                Controller_Product::FILTER_TYPE])
            ->where('good_id', 'IN', $gids) // ограничиваем всеми товарам групп
            ->execute();

        $returner = [];
        while ($row = $result->current()) {
            $returner[$row['good_id']][$row['filter_id']] = $row['value_id'];
            $result->next();
        }

        $groupValues = [];

        foreach( $groups as $groupId => $goodIds ){
            if (empty($groupValues[$groupId])) $groupValues[$groupId] = [];

            foreach ($goodIds as $gId ) {
                if ( ! empty($returner[$gId]) && is_array($returner[$gId])) {
                    foreach ($returner[$gId] as $fId => $vId) {
                        $groupValues[$groupId][$fId] = $vId;
                    }
                }
            }
        }

        $return = ['groupValues' => $groupValues, 'goodValues' => $returner, 'groups' => $groups];
        return $return;

    }
    
    /**
     * Если все категории в меню - одежда, то для результатов поиска будет шаблон одежды
     */
    public function is_cloth()
    {
        $cloth_subs = Model_Section::get_cloth_subs();

        if (empty($this->_menu)) {
            $this->_menu = $this->stats();
        }
        if (empty($this->_menu['sections'])) return FALSE; // нет категорий поиска => не одежда
        do { // проверим чтобы все категории были из одежды
            $section_id = key($this->_menu['sections']);
            if (empty($cloth_subs[$section_id])) return FALSE;
        } while(next($this->_menu['sections']) !== FALSE);

        return TRUE;
    }

    /**
     * Получить поисковую категорию
     * @return Model_Section
     */
    function section()
    {
        return $this->_section;
    }

    /**
     * получить ссылку для теговой из параметров поиска, учитываются только параметры c,b,f
     * @param $change - массив изменений ключ => значение, по ним из ссылки убираются/добавляются параметры
     * @return string
     */
    function href($change = [])
    {
        if ($change == self::HREF_INIT) {
            switch($this->_mode) {

                case 'word':
                    return Route::url('search').'?q='.$this->_query;
                case 'action':
                    return Route::url('action', ['id' => $this->_query]);
                case 'actions':
                    return Route::url('action', ['id' => $this->_query]);

                case 'section':
                    return $this->_section->get_link(0);
                case 'tag':
                    if ( ! empty($this->_section)) {
                        return $this->_section->get_link(0);
                    } else {
                        return Route::url('tag', $this->_tag->as_array());
                    }
                case 'section_filter':
                    return $this->_section->get_link(0, substr($this->_query, strpos($this->_query, '_') + 1));

                case 'new':
                    return Route::url('novelty');

                case 'superprice':
                case 'discount':
                case 'pampers':
                case 'hitz':
                    return Route::url($this->_mode);
            }
        }

        $return = [];

        if ( ! empty($this->_params['b']) || ! empty($change['b'])) { // бренды
            $return['b'] = ! empty($this->_params['b']) ? $this->_params['b'] : [];
            if ( ! empty($change['b'])) {
                foreach ($change['b'] as $b) {
                    if ($b > 0) $return['b'][] = $b;
                    if ($b < 0) $return['b'] = array_filter($return['b'], function($v) use ($b) { return $b + $v;});
                }
            }
            $return['b'] = array_unique($return['b']);
            sort($return['b']);
            $return['b'] = implode('_', $return['b']);
        }

        if ( ! empty($this->_params['co']) || ! empty($change['co'])) { // страны
            $return['co'] = ! empty($this->_params['co']) ? $this->_params['co'] : [];
            if ( ! empty($change['co'])) {
                foreach ($change['co'] as $co) {
                    if ($co > 0) $return['co'][] = $co;
                    if ($co < 0) $return['co'] = array_filter($return['co'], function($v) use ($co) { return $co + $v;});
                }
            }
            $return['co'] = array_unique($return['co']);
            sort($return['co']);
            $return['co'] = implode('_', $return['co']);
        }

        if ( ! empty($this->_params['c']) || ! empty($change['c'])) { // категории
            $return['c'] = ! empty($this->_params['c']) ? $this->_params['c'] : [];
            if ( ! empty($change['c'])) {
                foreach ($change['c'] as $b) {
                    if ($b > 0) $return['c'][] = $b;
                    if ($b < 0) $return['c'] = array_filter($return['c'], function($v) use ($b) { return $b + $v;});
                }
            }
            $return['c'] = array_unique($return['c']);
            sort($return['c']);
            $return['c'] = implode('_', $return['c']);
        }

        if ( ! empty($this->_params['f']) || ! empty($change['f'])) { // фильтры
            if ( ! empty($this->_params['f'])) {
                foreach ($this->_params['f'] as $fid => $vids) {
                    $return['f' . $fid] = $vids;
                    if ( ! is_string($vids)) { // для бегунков тут может быть строка MIN-MAX
                        if ( ! empty($change['f'][$fid])) {
                            foreach ($change['f'][$fid] as $vid) {
                                if ($vid > 0) $return['f' . $fid][] = $vid;
                                if ($vid < 0) $return['f' . $fid] = array_filter($return['f' . $fid], function($v) use ($vid) { return $vid + $v;});
                            }
                            unset($change['f'][$fid]); // чтобы не добавить потом в строку
                        }

                        $return['f' . $fid] = array_unique($return['f' . $fid]);
                        sort($return['f' . $fid]);
                        $return['f' . $fid] = implode('_', $return['f' . $fid]);
                    } else {

                    }
                }
            }
            if ( ! empty($change['f'])) {
                foreach ($change['f'] as $fid => $ch) { // добавим новые фильтры
                    foreach ($ch as $vid) {
                        if ($vid >= 0) {
                            if (empty($return['f' . $fid]) || is_array($return['f' . $fid])) {
                                $return['f' . $fid][] = $vid;
                            } else {
                                if ($vid == 0) { // для строковых непустых значений (бегунки) - сбросить все если в change = [0]
                                    unset($return['f' . $fid]);
                                } else {
                                    $return['f' . $fid] = [$vid];
                                }
                            }
                        };
                    }
                    if ( ! empty($return['f' . $fid])) {
                        $return['f' . $fid] = array_unique($return['f' . $fid]);
                        sort($return['f' . $fid]);
                        $return['f' . $fid] = implode('_', $return['f' . $fid]);
                    }
                }
            }
        }
        ksort($return);
        $return = array_filter($return);

        if ( ! empty($this->_params['q']) && $this->_mode == 'word') {
            $return['q'] = $this->_params['q'];
        }

        if ( (empty($this->_params['a']) || ! ctype_digit($this->_params['a'])) && ! empty($this->_params['a']) != isset($change['a'])) {
            $return['a'] = 1;
        }

        $href = '';
        if ( ! empty($this->_section) && $this->_mode != 'pampers') { // пробуем подобрать теговую

            $filter = 0;
            if ($this->_mode == 'section_filter') { // фильтер нужен для ссылки
                list($id, $filter) = explode('_', $this->_query);
            }
            $href = $this->_section->tag($return, $filter);

        } elseif ($this->_mode == 'pampers') { // для памперса вытираем из урла b и с

            $href = Route::url('pampers');
            unset($return['b']);
            if (isset($return['c']) && $return['c'] == Model_Good::PAMPERS_SECTION) {
                unset($return['c']);
            }
        }

        // параметры, которые не влияют на основные условия запроса
        if ( ! empty($this->_params['pr']) && empty($change['pr'])) { // это очистка pr (цены)
            $return['pr'] = $this->_params['pr'][0].'-'.$this->_params['pr'][1];
        }

        if ( ! empty($this->_params['weight']) && empty($change['weight'])) { // это очистка веса
            $return['weight'] = $this->_params['weight'][0].'-'.$this->_params['weight'][1];
        }

        if ( ! empty($this->_params['pp']) && empty($change['pp'])
            && $this->_params['per_page'][0] != $this->_params['pp'] && in_array($this->_params['pp'], $this->_params['per_page']))
        {
            $return['pp'] = $this->_params['pp'];
        }
        if ( ! empty($this->_params['s']) && empty($change['s'])
            && $this->_params['sorts'][0] != $this->_params['s'] && in_array($this->_params['s'], $this->_params['sorts']))
        {
            $return['s'] = $this->_params['s'];
        }
        ksort($return);

        if (empty($href) && ! empty($_SERVER['REQUEST_URI'])) $href = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $href.( ! empty($return) ? '?'.http_build_query($return) : '');
    }

    /**
     * Генерация из параметров запроса строк для title, keywords, description
     */
    function seo()
    {
        $words = $kw = [];

        $names = $this->stats();

        if ( ! empty($this->_params['c'])) { // категория
            foreach ($this->_params['c'] as $cid) {
                if ( ! empty($names['sections'][$cid])) {
                    $words['c'][] = $names['sections'][$cid]['name'];
                    $kw[] = $names['sections'][$cid]['name'];
                }
            }
        }
        
        $count_selected_brands = 0;
        if ( ! empty($this->_params['b'])) { // бренд
            foreach ($this->_params['b'] as $bid) {
                if ( ! empty($names['brands'][$bid])) {
                    $words['b'][] = $names['brands'][$bid]['name'];
                    $kw[] = $names['brands'][$bid]['name'];
                }
                $count_selected_brands++;
            }
        }

        $count_selected_countries = 0;
        if ( ! empty($this->_params['co'])) { // страна
            foreach ($this->_params['co'] as $bid) {
                if ( ! empty($names['countries'][$bid])) {
                    $words['co'][] = $names['countries'][$bid]['name'];
                    $kw[] = $names['countries'][$bid]['name'];
                }
                $count_selected_countries++;
            }
        }

        $count_selected_filters = array();
        if ( ! empty($this->_params['f'])) { // фильтры
            foreach ($this->_params['f'] as $fid => $vals) {
                $count_selected_filters[$fid] = 0;
                if ( ! Model_Filter::big($fid) && is_array($vals)) {
                    foreach($vals as $v) {
                        if ( ! empty($names['vals'][$fid][$v])) {
                            $words['f'][] = $names['vals'][$fid][$v]['name'];
                            $kw[] = $names['vals'][$fid][$v]['name'];
                            $count_selected_filters[$fid]++;
                        }
                    }
                }
            }
        }
        
        //правила для исключения из индекса страниц с несколькими фильтрами
        $max_filters = count($count_selected_filters) > 0 ? max($count_selected_filters) : 0;
        if ($count_selected_countries > 1 || $count_selected_brands > 1 || $max_filters > 1 || ($count_selected_brands + count($count_selected_filters)) > 2)
        {            
            $return['robots'] = 'noindex,nofollow';
        } 

        $word = ( ! empty($words['c']) ? implode(', ', $words['c']) : '') .
                ( ! empty($words['b']) ? ' / '.implode(', ', $words['b']) : '') .
                ( ! empty($words['co']) ? ' / '.implode(', ', $words['co']) : '') .
                ( ! empty($words['f']) ?  ' / '.implode(', ', $words['f']) : '');

        $return['title'] = $word;
        $return['description'] = 'Интернет-магазин детских товаров Младенец.ру предлагает: '.$word.'. Большой ассортимент, низкие цены, доставка по всей России.';
        $return['keywords'] = implode(', ', $kw);

        return $return;
    }
}
