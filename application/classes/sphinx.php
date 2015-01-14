<?php

/**
 * Class sphinx
 * Обеспечивает общение со сфинксом, получение товаров для показа по различным запросам поиска, получение и кеш наборов данных для расширенного поиска
 */
class Sphinx {

    const SHOP_MLADENEC_ID = 1;
    const SHOP_EATMART_ID = 2;
    const SHOP_DEFAULT_ID = 0; // unused
    
    public $total = 0; // число результатов последнего поиска

    private $_mode = '';
    private $_query = '';
    private $_section = NULL; // если запрос относится к одной категории - тут категория
    private $_menu_params = array(); // параметры для меню,
    private $_params = array(); // параметры поиска,
    private $_stats = array(); // статистика поиска [бренды, категории, фильтры, цены]
    private $_menu = array(); // меню для поиска [бренды, категории, фильтры, цены]

    public $goods   = []; // результаты поиска по товарам (если упорядочено по цене)
    public $price   = []; // спец. цены для товаров из результатов поиска
    public $actions = []; // акции для товаров из результатов поиска
    public $images  = []; // картинки для товаров из результатов поиска
    public $pager   = []; // пажинатор для результатов поиска
    public $filters   = []; // ab
    public $hash    = ''; // хэш для пажинатора

    public $other_shop_goods            = array(); // Товары, найденные на других витринах
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

    /*
     * Читает настройки категории
     */
    private function _read_section($c)
    {
        $this->_section = new Model_Section($c);
        if ( ! $this->_section->loaded()) return FALSE;

        if ( ! empty($this->_section->settings['orderByItems'])) $this->_params['sorts'] = $this->_section->settings['orderByItems'];
        $this->_params['x'] = isset($this->_section->settings['x']) ? $this->_section->settings['x'] : 0;
        $this->_params['s'] = ! empty($this->_section->settings['s']) ? $this->_section->settings['s'] : 'rating';
        $this->_params['m'] = isset($this->_section->settings['m']) ? $this->_section->settings['m'] : 1;

        $this->_menu_params['c'] = [$this->_section->id];
        if ($this->_section->settings['x'] == Model_Section::HIDE_OUT_OF_STOCK_STRICTLY) $this->_menu_params['x'] = 1;
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
        if ( ! in_array($mode, array('section', 'section_filter', 'word', 'discount', 'superprice', 'pampers', 'tag', 'new', 'hitz', 'action'))) return FALSE;

        $this->_mode = $mode;
        $this->_query = $q;

        $this->_params['sorts'] = ["rating", "new", "price", "pricedesc"];
        $this->_params['per_page'] = [20, 40, 80];
        $this->_params['m'] = 1;
        $this->_params['x'] = 0;
        $this->_params['pp'] = 40;
        $this->_params['s'] = 'rating';

        switch ($this->_mode) {
            case 'word':
                $this->_params['q'] = self::correct_user_query($this->_query);
                $this->_menu_params['q'] = $this->_params['q'];
                break;

            case 'section':
            case 'section_filter':
                @list($sid, $f) = explode('_', $this->_query);
                $sid = intval($sid);
                if ( ! $this->_read_section($sid)) { // menu_params set inside _read_section
                    echo 'no section for ' . $this->_mode.':'.$sid;
                    throw new HTTP_Exception_404;
                }
                $this->_params['c'] = [intval($sid)];

                if ($this->_mode == 'section_filter') {

                    if ( ! $this->_section->is_cloth()) { // этот режим разрешён только для одежды
                        echo ('not cloth for ' . $this->_mode.':'.$sid);
                        throw new HTTP_Exception_404;
                    }

                    $val = new Model_Filter_Value($f);
                    if ( ! $val->loaded() || $val->filter_id != Model_Filter::CLOTH_BIG_TYPE) { // и только по большому типу
                        echo 'no filter for ' . $this->_mode.':'.$f;
                        throw new HTTP_Exception_404;
                    }
                    $this->_menu_params['f'] = $this->_params['f'] = [Model_Filter::CLOTH_BIG_TYPE => [$f]];
                }

                break;

            case 'discount':
                $this->_params['d'] = $this->_menu_params['d'] = 1;
                $this->_params['x'] = 2;
                $this->_menu_params['x'] = 1;
                break;

            case 'superprice':
                $this->_params['su'] = $this->_menu_params['su'] = 1;
                $this->_params['x'] = 2;
                $this->_menu_params['x'] = 1;
                break;

            case 'new':
                $this->_params['sorts'] = ["new", "rating", "price", "pricedesc"];
                $this->_params['n'] = $this->_menu_params['n'] = 1;
                $this->_params['x'] = 2;
                $this->_menu_params['x'] = 1;

                break;

            case 'hitz':
                $this->_params['h'] = $this->_menu_params['h'] = 1;
                $this->_params['x'] = 2;
                $this->_menu_params['x'] = 1;
                break;

            case 'tag':
                $tag = new Model_Tag($this->_query);
                if ( ! $tag->loaded()) return FALSE;

                $tag_params = $tag->parse_params();
                $this->_params += $tag_params;

                if ($tag->section_id) { // новые теговые - меню как у категории
                    if ( ! $this->_read_section($tag->section_id)) { // menu_params set inside _read_section
                        echo 'no section for ' . $this->_mode . ':' . $this->_query;
                        throw new HTTP_Exception_404;
                    }

                } else { // старые теговые - меню из условий

                    $this->_menu_params = $tag_params; // старые теговые
                    $this->_params['x'] = 2;
                    $this->_menu_params['x'] = 1;
                }
                break;

            case 'action':
                $action = new Model_Action($this->_query);
                if ( ! $action->loaded() || ! $action->active) return FALSE; //throw new HTTP_Exception_404;
                $this->_params['a'] = $this->_menu_params['a'] = intval($this->_query);
                $this->_params['x'] = 2;
                $this->_menu_params['x'] = 1;

                break;
        }

        if ($read_query) $this->read_params();
    }

    /**
     * Получить параметры-ограничения для запроса
     * получает список брендов, [категорий], фильтров, мин и макс цену, кеширует на час
     * @param $for_menu - получить параметры для меню (не учитывать доп параметры из query_string)
     * @return array|mixed
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function stats($for_menu = FALSE)
    {
        $mode = $this->_mode;
        $query = $this->_query;

        $stats_key = $mode.md5($query).Kohana::$server_name;
        $return = FALSE; // do we need cache here? Cache::instance()->get($stats_key);

        if (empty($return)) {
            $sphinx_db = Database::instance('sphinx');

            $q = DB::select(DB::expr('###'))
                ->from('goods_zukk')
                ->where('shop_id', '=', self::shop_id());

            $this->_apply_params($q, $for_menu ? $this->_menu_params : $this->_params, TRUE);

            // узнаём первым запросом а есть ли что-то вообще
            $max = $min = $total = 0;
            $prices = $sphinx_db->query(Database::SELECT, str_replace('###', 'MAX(price) as `max`, MIN(price) as `min`, COUNT(*) as `total`', $q))->as_array();
            if ( ! empty($prices)) {
                $max = $prices[0]['max'];
                $min = $prices[0]['min'];
                $total = $prices[0]['total'];
            }

            $brands = $sections = $fvals = [];
            if ($total > 0) {

                $cloneq = clone $q;
                $brands = $sphinx_db->query(Database::SELECT, str_replace('###', 'brand_id', $cloneq->group_by('brand_id')))->as_array('brand_id', 'brand_id');
                if (empty($this->_section)) {
                    $cloneq = clone $q;
                    $sections = $sphinx_db->query(Database::SELECT, str_replace('###', 'section_id ', $cloneq->group_by('section_id')->limit(250)))->as_array('section_id', 'section_id');
                } else {
                    $sections = [$this->_section->id => $this->_section->id];
                }

                if ( ! empty($this->_section)) { // для одной категории получаем ещё фильтры
                    $cloneq = clone $q;
                    $fvals = $sphinx_db->query(Database::SELECT, str_replace('###', '@groupby as fvalue', $cloneq->group_by('fvalue')->limit(250)))->as_array('fvalue', 'fvalue');
                }
            }

            if ( ! empty($sections)) $sections = Model_Section::id_name(array_keys($sections));

            if ( ! empty($fvals)){
				list($filters, $vals) = Model_Filter_Value::filter_val($fvals);
			}

            if ( ! empty($brands)) {
                $brands = Model_Brand::id_name(array_keys($brands));

                if ( ! empty($section)) {

                    $r = array(); // сюда собираем начало списка брендов

                    if( ! empty( $section->settings['brands'] )) { // в секции проставлен порядок брендов

                        foreach( $section->settings['brands'] as $bId) { // начало списка брендов - как в настройках
                            if( ! empty($brands[$bId])) {
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
                'max'       => ceil($max / 100),
                'min'       => floor($min / 100),
                'total'     => $total,
                'filters'   => ! empty($filters) ? $filters : [],
                'vals'      => ! empty($vals) ? $vals : [],
            ];

            Cache::instance()->set($stats_key, $return); // кэшируем результат на час
        }
        return $return;
    }

    /**
     * Получить левое меню с помеченными параметрами поиска
     * @return string html левого меню
     */
    function menu()
    {
        if (empty($this->_menu)) {
            $this->_menu = $this->stats(TRUE);
        }

        return View::factory('smarty:common/menu', $this->_menu + [
            'mode' => $this->_mode,
            'query' => $this->_query,
            'params' => $this->_params
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
     */
    private function read_params($do_redirect = TRUE)
    {
        $request = Request::current();

        foreach($request->query() as $k => $v) {
            switch ($k) {
                case 'b': // брэнд
                case 'c': // раздел
                    $this->_params[$k] = array_filter(explode('_', $v), 'ctype_digit');
                    sort($this->_params[$k]);
                    break;

                case 'pr': // цена
                    $p = array_filter(explode('-', $v, 2), 'ctype_digit');
                    if ( ! empty($p)) {
                        if (empty($p[1])) {
                            $this->_params['pr'] = array(1 => $p[0]); // только макс цена
                        } elseif ($p[1] >= $p[0]) {
                            $this->_params['pr'] = array($p[0], $p[1]);
                        }
                    }
                    break;

                case 'x': // наличие
                    if ($v >= 1) $this->_params['x'] = 1;
                    break;

                case 'pp': // на странице
                    $this->_params['pp'] = intval($v);
                    break;

                case 's': // сортировка
                    if (in_array($v, array('name', 'rating', 'price', 'new', 'pricedesc'))) {
                        $this->_params['s'] = $v;
                    }
                    break;

                case 'm': // вид
                    if (in_array($v, [0, 1])) $this->_params['m'] = $v;
                    break;

                default:
                    if (preg_match('~^f(\d+)$~', $k, $matches)) { // фильтры
                        $fid = $matches[1];
                        $vals = array_filter(explode('_', $v), 'ctype_digit');
                        if ( ! empty($vals)) $this->_params['f'][$fid] = $vals;
                    }
                    break;
            }
        }

        // значения по умолчанию
        if (empty($this->_params['pp']) || $this->_params['pp'] > 100 || $this->_params['pp'] < 20) $this->_params['pp'] = 40;
        if ( ! isset($this->_params['s'])) $this->_params['s'] = 'rating';
        if ( ! isset($this->_params['m'])) $this->_params['m'] = 1;

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
        if ( ! empty($params['q'])) {
            $q->where(DB::expr("MATCH('"), Sphinx::correct_user_query($params['q']), DB::expr("')"));
        }

        // секции
        if ( ! empty($params['c'])) $q->where('section_id', 'IN', array_map('intval', $params['c']));

        // брэнды
        if ( ! empty($params['b'])) $q->where('brand_id', 'IN', array_map('intval', $params['b']));

        // цена (в сфинксе в копейках)
        if ( ! empty($params['pr'])) {
            if (isset( $params['pr'][0] )) $q->where('price', '>=', $params['pr'][0] * 100);
            $q->where('price', '<=', $params['pr'][1] * 100);
        }

        // значения фильтров
        if ( ! empty($params['f'])) {
            foreach($params['f'] as $values) {
                $q->where('fvalue', 'IN', array_map('intval', $values)); // группируем по фильтрам
            }
        }

        // акция
        if ( ! empty($params['a'])) $q->where('action_id', '=', $params['a']);

        // наличие
        if ( ! empty($params['x']) AND $params['x'] >= 1) $q->where('x', '!=', 0);

        // скидка
        if ( ! empty($params['d'])) $q->where('discount', '=', 1);

        // суперцена
        if ( ! empty($params['su'])) $q->where('superprice', '=', 1);

        // хиты продаж
        if ( ! empty($params['h'])) $q->where('hit', '=', 1);

        // новинка
        if ( ! empty($params['n'])) $q->where('nt', '!=', 0);

        // сортировка
        if ( ! $no_order) {
            if ( ! empty($params['s'])) {
                $sorts = array('rating' => '-popularity', 'name' => 'alphabet', 'price' => 'price', 'pricedesc' => '-price', 'new' => '-nt');
                if ( ! empty($sorts[$params['s']])) {
                    $sort = $sorts[$params['s']];
                    $q->order_by(trim($sort, '-'), substr($sort, 0, 1) === '-' ? 'DESC' : 'ASC');
                };
            }
        }

        if ( ! empty($params['g'])) $q->where('id', 'IN', $params['g']);  // есть список ID товаров  - проверить где используется (памперс?)

        return $q;
    }

    /**
     * Получение списка групп и товаров, для показа результатов поиска, товаров удовлетворяющих запросу ( получаем из сфинкса )
     * @return grouped
     */
    function search()
    {
        if (empty($this->_menu)) { // отсюда мы берем списки возможных брендов-категорий-фильтров-цен
            $this->_menu = $this->stats(TRUE);
        }

        $pager = new Pager(FALSE, $this->_params['pp']);

        $q = DB::select('id', 'grouped', 'group_id')
            ->from('goods_zukk')
            ->where('shop_id', '=', self::shop_id())
            ->limit($pager->offset . ',' . $pager->per_page);

        $q = $this->_apply_params($q, $this->_params);

        $result = Database::instance('sphinx')
            ->query(Database::SELECT, strval($q))
            ->as_array('id');

        //Database::instance('sphinx')->last_query;

		$grouped = FALSE;
        $gids = $group_goods = array(); // ид товаров и списки групп
        if ( ! empty($result)) {

            $meta = $this->meta();
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
                if ($data['grouped']) { // это товар-группа
                    $g->review_qty = $groups[$g->group_id]->review_qty; // рейтинги группы
                    $g->rating = $groups[$g->group_id]->rating; // рейтинги группы
                    $g->grouped = $groups[$g->group_id]->qty; // число товаров в группе

                    if ($g->grouped > 1) {
                        $g->price = $groups[$g->group_id]->min_price;
                        $g->same_price = $groups[$g->group_id]->min_price == $groups[$g->group_id]->max_price;
                    } else {
                        $g->same_price = TRUE;
                    }
					// $g->same_price = FALSE;
                }
                $this->goods[$id] = $g;
            }
        }

        if ( ! empty($with_other_shop)) {// результаты такого же запроса на другой витрине, 4 товара
            $q_ov = DB::select('id', 'grouped')
                    ->from('goods_zukk')
                    ->where('shop_id', '=', self::get_other_shop_id())
                    ->where('x', '!=', 0)
                    ->where(DB::expr("MATCH('"), Sphinx::correct_user_query($this->_params['q']), DB::expr("')"))
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

		return View::factory('smarty:common/groups', [
            'goods' => $this->goods,
            'images'=> $this->images,
            'price' => $this->price,
            'pager' => $this->pager,
            'params'=> $this->_params,
            'is_section'    => ! empty($this->_section->id),
            'menu'  => $this->_menu,
            'mode'  => $this->_mode,
            'is_cloth' => $this->is_cloth(),
            'cloth'   => ( ! empty($grouped)) ? $this->_load_cloth($group_goods, $gids) : [],
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
                if (is_array($returner[$gId])) {
                    foreach ($returner[$gId] as $fId => $vId) {
                        $groupValues[$groupId][$fId] = $vId;
                    }
                }
            }
        }

        $return = ['groupValues' => $groupValues, 'goodValues' => $returner, 'groups' => $groups];
        return $return;

    }
    
    public static function correct_user_query( $q )
    {
        $q = trim( $q );

        $arCorrect   = array();
        $langCorrect = new LangCorrect();
        $langCorrect->parse( $q, LangCorrect::SIMILAR_CHARS | LangCorrect::KEYBOARD_LAYOUT | LangCorrect::ADD_FIX, $arCorrect );

        $searchQuery = count( $arCorrect ) ? str_replace( array_keys( $arCorrect ), array_values( $arCorrect ), $q ) : $q;
        $searchQuery = preg_replace( '/[^a-zA-Zа-яА-Я0-9]+/u', ' ', $searchQuery );

        return trim($searchQuery);
    }

    /**
     * Если все категории в меню - одежда, то для результатов поиска будет шаблон одежды
     */
    public function is_cloth()
    {
        $cloth_subs = Model_Section::get_cloth_subs();

        if (empty($this->_menu)) {
            $this->_menu = $this->stats(TRUE);
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
}
