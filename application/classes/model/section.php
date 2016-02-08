<?php

class Model_Section extends ORM {

	use Seo;

    const CLOTHS_ROOT = 29690; // ид родительской категории для всей одежды
    const DIAPERS_ID = 29798; // ид категории подгузников
    const FOOD_ID = 29798; // ид категории питания
    const LINEN_ID = 29781; // ид пелёнок
    const MILK_ID = 29051; // ид категории молочной продукции (холодильник)

    protected $_table_name = 'z_section';

    protected $_belongs_to = [
        'img' => ['model' => 'file', 'foreign_key' => 'image'],
        'img93' => ['model' => 'file', 'foreign_key' => 'image93'],
        'parent' => ['model' => 'section', 'foreign_key' => 'parent_id'],
        'menu_img' => ['model' => 'file', 'foreign_key' => 'img_menu']
    ];

    protected $_has_many = [
        'filters' => ['model' => 'filter', 'foreign_key'   => 'section_id'],
        'brands'  => ['model' => 'brand', 'through'    => 'z_section_brand', 'foreign_key'   => 'section_id', 'far_key'   => 'brand_id'],
        'hits'  => ['model' => 'good', 'through'    => 'z_hit', 'foreign_key'   => 'section_id', 'far_key' => 'good_id'],
        'params'  => ['model' => 'section_param', 'foreign_key' => 'section_id'],
        'serts'   => [
           'foreign_key' => 'section_id',
           'model' => 'sert',
           'through' => 'z_sert_rel',
           'far_key' => 'sert_id'
        ],
        'childs' => ['model' => 'section', 'foreign_key' => 'parent_id']
    ];
    
    protected $_table_columns = [
        'id' => '', 'code' => '', 'name' => '', 'vitrina' => '', 'translit' => '', 'parent_id' => '', 'active' => '', 'sort' => '',
        'image' => '', 'image93' => '', 'img_menu' => '', 'max_price' => '', 'min_price' => '',
        'text' => '',
        'qty' => '',                           // Общее количество товаров в разделе
        'settings' => '',
		'h1' => '', 'title' => '', 'keywords' => '', 'description' => '',
        'empty_date' => '',
        'market_category' => '',
        'roditi' => ''
    ];

    const SHOW_OUT_OF_STOCK 	     = 0;
    const HIDE_OUT_OF_STOCK 	     = 1;
    const HIDE_OUT_OF_STOCK_STRICTLY = 2;

    const BUY_BUTTON_INCDEC = 0; // Вид кнопки покупки - по умолчанию с выбором количества
    const BUY_BUTTON_SIMPLE = 1; // Вид кнопки покупки - простой "в корзину"

    const SUB_NO = 0;
    const SUB_BRAND = 1;
    const SUB_FILTER = 2;

    const LIST_GOODS = 0;
    const LIST_TEXT = 1;
    const LIST_FILTER = 2;

    const EXPORTYML_CLOTHERS = 1;
	
	const CACHE_KEY_CATALOG = 'catalog'; // ключ кэша каталога
    protected $_reload_on_wakeup = FALSE;

    public $children = array(); // подкатегории падают сюда
    public $sub = array(); // контейнер для меню 3го уровня

    private $_settings = array();
    private $_tags = array(); // теговые из этой секции, для прямых ссылок на них

    public function clear()
    {
        $this->_settings = array();
        return parent::clear();
    }

    /**
     * Получение настроек категории
     * @param string $var
     * @return array|mixed
     */
    public function __get($var)
    {
        if ($var == 'seo') return $this->seo();

        if ($var != 'settings') return parent::__get($var);

        if ( ! empty($this->_settings)) return $this->_settings;

        $s = parent::__get($var);
		
        if ( ! is_array($s)) $s = json_decode($s, TRUE);

        if (empty($s)) $s = array();

        if ( ! empty($s['per_page']) && ! is_array($s['per_page'])) $s['per_page'] = explode(',', $s['per_page']);

		if( ! empty($s['orderByItems'])) $s['s'] = $s['orderByItems'][0];
		
		$this->_settings = $s + array(
            'm' => 1,
            's' => 'rating',
            'per_page' => [30,60,90],
            'row' => 3,
            'sub' => 0,
            'list' => 0,
            'orderByItems' => ["rating","name","price", "pricedesc","new"],
			'brands' => [],
            'goodTabs' => []
        );
        return $this->_settings;
    }

    /**
     * Получить бренды в категории, отсортированные как в ней настроено
     * @return array
     */
    public function getSortedBrands()
    {
		$sb = $this->brands->find_all()->as_array('id');
		
		$br = array();
		
		if( ! empty($this->settings['brands'])) {
			foreach($this->settings['brands'] as $bId) {
				if( ! empty($sb[$bId])) {
					$br[] = $sb[$bId];
					unset($sb[$bId]);
				}
			}
			
			usort($sb, function($a, $b) { return strcmp($a->name, $b->name); });

			if( ! empty( $sb ) ) {
				foreach( $sb as $item ) $br[] = $item;
			}
		} else {
			
			usort($sb, function($a, $b) { return strcmp($a->name, $b->name); });
			$br = $sb;
		}

        foreach($br as &$b) {
            if( ! empty($this->settings['b_hit'][$b->id])) {
                $b->hit = TRUE;
            }
        }
		
		return $br;
	}

    /**
     * Получить варианты настроек категории
     * @param null $type
     * @return mixed
     */
    public static function settings($type = NULL, $value = NULL)
    {
        $settings = Kohana::message('section/settings');

        if ( ! empty($settings[$type])) return $settings[$type];
        return $settings;
    }

    public function setting($type, $value = NULL)
    {
        $settings = $this->settings; // чтобы загрузились настройки
        
        if ( ! is_null($value)) 
        {
            $settings[$type] = $value;
            $this->_settings = $this->settings = $settings;
        }
        
        return $this->settings[$type];
    }
    
    /**
     * @static получить меню категорий в виде массива (верхнее меню)
     * @param bool $show_inactive  - показывать неактивные категории (для админки)
     * @param null $sVitrina
     * @return array
     */
    public static function get_catalog($show_inactive = FALSE, $sVitrina = null)
    {
        $catalog = [];

        $sVitrina = (is_null($sVitrina) ? Kohana::$server_name : $sVitrina);
        $cache_key = self::CACHE_KEY_CATALOG.md5(DOCROOT).$sVitrina.intval($show_inactive);
        $catalog_cache = Cache::instance()->get($cache_key);  
        if ( ! empty($catalog_cache)) $catalog = unserialize($catalog_cache);
        //$catalog = FALSE;
        if (empty($catalog))
        {
            $return = ORM::factory('section')
                ->with('menu_img')
                ->where('code', '!=', '50061508') // сертификаты не показываем
                ->order_by('parent_id')
                ->order_by('sort');

            if ( ! empty($sVitrina)) $return->where('vitrina', '=', $sVitrina);
            if ($show_inactive === FALSE) $return->where('active', '=', 1);

            $rarray = $return->find_all();
            $catalog = $zombies = array();

            foreach($rarray as $item) {
                if ($item->parent_id != 0) {
                    if( ! empty($catalog[$item->parent_id])) {
                        $catalog[$item->parent_id]->children[$item->id] = $item;
                        if ($item->settings['sub'] != Model_Section::SUB_NO) {
                            $s = new Sphinx('section', $item->id, FALSE);
                            $params = $s->stats();
                            $item->sub = []; // массив данных подкатегорий

                            if ($item->settings['sub'] == Model_Section::SUB_BRAND) { // третий уровень - бренды

                                foreach($params['brands'] as $b) {
                                    $item->sub[$b['id']] = ['href' => $s->href(['b' => [$b['id']]])] + $b;
                                }
                            }
                            if ($item->settings['sub'] == Model_Section::SUB_FILTER // третий уровень - фильтр
                                && ! empty($item->settings['sub_filter'])
                                && ! empty($params['vals'][$item->settings['sub_filter']])) {

                                foreach($params['vals'][$item->settings['sub_filter']] as $k => $v) {
                                    if (Model_Filter::big($item->settings['sub_filter'])) { // для одежды по большому типу, для категорий всё для мам - свои ссылки (третий уровень)
                                        $href = $item->get_link(0, $k);
                                    } else {
                                        $href = $s->href(['f' => [$item->settings['sub_filter'] => [$k]]]);
                                    }
                                    $item->sub[$k] = ['href' => $href] + $v;
                                }
                            }
                        }
                    } else {
                        $zombies[] = $item->parent_id;
                    }
                } else {
                    $catalog[$item->id] = $item;
                }
            }
            if ( ! empty($zombies)) {
                foreach($zombies as $z) { // пишем в лог, что у нас появились потерянные каталоги верхнего уровня
                    if( empty($catalog[$z])) {
                        Log::instance()->add(Log::INFO,'Zombie top level section #' . $z);
                    }
                }
            }

            Cache::instance()->set($cache_key, serialize($catalog));
        }

        return $catalog;
    }
    
    /**
     * Получить html для картинки категории
     * @return string
     */
    public function get_img()
    {
        if ($this->image == 0) return Model_File::empty_image();

        return $this->img->get_img(array(
            'alt' => $this->name,
            'title' => $this->name,
        ));
    }

    /**
     * Получить все теги секции в формате query => tag
     * @return Model_Tag[]
     */
    public function tags()
    {
        return $this->_tags = ORM::factory('tag')
            ->where('section_id', '=', $this->id)
            ->order_by('query')
            ->find_all()
            ->as_array('query');
    }

    /**
     * Получить url теговой если есть под неё условия, а в массиве оставить параметры для query_string
     * @param [] $arr массив данных
     * @return mixed
     */
    public function tag(&$arr, $filter)
    {
        ksort($arr);
        $href = http_build_query($arr); // полный запрос - по нему будем искать теговую

        $arr2 = $arr; // соберём запрос без фильтров - по нему тоже будем искать, если полный не найдём
        foreach($arr2 as $k => $v) {
            if (preg_match('~f(\d+)~', $k)) {
                unset($arr2[$k]);
            }
        }
        $href2 = http_build_query($arr2);

        $tag_found = FALSE;
        if ($href != 'c='.$this->id) { // пытаемся подобрать теговую только если условие не только на категорию
            if ( ! empty($this->_tags[$href])) {
                $href = '/' . $this->_tags[$href]->code;
                $tag_found = TRUE;
                foreach ($arr as $k => $a) { // убираем все параметры категория, бренд, фильтр
                    if ($k == 'c' || $k == 'b' || preg_match('~f(\d+)~', $k)) {
                        unset($arr[$k]);
                    }
                }
            } elseif ( ! empty($this->_tags[$href2])) {
                $href = '/' . $this->_tags[$href2]->code;
                $tag_found = TRUE;
                foreach ($arr as $k => $a) { // убираем все параметры категория, бренд
                    if ($k == 'c' || $k == 'b') {
                        unset($arr[$k]);
                    }
                }
            }
        }

        if ( ! $tag_found) {

            $href = $this->get_link(0, $filter); // у запроса надо стереть условие на категорию, остальное оставляем
            foreach($arr as $k => $a) {
                if ($k == 'c' || (preg_match('~f(\d+)~', $k) && $a == $filter)) {
                    unset($arr[$k]);
                }
            }
            unset($arr['c']);
        }
        return $href;
    }

    /**
     * Получить ссылку на категорию
     * @param bool $html
     * @param bool $with_filter - c учетом фильтра id значения фильтра по большому типу для одежды
     * @return string
     */
    public function get_link($html = true, $with_filter = FALSE)
    {
        $third_level = FALSE;

        if ( ! empty($with_filter)) {
            $sphinx = new Sphinx('section', $this->id, FALSE);
            foreach($sphinx->stats()['vals'] as $fid => $values) {
                if ( ! empty($values[$with_filter]) && Model_Filter::big($fid)) {
                    $third_level = TRUE;
                    break;
                }
            }
        }
        if ($third_level) {
            $href = sprintf('/catalog/%s/%d_%d.html', $this->translit, $this->id, $with_filter);
        } else {
            $href = sprintf('/catalog/%s', $this->translit, $this->id);
        }

        if ( ! empty(Kohana::$hostnames[$this->vitrina]['host'])) { // ссылка всегда содержит витрину!
            $href = Route::$default_protocol . Kohana::$hostnames[$this->vitrina]['host'] . $href;
        }

        return $html ? HTML::anchor($href, $this->name) : $href;
    }

    /**
     * Получить родительскую категорию
     * @throws Exception
     * @internal param bool $html
     * @return self
     */
    public function get_parent()
    {
        if ( ! $this->loaded()) throw new Exception ('Unable to get parent of not loaded section');
        if (0 == $this->parent_id) return FALSE;
        
        return ORM::factory('section', $this->parent_id);
    }

    /**
     * @static Получить список возможных родительский страниц
     * @param int $exclude
     * @return Database_Result
     */
    public static function parents($exclude = 0)
    {
        return ORM::factory('section')
            ->select(array('id', 'name'))
            ->where('parent_id', '=', 0)
            ->where('id', '!=', $exclude)
            ->order_by('sort')
            ->find_all()
            ->as_array('id', 'name');
    }

    public function admin_order()
    {
        return $this->with('parent')->order_by('sort', 'ASC');
    }

    /**
     * После сохранения товара в админке - сохранить его пропы
     */
    public function admin_save()
    {
        $errors = array();
		
        $misc = Request::current()->post('misc');
        
        if ( ! $this->parent_id) 
        {
            $this->setting('new', empty($misc['new']) ? 0 : 1);
            
            $this->save();
        }
        
        // сохранение новой картинки
        if ( ! empty($_FILES['img']) AND Upload::not_empty($_FILES['img']) AND Upload::valid($_FILES['img'])) {

            if ( ! Upload::image($_FILES['img'])) {
                $errors[] = Kohana::message('admin/section', 'img.default');
            } else { // пришла новая картинка

                $file = Model_File::image('img');
                $file->MODULE_ID = __CLASS__;
                $file->item_id = $this->id;
                $file->save(); // save original file

                if ($this->image) ORM::factory('file', $this->image)->delete(); // delete old

                $this->image = $file->ID; // save img
                $this->save();

                Model_History::log('section', $this->id, 'image', $file->ID);
            }
        }
        // сохранение новой для мобильной версии
        if ( ! empty($_FILES['img93']) AND Upload::not_empty($_FILES['img93']) AND Upload::valid($_FILES['img93'])) {

            if ( ! Upload::image($_FILES['img93'], 93, 96, TRUE)) {
                $errors[] = Kohana::message('admin/section', 'img93.default');
            } else { // пришла новая картинка

                $file = Model_File::image('img93');
                $file->MODULE_ID = __CLASS__;
                $file->item_id = $this->id;
                $file->save(); // save original file

                if ($this->image93) ORM::factory('file', $this->image93)->delete(); // delete old

                $this->image93 = $file->ID; // save img
                $this->save();

                Model_History::log('section', $this->id, 'image93', $file->ID);
            }
        }
        // сохранение новой картинки для меню
        if ( ! empty($_FILES['img_menu']) AND Upload::not_empty($_FILES['img_menu']) AND Upload::valid($_FILES['img_menu'])) {

            if ( ! Upload::image($_FILES['img_menu'], 200, 110, TRUE)) {
                $errors[] = Kohana::message('admin/section', 'img_menu.default');
            } else { // пришла новая картинка

                $file = Model_File::image('img_menu');
                $file->MODULE_ID = __CLASS__;
                $file->item_id = $this->id;
                $file->save(); // save original file

                if ($this->img_menu) ORM::factory('file', $this->img_menu)->delete(); // delete old

                $this->img_menu = $file->ID; // save img
                $this->save();

                Model_History::log('section', $this->id, 'image menu', $file->ID);
            }
        }
		
		$d = json_decode(file_get_contents('http://export.yandex.ru/inflect.xml?name=' . urlencode($this->name) . '&format=json'), TRUE);

        if ( ! empty($d[2])) {
			
			$this->roditi = $d[2];
			$this->save();
		}
		
        return ['errors' => $errors];
    }

    /**
     * Id - Name для чекбоксов
     * @param $idz
     * @return mixed
     */
    static public function id_name($idz)
    {
        return DB::select('id', 'name')
            ->from('z_section')
            ->where('id', 'IN', $idz)
            ->order_by('name')
            ->execute()
            ->as_array('id');
    }

    /**
     * При сохранении отрезать 7 брендов, сохранить настройки, скинуть кеш каталога
     * @param Validation $v
     * @return ORM|void
     */
    function save(Validation $v = NULL)
    {
        $s = $this->settings;
		if( ! empty( $s['brands'] ) ) $s['brands'] = array_slice($s['brands'], 0, 7);
		
        $this->_settings = $this->settings = json_encode($s);
		$was_changed = $this->changed();
        parent::save($v);
        $this->settings = $this->_settings = $s;
        if ($was_changed) {
            Cache::instance()->delete(self::CACHE_KEY_CATALOG.$this->vitrina.'0');
            Cache::instance()->delete(self::CACHE_KEY_CATALOG.$this->vitrina.'1');
        }
    }

    /**
     * сохранение публичных свойств при сериализации
     * @return string
     */
    public function serialize()
    {
        foreach (array('_primary_key_value', '_object', '_changed', '_loaded', '_saved', '_sorting', '_original_values',
                     'children', 'sub', 'menu_img', 'img_menu') as $var)
        {
            $data[$var] = $this->{$var};
        }
        return serialize($data);
    }

    public function get_tags()
    {
        return DB::select('code', 'anchor')
            ->from('z_tag')
            ->where('section_id', '=', $this->id)
            ->where('goods_count', '!=', 0)
            ->order_by('goods_count', 'DESC')
            ->execute()
            ->as_array('code', 'anchor');
    }

    /**
     * Получить категории одежды
     * @return mixed
     * @throws Cache_Exception
     */
    public static function get_cloth_subs()
    {
        $return = json_decode(Cache::instance()->get('cloth_subs'), TRUE);

        if (empty($return)) {
            $return = DB::select('id')
                ->from('z_section')
                ->where('parent_id', '=', self::CLOTHS_ROOT)
                ->where('active', '=', 1)
                ->execute()
                ->as_array('id', 'id');
            Cache::instance()->set('cloth_subs', json_encode($return));
        }
        return $return;
    }

    /**
     * Определяем не относится ли категория к типу одежды
     */
    public function is_cloth()
    {
        $cloth_subs = self::get_cloth_subs();
        return ! empty($cloth_subs[$this->id]);
    }

    /**
     * проверяет что категория дает возможность бесплатной сборки
     * @param $id - ид категории
     * @return mixed
     */
    public static function sborkable($id)
    {
        $cache_key = 'section_sborkable';
        $ids = json_decode(Cache::instance()->get($cache_key), TRUE);

        if (empty($ids)) {
            $ids = DB::select('id')
                ->from('z_section')
                ->where('code', 'IN', ['1474', '50056991', '1081', '1110', '1124', '1426', '1491', '30016536', '30016537', '352', '1340', '1411', '1431'])
                ->execute()
                ->as_array('id', 'id');

            $cache = json_encode($ids);
            Cache::instance()->set($cache_key, $cache);
        }
        return ! empty($ids[$id]);
    }
}