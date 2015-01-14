<?php

class Controller_Section extends Controller_Frontend
{
    private function query_to_hash($query = array())
    {
        $hash   = array(); // параметры для хэша '#!';
        $url    = array(); // параметры для урла '?';
        $valid = array('s','pp','page','x','m','b','pr'); // параметры которые надо переметить в хэш

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
        $this->tmpl['section'] = $section = (empty($id)) ? new Model_Section(array('translit' => $this->request->param('translit'), 'active' => 1)) : new Model_Section($id); // новые урлы : старые урлы
        if ( ! $section->loaded() || ! $section->active) throw new HTTP_Exception_404;

        if ($section->vitrina != Kohana::$server_name) { // редирект на правильную витрину!
            $this->request->redirect($section->get_link(0));
        }

		if ( ! empty( $section->seo->title ) ){
			$this->layout->title = $section->seo->title;
			$this->layout->keywords = $section->seo->keywords;
		} else {
			$this->layout->title = $section->name;
		}
		
		if ( ! empty( $section->seo->description ) ){
			$this->layout->description = $section->seo->description;
		}
		
        $this->layout->allbg = $section->id;

        if ($section->parent_id > 0) {

            $this->tmpl['parent'] = $parent = ORM::factory('section')
                ->where('active', '=', 1)
                ->where('id', '=', $section->parent_id)
                ->find();
			
            $this->layout->allbg = $parent->id; // show parent image if has parent [eatmart]

            $fv_id = $this->request->param('fv_id');

            if ( ! empty($fv_id)) { // это страница с товарами, третий уровень

                $sphinx = new Sphinx('section_filter', $section->id.'_'.$fv_id);
                $this->tmpl['hide_text'] = TRUE;
                $this->tmpl['search_result'] = $sphinx->search();;
                $this->layout->menu = $sphinx->menu();
                $this->tmpl['third_level'] = $third_level = $sphinx->stats()['vals'][Model_Filter::CLOTH_BIG_TYPE][$fv_id];
                $this->layout->title = $third_level.' '.$this->layout->title;

            } elseif ( $section->settings['list'] == Model_Section::LIST_FILTER) {  // это страница с меню по фильтру

                $sphinx = new Sphinx('section', $section->id); // понадобится для меню
                $stats = $sphinx->stats();
                $this->layout->menu = $sphinx->menu();
                $this->tmpl['search_result'] = $sphinx->search();;

                $this->tmpl['filter_values'] = ORM::factory('filter_value')
                    ->where('id', 'IN', array_keys($stats['vals'][$section->settings['list_filter']]))
                    ->find_all()
                    ->as_array();

            } elseif ($section->settings['list'] == Model_Section::LIST_TEXT) { // показываем только текст

                $sphinx = new Sphinx('section', $section->id);

                if ($section->is_cloth()) { // для одежды левое меню = большой фильтр

                    $this->layout->menu = View::factory('smarty:common/menu_filter', [
                        'vals' => $sphinx->stats()['vals'][Model_Filter::CLOTH_BIG_TYPE],
                        'section'   => $section,
                    ]);

                } else { // для не одежды - обычное меню

                    $this->layout->menu = $sphinx->menu();
                }

            } else { // показываем обычные товары

                $sphinx = new Sphinx('section', $section->id);
                $this->layout->menu = $sphinx->menu();
                $this->tmpl['search_result'] = $sphinx->search();
            }

            $this->tmpl['tags'] = $section->get_tags();
			
        } elseif ($section->parent_id == 0) { // категория верхнего уровня

            $this->tmpl['subs'] = $subs = ORM::factory('section') // подкатегории
                ->with('img')
                ->where('vitrina', '=', Kohana::$server_name)
                ->where('active', '=', 1)
                ->where('parent_id', '=', $section->id)
                ->order_by('sort')
                ->find_all()
                ->as_array('id');

            if (empty($subs)) throw new HTTP_Exception_404;

            $brandy = $filtry = $filters = $brand_ids = $brands = $by_section = array();
            foreach($subs as $sub_section) {
                if ($sub_section->settings['list'] == Model_Section::LIST_GOODS) {
                    $brandy[] = $sub_section->id;
                } elseif ($sub_section->settings['list_filter']) {
                    $filtry[$sub_section->id] = $sub_section->settings['list_filter'];
                }
            }
            if ( ! empty($brandy)) {
                $section_brand = DB::select('brand_id', 'section_id', DB::expr('SUM(qty) as qty')) // брэнды в секции
                    ->from('z_group')
                    ->where('section_id', 'IN', $brandy)
                    ->where('brand_id',  '>', 0)
                    ->where('active', '=', 1)
                    ->group_by('section_id')
                    ->group_by('brand_id')
                    ->execute()
                    ->as_array();

                foreach($section_brand as $sb) { // группируем по секциям и собираем бренды
                    $brand_ids[$sb['brand_id']] = $sb['brand_id'];
                    if (empty($by_section[$sb['section_id']])) $by_section[$sb['section_id']] = array();
                    $by_section[$sb['section_id']][$sb['brand_id']] = $sb['qty'];
                }

                if ( ! empty($brand_ids)) // чтобы когда нет вообще брендов не давало красный экран
                {
                    $this->tmpl['brands'] = $brands = ORM::factory('brand')   // все подряд брэнды
                        ->where('active', '=', 1)
                        ->where('id', 'IN', array_keys($brand_ids))
                        ->find_all()
                        ->as_array('id');

                    function brand_sorter($brands) {
                        return function ($a, $b) use ($brands) {
                            // Не должно выбрасывать ошибки на "лишних" брендах
                            if ( ! empty($brands[$a]) AND ! empty($brands[$b]) AND ($brands[$a] instanceof Model_Brand) AND ($brands[$b] instanceof Model_Brand)) {
                                return $brands[$a]->sort - $brands[$b]->sort ;
                            } else return 0;
                        };
                    }
                    foreach($by_section as &$section_brand) {
                        uksort($section_brand, brand_sorter($brands));
                    }
                    $this->tmpl['by_section'] = $by_section; // бренды по категориям
                }
            }
            if ( ! empty($filtry)) {
                foreach($filtry as $sub_id => $filter_id) {
                    $sphinx = new Sphinx('section', $sub_id);
                    $params = $sphinx->menu();
                    if ( ! empty($params['vals'][$filter_id])) {
                        $filters[$sub_id] = $params['vals'][$filter_id];
                    }
                }
                $this->tmpl['filters'] = $filters; // фильтры по категориям
            }

			$this->tmpl['text'] = $section->text;

			$this->layout->menu = View::factory('smarty:common/menu_section', array(
                'section' => $section,

/*				'items' => $by_section,
				'sections' => $subs,
				'column' => 7,
				'brands' => $brands,
                'filters' => $filters
*/			)); // меню c помеченными опциями поиска
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
        list($f, $v, $g) = Model_Good::get_pampers();

        /**
         * Специальные параметры отбора для памперса [HARDCODE]
         */
        $age = array(
            '0-5' => array(184435, 54684, 54037, 54038, 148036, 148037, 102705, 193248),
            '6-12' => array(54060, 54062, 150178, 54061, 54063, 150179, 148030, 148032, 148119, 148120, 193758, 193760, 193759),
            '1-2' =>  array(54064, 150177, 54065, 191785, 191784, 148038, 148121, 148039, 148041, 148040, 148042, 148033, 148034, 191783, 191782, 55367, 55370, 55368, 55371, 55369, 55372),
        );

        $size = array(
            1 => array('Размер 1 (2-5 кг)' => array(54036, 54051, 74550, 193258)),
            2 => array('Размер 2 (3-6 кг)' => array(54037, 54038, 54052, 54053, 148036, 148037, 184435, 193248, 193254)),
            3 => array('Размер 3 (4-9 кг)' => array(54039, 54040, 54054, 54055, 54060, 54061, 147994, 148030, 148031, 148119, 150178, 157491, 184434, 193255)),
            4 => array('Размер 4 (7-18 кг)' => array(54041, 54042, 54043, 54056, 54057, 54062, 54063, 55367, 55370, 148032, 148038, 148120, 148121, 148826, 150179, 181635, 191783, 191785, 193247, 193256)),
            '4+' => array('Размер 4+ (9-20 кг)' => array(54044, 54045, 54046, 148033, 181636, 193253)),
            5 => array('Размер 5 (11-25 кг)' => array(54047, 54048, 54049, 54058, 54059, 54064, 54065, 55368, 55371, 109458, 148034, 148039, 148041, 150177, 181637, 184433, 191782, 191784, 193257)),
            6 => array('Размер 6 (16+ кг)' => array(54050, 55369, 55372, 148035, 148040, 148042)),
        );
        
        $this->layout->title = 'Магазин Памперс';
	    $this->tmpl['front'] = ! (bool)$this->request->query('ba');
        $query_string = '';

    //    $params = Txt::read_params(TRUE);
        if (empty($params)) {
            $this->tmpl['view_params'] = Txt::view_params('');
        } else {
            $this->tmpl['front'] = FALSE;
        }

        $section = new Model_Section(Model_Good::PAMPERS_SECTION);

        $params['b'] = array(Model_Good::PAMPERS_BRAND);
        if (($s = $this->request->query('size')) && ! empty($size[$s])) {
            $query_string = 'size'.$s;
            $this->tmpl['front'] = FALSE;
        }

        if (($a = $this->request->query('age')) && ! empty($age[$a])) {
            $query_string = 'age'.$a;
            $this->tmpl['front'] = FALSE;
        }

        // тут надо отобрать только те фильтры и товары что для пампарса подходят
        $filters = $vals = array();
        list($filters, $vals) = Model_Filter_Value::filter_val($v);
 
        if ($this->tmpl['front']) {
            $this->tmpl['best'] = ORM::factory('good')->where('id', 'IN', $g)->order_by('popularity', 'DESC')->limit(4)->find_all();
        }

        $this->tmpl['line'] = $vals[1578]; // фильтр по коллекции HARDCODE
        $this->tmpl['age'] = $age;
        $this->tmpl['size'] = $size;
		$this->tmpl['query'] = array_diff_key($this->request->query(), $params);

        $this->layout->menu = View::factory('smarty:common/menu', array(
            'vals' => $vals,
            'filters' => $filters,
            'params' => $params,
            'max' => ceil($section->max_price),
            'min' => floor($section->min_price),
            'sections' => Model_Section::id_name(array(Model_Good::PAMPERS_SECTION, 28856)),

            'search_mode' => 'pampers',
            'search_query' => $query_string,
        )); // меню
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
