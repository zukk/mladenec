<?php

class Controller_Section extends Controller_Frontend
{
    private function query_to_hash($query = array())
    {
        $hash   = []; // параметры для хэша '#!';
        $url    = []; // параметры для урла '?';
        $valid  = ['s','pp','page','m','b','pr']; // параметры которые надо переметить в хэш

        $skip = TRUE; // пропустить ли этот урл как есть?

        if ( ! empty($query['a']) && $query['a'] == 1) { // очень старый урл - маркер поиска - сбрасываем
            unset($query['a']);
        }
        if ( ! empty($query['s']) && $query['s'] == 1) { // очень старый урл - маркер поиска - меняем на сортировку по популярности
            $query['s'] = 'rating';
            unset($query['s']);
        }

        if ( ! empty($query['p']['PROPERTY_BRAND'])) { // очень старый урл - ид брендов
            $query['b'] = $query['p']['PROPERTY_BRAND'];
            unset($query['p']);
        }

        if ( ! empty($query['napkins'])) { // старый урл - салфетки в памперсе
            unset($query['napkins']);
            $hash['c'] = 28856;
            $skip = FALSE;
        }

        if ( ! empty($query['f'])) {
            if (is_array($query['f'])) {
                foreach($query['f'] as $k => $v) {
                    $fv = ORM::factory('Filter_Value')->where('id', '=', $v)->where('filter_id', '=', $k)->count_all();
                    if ( ! empty($fv)) {
                        $hash['f'.$k ] = $v;
                    }
                }
            } else {
                $fstr = explode('_', $query['f']);
                foreach($fstr as $fs) {
                    if (FALSE === strpos($fs, '-')) continue;
                    list($fid, $fvalues_str) = explode('-', $fs);
                    $fvalues = explode('x', $fvalues_str);
                    foreach($fvalues as $v) {
                        $fv = ORM::factory('Filter_Value')->where('id', '=', $v)->where('filter_id', '=', $fid)->count_all();
                        if (empty($fv)) unset($fvalues[$v]);
                    }
                    if ( ! empty($fvalues)) {
                        $hash['f'.$fid ] = implode('_', $fvalues);
                    }
                }
            }
            $skip = FALSE;
            unset($query['f']);
        }
        
        foreach($query as $name => $value) {
            if ( ! in_array($name, $valid)) {
                $url[$name] = $value;
            } else {
                $hash[$name] = $value;
                $skip = FALSE;
            }
        }
        if (array_keys($hash) == ['page']) {
            $skip = TRUE; // только один параметр - номер страницы, оставляем как есть
        }
        if ($url + $hash == $query) {
            $skip = TRUE;
        }
        if ($skip) return FALSE; // To avoid cycle redirecting

        return ( ! empty($url) ? '?' : '') . http_build_query($url) . ( ! empty($hash) ? ( empty($url) ? '?' :  '&') : '') . http_build_query($hash, null, '&');
    }
    
    /**
     * Просмотр раздела - выводит список подразделов или список товаров
     * @throws HTTP_Exception_404
     */
    public function action_view()
    {
        if ($q = $this->request->param('query')) {
            parse_str($q, $query);
            $url = str_replace('&'.$q, '', $this->request->url()); // если ссылка типа html& (старые ссылки)
        } else {
            $query = $this->request->query();
            $url = $this->request->url();
        }

        if ( ! empty($query)) { // редирект на исправленный урл
            $q_str = $this->query_to_hash($query);
            if (FALSE !== $q_str) {  // To avoid cycle redirecting
                $this->request->redirect($url . $q_str, 301);
            }
        }
        $id = $this->request->param('id');
        $this->tmpl['section'] = $section = (empty($id)) ? new Model_Section(['translit' => $this->request->param('translit'), 'active' => 1]) : new Model_Section($id); // новые урлы : старые урлы
        if ( ! $section->loaded() || ! $section->active) throw new HTTP_Exception_404;

        if ($section->vitrina != Kohana::$server_name) { // редирект на правильную витрину!
            $this->request->redirect($section->get_link(0));
        }

        $this->tmpl['seoname'] = $seoname = $section->h1;

		if ( ! empty($section->seo->title)) {
			$this->layout->title = $section->seo->title;
			$this->layout->keywords = $section->seo->keywords;
            $this->layout->description = $section->seo->description;
        } else {
            $this->layout->title = $section->name;
        }

		$this->layout->allbg = $section->id; //картинка фона для eatmart

		if (empty($section->roditi)) $section->roditi = $section->name;

        if ($section->parent_id > 0) {

            $this->tmpl['parent'] = $parent = ORM::factory('section')
                ->where('active', '=', 1)
                ->where('id', '=', $section->parent_id)
                ->find();

            $this->layout->allbg = $parent->id; // show parent image if has parent [eatmart]

            $fv_id = $this->request->param('fv_id');

			$this->tmpl['roditi'] = $this->tmpl['third_level'] = '';

            if ( ! empty($fv_id)) { // это страница с товарами, третий уровень, созданный из фильтра по большому типу, есть

                $sphinx = new Sphinx('section_filter', $section->id.'_'.$fv_id);

                $this->tmpl['search_result'] = $sphinx->search();
                $this->layout->menu = $sphinx->menu();

                $third_level = '';
                foreach($sphinx->stats()['vals'] as $fid => $data) {
                    if (Model_Filter::big($fid)) {
                        $third_level = $sphinx->stats()['vals'][$fid][$fv_id]['name'];
                        break;
                    }
                }
                $this->tmpl['third_level'] = $third_level ;
                $seoname = $third_level.' '.($section->parent_id == Model_Section::CLOTHS_ROOT ? mb_strtolower($section->name) : ''); // тут переставляем слова местами

                $this->tmpl['hide_text'] = TRUE;

            } elseif ( $section->settings['list'] == Model_Section::LIST_FILTER) {  // это страница с меню по фильтру

                $sphinx = new Sphinx('section', $section->id); // понадобится для меню, нужно чтение параметров на случай редиректа в фильтр-категорию
                $stats = $sphinx->stats();

                $this->tmpl['filter_values'] = $values = ORM::factory('filter_value')
                    ->where('id', 'IN', array_keys($stats['vals'][$section->settings['sub_filter']]))
                    ->order_by('sort')
                    ->find_all()
                    ->as_array();

                $subs = [];

                $sf = [
                    // коды фильтров для подменю во всё-для мам
                    18030 => 2101, // хранение молока - вид
                    20123 => 2100, // уход за грудью - вид
                    20124 => FALSE, // подушки для мам - бренд
                    20125 => 2260, // для роддома - вид

                    20126 => 2267, // питание для мам - применение
                    20127 => 2262, // косметика - тип
                    18027 => 2264, // молокоотсосы - вид
                    20128 => 2259, // белье - вид

                    // коды фильтров для подменю - косметика и ежедневный уход
                    20422 => 2290, // для ванны и душа - вид
                    20423 => 2292, // уход за волосами - вид
                    20424 => 2294, // гигиена рта - вид
                    20425 => 2296, // личная гигиена - вид
                    20426 => 2301, // уход за руками - вид
                    20427 => 2303, // уход за ногами - вид
                    20428 => 2306, // уход за лицом - применение

                    // коды фильтров для подменю Игрушки для малышей
                    19079 => 2230,
                    19081 => 2230,
                    19089 => 2230,
                    19091 => 2230,
                    19093 => 2230,
                    19105 => 2230,
                    19125 => 2230,
                    19152 => 2230,
                    19171 => 2230,
                    21064 => 2230,
                    21065 => 2230,
                    21066 => 2230,
                    21067 => 2230,
                    21068 => 2230,
                    21069 => 2230,
                    21070 => 2230,
                    21071 => 2230,
                    21072 => 2230,
                    21073 => 2230,
                    21074 => 2230,
                    21075 => 2230,
                    21076 => 2230,
                    21077 => 2230,
                    21078 => 2230,
                    21079 => 2230,
                ];
                foreach($values as $val) {
                    $s = new Sphinx('section_filter', $section->id.'_'.$val->id, FALSE);
                    $sub = [
                        'name'  => $val->name,
                        'id'    => $val->id,
                        'img'   => $val->get_img(),
                    ];

                    $stats = $s->stats();
                    if ( ! empty($sf[$val->id]) && ! empty($stats['vals'][$sf[$val->id]])) {
                        foreach($stats['vals'][$sf[$val->id]] as $vid => &$v) {
                            $v['href'] = $s->href(['f' => [$sf[$val->id] => [$vid]]]);
                        }
                        $sub['sub'] = $stats['vals'][$sf[$val->id]];

                    } elseif ( ! empty($stats['brands'])) {

                        foreach($stats['brands'] as &$b) {
                            $b['href'] = $s->href(['b' => [$b['id']]]);
                        }
                        $sub['sub'] = $stats['brands'];

                    }
                    $subs[] = $sub;
                }
                $this->tmpl['row'] = 2;
                $this->tmpl['column'] = 0;

                $this->layout->menu = View::factory('smarty:common/menu_third', [
                    'section'       => $section,
                    'subs_filter'   => $subs,
                    'column'        => 5,
                ])->render();

                $this->tmpl['subs_filter'] = $subs;
                $this->tmpl['hide_text'] = TRUE;

            } else { // показываем обычные товары - тут надо показать текст если не было запроса

                $sphinx = new Sphinx('section', $section->id);
                $this->layout->menu = $sphinx->menu();
                $this->tmpl['search_result'] = $sphinx->search();
				$params = $sphinx->params();
				
				if ( ! empty($params['b'])) { // есть бренды
					
					list(, $_v) = each($params['b']);
					
					$this->tmpl['third_level'] .= $sphinx->stats()['brands'][$_v]['name'] . ' ';
					$this->tmpl['roditi'] .= $sphinx->stats()['brands'][$_v]['name'] . ' ';
				}
				
				if ( ! empty($params['f'])) {  // есть фильтры

					foreach( $params['f'] as $_f => $_v ) {
						
						list($_f, $_v) = each($params['f']);
						
						if ( is_array($_v)) {
                            foreach ($_v as $_) {

                                $tmp = &$sphinx->stats()['vals'][$_f][$_];

                                $this->tmpl['third_level'] .= $tmp['name'] . ' ';
                                $this->tmpl['roditi'] .= (!empty($tmp['roditi']) ? $tmp['roditi'] : $tmp['name']) . ' ';
                            }
                        }
					}
				}

                if ($sphinx->qs) { // есть строка запроса - надо генерить СЕО
                    $this->layout->set($sphinx->seo());
                }
            }

            if ( ! empty($query)) $this->tmpl['hide_text'] = TRUE;
            
            $this->tmpl['tags'] = $section->get_tags();
			
			if (empty($seoname)) {
                $seoname = $section->name.' '.mb_strtolower($this->tmpl['third_level']);
            }

            if ( ! empty($sphinx->qs)) {
                $this->layout->title = $seoname . '. Купить ' . mb_strtolower($section->name) . ' Москве, Санкт-Петербурге — интернет магазин Младенец.ру';
                $this->layout->description = 'Большой выбор ' . mb_strtolower($section->roditi) . ' ' . $this->tmpl['roditi'] . ' по доступным ценам. '
                    .'У нас вы можете купить ' . mb_strtolower($seoname) . ' по тел: 8(800)555-699-4. Быстрая доставка по всей России';
                $this->layout->keywords = $seoname  . ', купить ' . mb_strtolower($seoname) . ' в москве, заказать, продажа';
            }
			
        } elseif ($section->parent_id == 0) { // категория верхнего уровня

            $this->tmpl['row'] = 4;

            if (in_array($section->id, [29777])) {

                $this->layout->menu = View::factory('smarty:common/menu_section', [ // подменю для подгузников
                    'section' => $section,
                    'top_menu' => Model_Section::get_catalog(),
                ])->render();
                $this->tmpl['row'] = 2;
                $this->tmpl['hide_sub'] = 1;

            } else {

                $this->layout->menu = FALSE;
            }

            $this->tmpl['subs'] = $subs = ORM::factory('section') // подкатегории
                ->with('img')
                ->where('vitrina', '=', Kohana::$server_name)
                ->where('active', '=', 1)
                ->where('parent_id', '=', $section->id)
                ->order_by('sort')
                ->find_all()
                ->as_array('id');

            if (empty($subs)) throw new HTTP_Exception_404;
        }

        $this->tmpl['seoname'] = $seoname;
        if (($p = $this->request->query('page')) && $p > 1) { // если мы не на первой странице - добавим текст в title
            $this->layout->title .= ' (страница каталога №'.$p.') - Младенец.ру';
        }

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => ! empty($this->layout->title) ? $this->layout->title : 'Младенец. РУ',
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:section/view', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }

    /**
     * Промо-страница памперса
     */
    public function action_pampers()
    {
        if ($this->request->query('napkins')) {
            $query = $this->request->query();
            if ( ! empty($query)) {
                $q_str = $this->query_to_hash($query);
                if (FALSE !== $q_str) {  // To avoid cycle redirecting
                    $this->request->redirect($this->request->url() . $q_str, 301);
                }
            }
        }

        $g = Model_Good::get_pampers();

        /**
         * Специальные параметры отбора для памперса [HARDCODE]
         */
        $this->tmpl['age'] = $age = [
            '0-5' => [184435, 54684, 54037, 54038, 148036, 148037, 102705, 193248],
            '6-12' => [54060, 54062, 150178, 54061, 54063, 150179, 148030, 148032, 148119, 148120, 193758, 193760, 193759],
            '1-2' =>  [54064, 150177, 54065, 191785, 191784, 148038, 148121, 148039, 148041, 148040, 148042, 148033, 148034, 191783, 191782, 55367, 55370, 55368, 55371, 55369, 55372],
        ];

        $this->tmpl['size'] = $size = [
            0 => ['Размер 0' => [188554]],
            1 => ['Размер 1 (2-5 кг)' => [54036, 54051, 74550, 193258]],
            2 => ['Размер 2 (3-6 кг)' => [54037, 54038, 54052, 54053, 148036, 148037, 184435, 193248, 193254]],
            3 => ['Размер 3 (4-11 кг)' => [54039, 54040, 54054, 54055, 54060, 54061, 147994, 148030, 148031, 148119, 150178, 157491, 184434, 193255,214139,214142, 216123, 216122, 216121]],
            4 => ['Размер 4 (7-14 кг)' => [54041, 54042, 54043, 54056, 54057, 54062, 54063, 55367, 55370, 148032, 148038, 148120, 148121, 148826, 150179, 181635, 191783, 191785, 193247, 193256, 214143, 214140, 216125, 216126]],
            '4 ' => ['Размер 4+ (9-16 кг)' => [54044, 54045, 54046, 148033, 181636, 193253]],
            5 => ['Размер 5 (11-25 кг)' => [54047, 54048, 54049, 54058, 54059, 54064, 54065, 55368, 55371, 109458, 148034, 148039, 148041, 150177, 181637, 184433, 191782, 191784, 193257,214141, 214144, 216129, 216127, 216128]],
            6 => ['Размер 6 (15+ кг)' => [54050, 55369, 55372, 148035, 148040, 148042, 216130, 216131, 216132]],
        ];
        
        $this->layout->title = 'Магазин Памперс';
	    $this->tmpl['front'] = ! (bool)$this->request->query('ba'); // ba - значит баннер сверху

        $sphinx = new Sphinx('pampers');
        $params = $sphinx->params();
        
        if ( ! empty($params['f'])) $this->tmpl['front'] = FALSE; // если есть хоть один фильтр - мы не на главной
        $s = $this->request->query('size');
        if ( $s !== '' && ! empty($size[$s])) {
            $sphinx->param('g', current($size[$s]));
            $this->tmpl['front'] = FALSE;
        }

        if (($a = $this->request->query('age')) && ! empty($age[$a])) {
            $sphinx->param('g', $age[$a]);
            $this->tmpl['front'] = FALSE;
        }

        if ($this->tmpl['front']) { // на главной получаем 4 самых популярных товара
            $this->tmpl['best'] = $best = ORM::factory('good')
                ->where('id', 'IN', $g)
                ->order_by('popularity', 'DESC')
                ->limit(4)
                ->find_all()
                ->as_array('id');

            $this->tmpl['images'] = Model_Good::many_images([255], array_keys($best));
            $this->tmpl['price'] = Model_Good::get_status_price(1, array_keys($best));
        
            //цена на premium care 
            $result = DB::select(DB::expr('MIN(price) as min_price'))
                        ->from('z_good')
                        ->join('z_good_filter')
                        ->on('z_good.id','=','z_good_filter.good_id')
                        ->where('filter_id', '=', 2199)
                        ->where('value_id', '=', 18696)
                        ->where('good_id', 'in', 
                                DB::select('good_id')->from('z_good')
                                    ->join('z_good_filter')
                                    ->on('z_good.id','=','z_good_filter.good_id')
                                    ->where('filter_id', '=', 2200)
                                    ->where('value_id', '=', 18743)
                               )->limit(1)->execute()->current();
            $this->tmpl['price_premium_care'] = ($result['min_price'] ? $result['min_price'] : 540);
        }

        $this->layout->menu = $sphinx->menu();
        $is_pampers = true;
        View::bind_global('is_pampers', $is_pampers);
        
        if ( ! $this->tmpl['front']) {
            $this->tmpl['search_result'] = $sphinx->search();
        }

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => $this->layout->title,
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:section/pampers', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }

    /**
     * Карта товарного каталога - выводит все разделы товарного каталога в виде дерева
     */
    public function action_map()
    {
        $this->tmpl['map'] = Model_Section::get_catalog();
        $this->layout->menu = Model_Menu::html();
    }
}
