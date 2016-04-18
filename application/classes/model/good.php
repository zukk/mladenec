<?php
/*
* @property id
* @property name
* @property group_name
* @property translit
* @property section_id
* @property brand_id
* @property group_id
* @property active
* @property order
* @property image
* @property code
* @property price
* @property pack
* @property rating
* @property review_qty
* @property qty
* @property popularity
* @property upc
* @property old_price
* @property barcode
* @property show
*/

class Model_Good extends ORM {

    use Seo;

    const PAMPERS_SECTION = 29798;
    const PAMPERS_BRAND = 51566;
    const OZON_MIN_QTY = 5;
    const SBORKA_ID1C = 'Uslug1790014'; // 50055179; // код товара - услуга сборки КГТ

    const TYPE_PRICE_DEFAULT = 0;
    const TYPE_PRICE_LK      = 1;
    const TYPE_PRICE_BUY     = 2;

    public $quantity = 0; // FOR CART количество штук заказано
    public $total = 0; // FOR CART сумма денег заказано
    public $order_comment = ''; // FOR CART комментарий к товару
    public $goods_ids = ''; // список id для викимарта
    public $wiki_cat_id = ''; // id викимарта

    public $grouped = 1; // for search - число товаров в группе падает в этот параметр для товаров - групп

    protected $_table_name = 'z_good';
    public $totalFrequently = 0;

    protected $_belongs_to = array(
        'group' => array('model' => 'group', 'foreign_key' => 'group_id'),
        'section' => array('model' => 'section', 'foreign_key' => 'section_id'),
        'wikicategories' => array('model' => 'wikicategories', 'foreign_key' => 'wiki_cat_id'),
        'googlecategories' => array('model' => 'googlecategories', 'foreign_key' => 'google_cat_id'),
        'ozontype' => array('model' => 'ozontype', 'foreign_key' => 'ozon_type_id'),
        'brand' => array('model' => 'brand', 'foreign_key' => 'brand_id'),
        'country' => array('model' => 'country', 'foreign_key' => 'country_id'),
        'promo' => array('model' => 'promo', 'foreign_key' => 'promo_id'),
        'img' => array('model' => 'file', 'foreign_key' => 'image')
    );
    protected  $_has_one = array(
        'prop' => array('model' => 'good_prop', 'foreign_key' => 'id')
    );

    protected $_has_many = array(
        'text' => array('model' => 'good_text', 'foreign_key' => 'good_id'),
        'reviews' => array('model' => 'good_review', 'foreign_key' => 'good_id'),
        'imgs' => array('model' => 'file', 'through' => 'z_good_img', 'foreign_key' => 'good_id', 'far_key' => 'file_id'),
        'filters' => array('model' => 'filter_value', 'through' => 'z_good_filter', 'foreign_key' => 'good_id', 'far_key' => 'value_id'),
        'tags' => array('model' => 'tag', 'through' => 'z_good_tag', 'foreign_key' => 'good_id', 'far_key' => 'tag_id'),
        'promos' => array('model' => 'promo', 'through' => 'z_promo_good', 'foreign_key' => 'good_id', 'far_key' => 'promo_id'),
        'actions' => array('model' => 'action', 'through' => 'z_action_good', 'foreign_key' => 'good_id', 'far_key' => 'action_id'),
    );

    protected $_table_columns = array(
        'id'              => '',
        'name'            => '',
        'group_name'      => '',
        'translit'        => '',
        'section_id'      => '',
        'brand_id'        => '',
        'group_id'        => '',
        'country_id'      => '',
        'active'          => '', // активность [0] - 404, [1] - продаем, [2] - показываем но не продаем, нет в поиске
        'order'           => 0,
        'image'           => '',
        'move'            => '',
        'code'            => '', // Артикул
        'code1c'          => '', // Артикул 1С
        'id1c'            => 0,  // Код 1С, уникальный цифровой
        'price'           => '', // Базовая цена
        'price_lk'        => '', // Цена любимого клиента
        'price_buy'       => '', // Закупочная цена
        'price_sale'      => '', // Цена распродажи 
        'price_ts'        => '', // int Время последнего изменения базовой цены  UNIX_TIMESTAMP
        'price_sale_from' => '', // int Время начала действия цены распродажи UNIX_TIMESTAMP
        'price_sale_to'   => '', // int Время конца действия цены распродажи UNIX_TIMESTAMP
        'nds'             => '', // Размер ставки НДС для данного товара 
        'pack'            => '',
        'per_pack'        => '', // число штук в пачке (для подгузов)
        'rating'          => '',
        'review_qty'      => '',
        'qty'             => '',
        'popularity'      => '',
        'upc'             => '', // штрихкод для channel intelligence
        'old_price'       => '',
        'barcode'         => '',  // штрихкод
        'big'             => '',
        'promo_id'        => '',
        'show'            => 0,  // Отображать на сайте?
        'new'             => 0,  // флаг новинки
        'zombie'          => 0,   // флаг зомби
        'wiki_cat_id'     => 0,   // id викимарт категории
        'ozon_type_id'    => 0,   // id озон категории
        'google_cat_id'   => 0,   // id google merchant
        'seo_auto'        => 0,
        'order_search'    => 0
    );
	
	protected $filters_data;

    public function events()
    {
        $events = [];

        if ( ! $this->pk()) $events[Model_Event::T_GOOD_ADD] = '';

        if ($this->show AND $this->changed('show'))
        {
            $events[Model_Event::T_GOOD_SHOW] = '';

            $appear = DB::select('id')
                ->from('z_event')
                ->where('item_id', '=', $this->pk())
                ->where('type', '=', Model_Event::T_GOOD_SHOW)
                ->execute()
                ->get('id');
            if ( ! $appear) $events[Model_Event::T_GOOD_APPEAR] = '';
        }

        if ( ! $this->show AND $this->changed('show'))  $events[Model_Event::T_GOOD_HIDE] = '';

        if ( 0 != $this->qty AND $this->changed('qty')) $events[Model_Event::T_GOOD_INSTOCK] = '';
        
        if ( 0 == $this->qty AND $this->changed('qty')) $events[Model_Event::T_GOOD_OUTSTOCK] = '';
        
        if ( ! $this->show AND $this->changed('show'))  $events[Model_Event::T_GOOD_HIDE] = '';
        
        if ($this->changed('price'))                    $events[Model_Event::T_GOOD_PRICE_CHANGE] = '';
        
        return $events;
    }
    
    /**
     * 
     * @param bool $lk
     * @return float
     */
    public function get_price($lk = FALSE)
    {
        $price = $this->price;
        
        if ($lk AND $this->price_lk > 0) $price = $this->price_lk;
        
        if (
                    $this->price_sale      > 0 
                AND $this->price_sale_from > 0 
                AND $this->price_sale_to   > time()
                )
        {
            $price = $this->price_sale;
        }
        
        return $price;
    }

    /**
     * Возвращает ссылку на товар
     * @param bool $html
     * @return string
     */
    public function get_link($html = TRUE)
    {
        $href = sprintf('/product/%s/%d.%d.html', $this->translit, $this->group_id, $this->id);

        return $html ? HTML::anchor($href, $this->name) : $href;
    }
    
    /**
     * Возвращает ссылку на товар
     * @param bool $html
     * @return string
     */
    public function get_link_admin($html = TRUE)
    {
        $href = Route::url('admin_edit',array('model' => 'good', 'id' => $this->id));

        return $html ? HTML::anchor($href, $this->name) : $href;
    }

    /**
     * Ссылка на отзывы о товаре
     * @return string
     */
    public function get_review_link() {
        return sprintf('/product/%s/%d.%d.html#reviews', $this->translit, $this->group_id, $this->id);
    }
    
    /**
     * Пересчет количества активных отзывов
     * @return int
     */
    public function review_count()
    {
        $this->review_qty = DB::select(DB::expr('count(`id`) as `cnt`'))
            ->from('z_good_review')
            ->where('good_id', '=', $this->id)
            ->where('active', '=', 1)
            ->execute()->get('cnt');
        
        $this->save(); // Сохраняем тут, чтобы обновленное значение посчиталось в группе
        $this->group->review_count();
        return $this->review_qty;
    }

    /**
     * Уменьшает $qty до максимального доступного для приобретения в 1 заказе
     * @param $qty_ordered
     * @return int
     */
    public function buy_limit($qty_ordered)
    {
		// временная мера для merries
		$limits = []; // [$good_id => $limit,...]
		
        if ($this->qty != -1 AND $qty_ordered > $this->qty) $qty_ordered = $this->qty;
        
        if ( isset($limits[$this->pk()]) AND $qty_ordered > $limits[$this->pk()]) {
            return $limits[$this->pk()];
        }
        return $qty_ordered;
    }
    
    /**
     * Возвращает массив, ключ - ид товара, значение - цена для нужного статуса пользователя
     * @static
     * @param int $status_id
     * @param int|array $id - массив идентификаторов товара или один идентификатор
     * @return array
     */
    public static function get_status_price($status_id, $id)
    {
        if ( ! is_array($id)) $id = array($id);
        if (empty($id)) return array();

        $return = DB::select('good_id', 'price')
            ->from('z_price')
            ->where('status_id', '=', $status_id)
            ->where('good_id', 'IN', $id)
            ->execute()
            ->as_array('good_id', 'price');

        return $return;

    }

    /**
     * Обновить параметры групп, брендов и секций, согласно товарам (меняет мин цену, макс цену, картинку группы, товар, бренд, кол-во)
     */
    public static function refresh()
    {
//        Database::instance()->begin();

        DB::query(Database::INSERT, '
            INSERT IGNORE INTO z_group(id, active, min_price, max_price, image, good_id, brand_id, qty)
            SELECT group_id, MAX(IF(active != 0, 1, 0)), MIN(price), MAX(price), img255, MAX(IF(`show`, z_good.id, 0)), MAX(IF(active != 0, z_good.brand_id, 0)), SUM(active)
              FROM `z_good`
              JOIN z_good_prop ON (z_good.id = z_good_prop.id)
            WHERE price > 0
              GROUP BY group_id ORDER BY z_good.id
            ON DUPLICATE KEY UPDATE
                active = VALUES(active), min_price = VALUES(min_price), max_price = VALUES(max_price), brand_id = VALUES(brand_id),
                image = VALUES(image), good_id = VALUES(good_id), qty = VALUES(qty)
        ')->execute();

        // Обновить привязанные к разделу бренды
        DB::delete('z_section_brand')->execute();
        DB::query(Database::INSERT, 'INSERT IGNORE INTO z_section_brand
            SELECT section_id, brand_id FROM z_group WHERE active = 1
        ')->execute();

        // Обновить параметры раздела
        DB::query(Database::INSERT, 'INSERT INTO z_section (id, max_price, min_price, qty)
            SELECT section_id, MAX(max_price), MIN(min_price), SUM(qty)
              FROM z_group
            WHERE active = 1 AND qty > 0
              GROUP BY section_id
            ON DUPLICATE KEY UPDATE
              max_price = VALUES(max_price),
              min_price = VALUES(min_price),
              qty = VALUES(qty)
        ')->execute();

        // пересчитать рейтинг и отзывы товаров и групп
        DB::query(Database::UPDATE, "UPDATE z_good SET rating = 0, review_qty = 0")->execute();
        DB::query(Database::INSERT, 'INSERT INTO z_good (rating, review_qty, id)
            SELECT AVG(z_good_review.rating) as r, count(z_good_review.id) as q, good_id
              FROM z_good_review
              JOIN z_good ON (z_good.id = good_id)
            WHERE good_id > 0 AND z_good_review.active = 1 AND z_good_review.rating > 0
              GROUP BY good_id
            ON DUPLICATE KEY UPDATE
              z_good.rating = VALUES(rating),
              z_good.review_qty = VALUES(review_qty)
        ')->execute();

        DB::query(Database::UPDATE, "UPDATE z_group SET rating = 0, review_qty = 0")->execute();
        DB::query(Database::INSERT, 'INSERT INTO z_group(id, rating, review_qty)
          SELECT group_id, AVG(rating), SUM(review_qty)
            FROM `z_good`
          WHERE rating > 0 AND (z_good.big = 1 OR z_good.section_id IN
            (
              SELECT id FROM z_section WHERE active = 1 AND parent_id = '.Model_Section::CLOTHS_ROOT.'
            )
          )
          GROUP BY group_id

          ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_qty = VALUES(review_qty)
        ')->execute();

        // прописываем всем товарам как группе
        DB::query(Database::INSERT, 'INSERT INTO z_good(id, rating, review_qty)
          SELECT g.id, gr.rating, gr.review_qty
            FROM z_good g
            LEFT JOIN z_group gr ON (g.group_id = gr.id)
          WHERE (g.big = 1 OR g.section_id IN
            (
              SELECT id FROM z_section WHERE active = 1 AND parent_id = '.Model_Section::CLOTHS_ROOT.'
            )
          )
          ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_qty = VALUES(review_qty)
        ')->execute();

        // чистка старых новинок
        DB::query(Database::UPDATE, 'UPDATE  z_good g, z_good_prop gp
            SET g.new = 0, gp.new_till = NULL
            WHERE new_till IS NOT NULL AND new_till < now() AND g.id = gp.id
        ')->execute();

        // обновить флаг одежды в товарах из одежды (и наоборот)
        DB::query(Database::UPDATE, 'UPDATE z_group
          SET good = 0 WHERE section_id NOT IN (
            SELECT id
            FROM z_section
            WHERE parent_id = 29690)
        ')->execute();

        DB::query(Database::UPDATE, 'UPDATE z_group
          SET good = 1 WHERE section_id IN (
            SELECT id
            FROM z_section
            WHERE parent_id = 29690)
        ')->execute();

        // обновить цвета одежды
        DB::query(Database::DELETE, 'TRUNCATE good_color')->execute();
        DB::query(Database::INSERT, 'INSERT INTO good_color (good_id, color, group_id)
          SELECT g.id, GROUP_CONCAT(gf.value_id-16294 ORDER BY gf.value_id ASC), g.group_id
          FROM z_good g
          INNER JOIN z_group gr ON (gr.good = 1 and g.group_id = gr.id)
            LEFT JOIN z_good_filter gf ON (g.id = gf.good_id and gf.filter_id = 1952)
          WHERE g.show = 1 AND g.qty != 0
            GROUP BY g.id'
        )->execute();

//        Database::instance()->commit();

        if (Kohana::DEVELOPMENT == Kohana::$environment) {
            // сфинкс запускать от www-data
            passthru('indexer --rotate --all', $return); // переиндексация
            echo '<pre>' . $return . '</pre>';
        }

        // Перерасчет данных акций
        $current_user = Model_User::i_robot();
        Model_Action::activator($current_user);

    }

    /**
     * Проставить товару цену для статуса
     * @param $status_id
     * @param $prices
     * @param $doc_id
     * @return object
     */
    public static function set_status_prices($status_id, $prices, $doc_id)
    {
        $begin = 'INSERT IGNORE INTO z_price (`good_id`, `status_id`, `price`, `doc_id`) VALUES ';
        // $end = 'ON DUPLICATE KEY UPDATE price = VALUES(price)';
        
        $middle = array();
        
        foreach($prices as $good_id => $p)
        {
            $middle .= sprintf("(%d, %d, %f, %d)" , $good_id, $status_id, $p, $doc_id);
        }
        
        $q = DB::query(Database::INSERT, $begin . implode(', ', $middle));
        
        return $q->execute();
    }
    
    /**
     * Проставить товару цену для статуса
     * @param $status_id
     * @param $price
     * @param $doc_id
     * @return object
     */
    public function set_status_price($status_id, $price, $doc_id = 0)
    {
        return DB::query(Database::INSERT, sprintf("
            INSERT INTO z_price (good_id, status_id, price)
            VALUES (%d, %d, %f)
            ON DUPLICATE KEY UPDATE price = VALUES(price)",
            $this->id, $status_id, $price)
        )->execute();
    }

    /**
     * Получить исходники картинок
     * @return array
     * @throws Kohana_Exception
     */
    public function get_src_images()
	{
        $imgs = ORM::factory('file')
            ->where('MODULE_ID', '=', 'Model_Good')
            ->where('item_id', '=', $this->id)
            ->where('original', '=', 0)
            ->find_all()
            ->as_array('ID');

        return $imgs;
    }
    
    /**
     * Получить картинки товара (по размеру?)
     * Возвращает массив размер => картинки
     * @return array
     */
    public function get_images()
    {
        $return = array();

        // получить все прикрепленные картинки id => size
        $img2size = DB::select('file_id', 'size')
            ->from('z_good_img')
                ->where('good_id', '=', $this->id)
			->order_by('id')
            ->execute()
            ->as_array('file_id', 'size');

        if (empty($img2size)) return $return;

        // Получить все объекты файлов кучей
		$ids = array_keys($img2size);
        $imgs = ORM::factory('file')
            ->where('ID', 'IN', $ids)
            ->find_all()
            ->as_array('ID');
		
		if (empty($imgs)) return $return;

        // разложить по размерам array( $i => array(70 => ID,255 => ID,1600 => ID), $i+1 => array(70 => ID,255 => ID,1600 => ID))
        $sizes = array('70'=> 0, '255' => 0, '380' => 0, '1600' => 0, '380x560' => 0, '173x255' => 0);
        foreach($img2size as $id => $size) {
            $i = $sizes[$size];
            $return[$i][$size] = $imgs[$id];
            $sizes[$size]++;
        }
        return $return;
    }

    /**
     * Возвращает массив id товара => (размер => картинка)
     * @param array $sizes
     * @param array $ids
     * @return array
     */
    public static function many_images($sizes = [255], array $ids = [], $all_photos = FALSE)
    {
        $return = [];
        if (empty($ids)) return $return;

        // получить только оригиналы картинок
        if (in_array('originals', $sizes)) {
            // сначала пробуем получить все 1600е картинки с оригиналами - в порядке как 1600-е
            $_origs = DB::select('f1.ID', 'good_id')
                ->from(['z_good_img', 'gi'])
                ->join(['b_file', 'f'])
                    ->on('file_id', '=', 'f.ID')
                ->join(['b_file', 'f1'])
                    ->on('f.original', '=', 'f1.ID')
                ->where('size', '=', '1600')
                ->where('good_id', 'IN', $ids)
                //->where('good_id', '=', 197919)
                ->order_by('good_id')
                ->order_by('gi.id')
                ->execute()
                ->as_array('ID', 'good_id');

            if ( ! empty($_origs)) { //

                //print_r($_origs);

                $files = ORM::factory('file')
                    ->where('ID', 'IN', array_keys($_origs))// в этом поле id товара к которому прикреплена картинка
                    ->find_all()
                    ->as_array('ID');

                foreach($_origs as $id => $good_id) { // упорядочить как было
                    $_origs[$id] = $files[$id];
                }
                $files = $_origs;
            }

            if ( ! empty($files)) {
                foreach ($files as $file_id => $data) {
                    $return[$data->item_id]['originals'][] = $data;
                }
            }
            
            $not_found = array_diff($ids, array_keys($return)); // id товаров без оригиналов
            if (empty($not_found)) return $return;
            
            $imgs = DB::select('img500', 'id') // если нет оригинала - поищем картинки 500х500
                ->from('z_good_prop')                    
                ->where('id', 'IN', $not_found)
                ->where('img500', '!=', 0)
                ->execute()
                ->as_array('img500', 'id');

            if (empty($imgs)) return $return;
            
            $files = ORM::factory('file')
                ->where('ID', 'IN', array_keys($imgs))
                ->find_all()
                ->as_array('ID');
            
            if (empty($files)) return $return;
            
            foreach ($imgs as $file_id => $good_id) {
                if ( ! empty($files[$file_id])) {
                    $return[$good_id]['originals'][] = $files[$file_id];
                }
            }
            
            return $return;
        }

        if (in_array('500', $sizes)) { // получить только картинки500

            $imgs = DB::select('img500', 'id')
                ->from('z_good_prop')
                ->where('id', 'IN', $ids)
                ->where('img500', '!=', 0)
                ->execute()
                ->as_array('img500', 'id');

            if (empty($imgs)) return $return;

            $files = ORM::factory('file')
                ->where('ID', 'IN', array_keys($imgs))
                ->find_all()
                ->as_array('ID');

            if (empty($files)) return $return;

            foreach ($imgs as $file_id => $good_id) {
                if ( ! empty($files[$file_id])) {
                    $return[$good_id]['500'][] = $files[$file_id];
                }
            }

            return $return;
        }

        // получить все прикрепленные картинки id => size
        $imgs = DB::select()
            ->from('z_good_img')
            ->where('good_id', 'IN', $ids)
            ->where('size', 'IN', $sizes)
            ->order_by('id')
            ->execute()
            ->as_array('file_id');

        if (empty($imgs)) return $return;

        // Получить все объекты кучей
        $files = ORM::factory('file')
            ->where('ID', 'IN', array_keys($imgs))
            ->find_all()
            ->as_array('ID');

        if (empty($files)) return $return;

        foreach ($imgs as $file_id => $data) {
            if ( ! empty($files[$file_id])) {
                if ( ! $all_photos) {
                    if (empty($return[$data['good_id']][$data['size']])) {
                        $return[$data['good_id']][$data['size']] = $files[$file_id];
                    }
                } else {
                    $return[$data['good_id']][$data['size']][] = $files[$file_id];
                }
            } // Если картинки нет то в следующем цикле вставится заглушка
        }

        foreach ($ids as $good_id) { // fill empty images if any
            foreach ($sizes as $size) {
                if (empty($return[$good_id][$size]))
                    $return[$good_id][$size] = new Model_File();
            }
        }
        return $return;
    }

    /**
     * Получить url картинки товара 255 размера (за ней не надо лезть в prop)
     * @param null|Model_File[] $imgs - массив возможных картинок из many_images
     * @return string
     */
    public function get_img($imgs = NULL)
    {
        $return = '/images/no_pic70.png';
        if ( empty($this->image)) return $return;
        if (is_array($imgs)) { // картинки только из массива пробуем брать
            if ( ! empty($imgs[$this->id])) {
                $return = $imgs[$this->id][255]->get_url();
            }

        } else {
            $return = ORM::factory('file', $this->image)->get_url();
        }
        return $return;
    }

    /**
     * Стереть картинки товара
     * @param $img_ids
     */
    protected function unbind_images(array $img_ids)
    {
        if ( ! empty($img_ids)) {
            DB::delete('z_good_img')
                ->where('good_id', '=', $this->id)
                ->where('file_id', 'IN', $img_ids)
                ->execute();
        }
    }

    /**
     * Прицепить картинки товара
     * @param Model_File $_70
     * @param Model_File $_255
     * @param Model_File $_1600
     * @return void
     */
    protected function bind_image(Model_File $_70, Model_File $_255, Model_File $_380, Model_File $_1600, Model_File $_380x560, Model_File $_173x255)
    {
        DB::insert('z_good_img')
            ->columns(['good_id', 'file_id', 'size'])
            ->values([$this->id, $_70->ID, 70])
            ->values([$this->id, $_255->ID, 255])
            ->values([$this->id, $_380x560->ID, '380x560'])
            ->values([$this->id, $_173x255->ID, '173x255'])
            ->values([$this->id, $_380->ID, 380])
            ->values([$this->id, $_1600->ID, 1600])
            ->execute();

        DB::update('b_file')
            ->set(['MODULE_ID' => 'Model_Good', 'item_id' => $this->id])
            ->where('ID', 'IN', [$_70->ID, $_255->ID, $_380x560->ID, $_173x255->ID, $_380->ID, $_1600->ID])
            ->execute();
    }
    
    /**
     * Получить страницу товаров из набора
     * @static
     * @param $set_id номер набора
     * @param $total
     * @param $page
     * @param int $per_page
     * @param bool $cycle
     * @return array|bool
     */
    public static function get_set_slider($set_id, &$total, $page, $per_page = Pager::PER_PAGE, $cycle = FALSE,$skip_ids = array())
    {
        $cache = Cache::instance();

        $cache_key = 'good_set_slider_' . $set_id;
        $cache_ids = $cache->get($cache_key); 
        if ($cache_ids) {
            $ids = unserialize($cache_ids);
        } else {
            $set = ORM::factory('good_set', $set_id);
            if ( ! $set->loaded()) return FALSE;
            
            $ids = $set->get_good_ids(50,0,TRUE,1); // Только первые 50 отображаемых в наличии
            
            if (count($ids)) {
                $cache->set($cache_key, serialize($ids), 3600); // кэшируем список id товаров слайдера на 1 час
            }
        }
        
        $total = count($ids);
        if ( ! empty($skip_ids)) {
            $skip_ids = array_combine($skip_ids, $skip_ids);
            $ids = array_diff_key($ids,$skip_ids);
        }
        
        if ( ! $total) return FALSE;
        if ($cycle) { // отгрызаем по модулю
            
            $offset = (($page - 1) * $per_page + $total) % $total;
            if ($offset < 0) { $offset = $total + $offset; }
            
            $page_ids = array_slice($ids, $offset, $per_page);
            $found = count($page_ids);
            
            if ($found < $per_page) {
                $page_ids = array_merge($page_ids, array_slice($ids, 0, $per_page - $found));
            }
        } else {
            $page_ids = array_slice($ids, ($page - 1) * $per_page, $per_page);
        }
        if (empty($page_ids)) return FALSE;

        return ORM::factory('good')->with('prop')->where('good.id', 'IN', $page_ids)->where('show', '=', 1)->where('qty', '!=', 0)->find_all()->as_array('id');
    }

    /**
     *  Получить товары, участвующие в промоакции вместе с этим
     * 
     * @param int $active
     * @param int $page
     * @param int $per_page
     * @return SELF
     * @throws Exception
     */
    public function get_bundled($active = NULL, $page = 1, $per_page = 5) {
        if ( ! $this->id) throw new Exception('Cannot get bundled for no object');
        
        $cache = Cache::instance();
        $key = 'bundled.'.$this->id;

        $goods_arr = $cache->get($key);
        
        if ( ! $goods_arr OR TRUE) {
            
            $promos = $this->promos;
            if ( ! is_null($active)) {
                // show - потому что переделали логику, аналог active
                $promos->where('show','=',$active);
            }
            $promos_arr = $promos->distinct('id')->find_all()->as_array('id');

            if ( empty($promos_arr)) return FALSE;

            $promo_good_ids = array();

            foreach ($promos_arr as $promo) {
                $promo_good_ids[] = $promo->id;
            }

            $goods = DB::query(Database::SELECT, 
                    'SELECT DISTINCT `z_good`.* 
                        FROM `z_good`,`z_promo_good`
                        WHERE `z_good`.`id` = `z_promo_good`.`good_id`
                            AND `z_good`.`id` <> :currend_good_id
                            AND `z_good`.`qty` > 0
                            AND `z_promo_good`.`promo_id` IN (' . implode(',', $promos_arr) . ')
                        ORDER BY `z_good`.`brand_id` ASC, `z_good`.`group_name` ASC, `z_good`.`name` ASC
                        '
                    );
            $goods->param(':currend_good_id', $this->id);

            $goods_arr = $goods->as_object('Model_Good')->execute()->as_array('id');
            
            /* бандлы в кеш, на сутки */
            $cache->set($key, $goods_arr, 24 * 3600);
        }
        if (empty($goods_arr)) return FALSE;
        
        $total = count($goods_arr);
        $offset = (($page - 1) * $per_page + $total) % $total;
        if ($offset < 0) { $offset = $total + $offset; }
        $promo_goods = array_slice($goods_arr, $offset, $per_page);
        
        $found = count($promo_goods);
        if ($found < $per_page AND $total > $per_page) {
            $promo_goods = array_merge($promo_goods, array_slice($goods_arr, 0, $per_page - $found));
        }
        
        if (empty($promo_goods)) return FALSE;

        return $promo_goods;
    }

    /**
     * @param bool $only_active
     * @return Model_Promo[]
     */
    public function get_promos($only_active = FALSE) {
        $promos = array();
        
        /* Получаем промоакции, к которым товар привязан через бренд */
        $brand = FALSE;
        if ($this->brand_id > 0) {
            try {
                $brand = $this->brand;
            } catch (Exception $e) {
                $brand = FALSE;
                Log::instance()->add(Log::INFO, 'Good #' . $this->id . ' attached to an not existent brand #' . $this->brand_id);
                /**
                 * @todo Может быть обнулять id несуществующих брендов? Могут ли они откуда-то проявиться?
                 */
            }
        }
        if ($brand) {
            if ($only_active) { 
                if ($brand->active) {
                    $promos = $brand->promos
                            ->where('promo.active','=',1)
                            ->find_all()->as_array();
                }
            } else {
                $promos = $brand->promos->find_all()->as_array();
            }
        }
        
        /* Получаем промоакцию, к которой непосредственно привязан товар */
        $promo = FALSE;
        if ($this->promo_id > 0) {
            try {
                $promo = $this->promo;
                
            } catch (Exception $e) {
                $promo = FALSE;
                /* Ugh, no promo */
                Log::instance()->add(Log::INFO, 'Good #' . $this->id . ' attached to an not existent promo #' . $this->promo_id);
                /**
                 * @todo Может быть обнулять id несуществующих промоакций? Появиться они ниоткуда не могут.
                 */
            }
        }
        
        if ($promo) { 
            if($only_active) { 
                if ($promo->active) {
                    $promos[] = $this->promo;
                }
            } else {
                $promos[] = $this->promo;
            }
        }
        
        return $promos;
    }
    
    /**
     * 
     * @param int $active
     * @param int $page
     * @param int $per_page
     * 
     * @return Model_Good
     */
    public function get_promo_goods($active = NULL, $page = 1, $per_page = 5) {
        
        $promos = $this->get_promos();
        $bundled_goods = array();
        foreach ($promos as $promo) {
            $bg = $promo->get_goods($active);
            $bundled_goods = array_merge($bundled_goods, $bg);
        }
        
        if ( ! count($bundled_goods)) return FALSE;
        
        $total = count($bundled_goods);
		$this->totalFrequently = $total;
		
        $offset = (($page - 1) * $per_page + $total) % $total;
        if ($offset < 0) { $offset = $total + $offset; }
        $promo_goods = array_slice($bundled_goods, $offset, $per_page);
        
        $found = count($promo_goods);
        if ($found < $per_page AND $total > $per_page) {
            $promo_goods = array_merge($promo_goods, array_slice($bundled_goods, 0, $per_page - $found));
        }
        
        return $promo_goods;
    }
    
    /**
     * Получить товары, которые заказывают с этим, упорядочено по кол-ву
     *
     * @param int $page
     * @param int $per_page
     * @throws Exception
     * @return bool
     */
    public function get_frequent($page = 1, $per_page = 5) {
        if (!$this->id)
            throw new Exception('Cannot get frequent for no object');

        $cache = Cache::instance();
        $key = 'frequent.' . $this->id;

        $page_ids = $cache->get($key);
        if (!$page_ids OR TRUE) {
            $more = DB::select('gg.min_good_id', 'gg.qty')
                    ->from(array('z_good_good', 'gg'))               
                    ->join(array('z_good', 'g'), 'LEFT')
                    ->on('gg.min_good_id', '=', 'g.id')
                    ->where('gg.max_good_id', '=', $this->id)
                    ->where('g.show', '=', 1)
                    ->where('g.qty', '!=', 0)
                    ->order_by('gg.qty', 'DESC')
                    ->limit(100)
                    ->execute()
                    ->as_array('min_good_id', 'qty');

            $less = DB::select('gg.max_good_id', 'gg.qty')
                    ->from(array('z_good_good', 'gg'))                    
                    ->join(array('z_good', 'g'), 'LEFT')
                    ->on('gg.max_good_id', '=', 'g.id')
                    ->where('min_good_id', '=', $this->id)
                    ->where('g.show', '=', 1)
                    ->where('g.qty', '!=', 0)
                    ->order_by('gg.qty', 'DESC')
                    ->limit(100)
                    ->execute()
                    ->as_array('max_good_id', 'qty');

            $frequent = $more + $less;
            arsort($frequent);
            $page_ids = array_keys($frequent);

            $cache->set($key, $page_ids, 24 * 3600); // что с чем заказывают, на сутки
        }

        $total = count($page_ids);
        $this->totalFrequently = $total;

        if (!$total)
            return FALSE;
        $offset = (($page - 1) * $per_page + $total) % $total;
        if ($offset < 0) {
            $offset = $total + $offset;
        }
        $slice_ids = array_slice($page_ids, $offset, $per_page);
        $found = count($slice_ids);
        if ($found < $per_page) {
            $slice_ids = array_merge($slice_ids, array_slice($page_ids, 0, $per_page - $found));
        }

        if (empty($slice_ids))
            return FALSE;

        $return = ORM::factory('good')->with('prop')
                ->where('good.id', 'IN', $slice_ids)
                ->find_all()
                ->as_array('id');

        return $return;
    }

    /**
     * Получение тегов товара, упорядоченных по сортировке
     * @return Model_Tag[]
     */
    public function get_tags()
    {
        $tags = DB::select('t.*')
            ->from(array('z_good_tag', 'gt'))
                ->join(array('z_tag', 't'))
                ->on('gt.tag_id', '=', 't.id')
            ->where('gt.good_id', '=', $this->id)
            ->order_by('gt.sort')
            ->as_object('Model_Tag')
            ->execute();

        return $tags->as_array();
    }

    /**
     * вытаскивает товары для XML пачками
     * 
     * @param int $heap_size сколько товаров вытаскивать в 1 пачке
     * @param int $heap_number сколько пачек уже вытащено
     * @param array $where - доп условия
     * @return boolean
     */
    public static function for_yml($heap_size, $heap_number, $where = NULL, $type = FALSE)
    {
        $active_top_sections = DB::select('z_section.id')
                ->from('z_section')
                ->where('parent_id','=',0)
                ->where('active','=',1)
                ->execute()->as_array();
        
        $query = DB::select('good.*', 'file.SUBDIR', 'file.FILE_NAME','prop.img1600','prop.img500','prop.desc',
                ['brand.name','brand_name'],
                ['section.name','section_name'],
                ['section.market_category','market_category']
            )->from(['z_good', 'good'])

                ->join(['z_good_prop',   'prop'])    ->on('good.id',         '=', 'prop.id')
                ->join(['z_brand',       'brand'])   ->on('good.brand_id',   '=', 'brand.id')
                ->join(['z_section',     'section']) ->on('good.section_id', '=', 'section.id')
                ->join(['z_group',       'group'])   ->on('good.group_id',   '=', 'group.id')
                ->join(['b_file',        'file'])    ->on('prop.img1600',    '=', 'file.id')

                ->where('good.show',      '=', 1);

        if ($type != 'admitad') {
            $query->where('good.qty', '!=', '0');
        }

            $query->where('good.section_id',  '>', 0)
                ->where('good.brand_id',    '>', 0)
                ->where('good.group_id',    '>', 0)

                ->where('prop.img1600',     '>', 0)
                ->where('section.active',   '=', 1)
                ->where_open()
                    //->where('section.parent_id',  '=', 0)
                    ->where('section.parent_id',  'IN', $active_top_sections)
                ->where_close()
                ->where('group.active',     '=', 1)
                ->where('brand.active',     '=', 1)
                ->order_by('good.id')
                ->offset($heap_number * $heap_size)
                ->limit($heap_size);

        if ( ! empty($where)) {
            foreach($where as $w) $query->where($w[0], $w[1], $w[2]);
        }
        if ($type == 'yandex') {
         // $query->where('prop.to_yandex', '!=', 0);
		//	$query->where('good.big','=', 0);
        }
        $goods = $query->execute()->as_array('id');

        if (count($goods)) return $goods;
        else return FALSE;
    }

    /**
     * вытаскивает товары для XML пачками
     *
     * @param int $heap_size сколько товаров вытаскивать в 1 пачке
     * @param int $heap_number сколько пачек уже вытащено
     * @param array $where - доп условия
     * @return boolean
     */
    public static function for_criteo_yml($heap_size, $heap_number, $where = NULL)
    {
        $active_top_sections = DB::select('z_section.id')
            ->from('z_section')
            ->where('parent_id','=',0)
            ->where('active','=',1)
            ->execute()->as_array();

        $query = DB::select('good.*', 'file.SUBDIR', 'file.FILE_NAME','prop.img1600','prop.img500','prop.desc',
            ['brand.name','brand_name'],
            ['section.name','section_name'],
            ['section.market_category','market_category']
        )->from(['z_good', 'good'])

            ->join(['z_good_prop',   'prop'])    ->on('good.id',         '=', 'prop.id')
            ->join(['z_brand',       'brand'])   ->on('good.brand_id',   '=', 'brand.id')
            ->join(['z_section',     'section']) ->on('good.section_id', '=', 'section.id')
            ->join(['z_group',       'group'])   ->on('good.group_id',   '=', 'group.id')
            ->join(['b_file',        'file'])    ->on('prop.img1600',    '=', 'file.id')

            ->where('good.show',      '=', 1);

        $query->where('good.section_id',  '>', 0)
            ->where('good.brand_id',    '>', 0)
            ->where('good.group_id',    '>', 0)
            ->where('good.price',   '>=', 200)
            ->where('good.qty',     '!=', 0)

            ->where('prop.img1600',     '>', 0)
            ->where('section.active',   '=', 1)
            ->where_open()
            ->where('section.parent_id',  'IN', $active_top_sections)
            ->where_close()
            ->where('group.active',     '=', 1)
            ->where('brand.active',     '=', 1)
            ->order_by('good.id')
            ->offset($heap_number * $heap_size)
            ->limit($heap_size);

        if ( ! empty($where)) {
            foreach($where as $w) $query->where($w[0], $w[1], $w[2]);
        }
        $goods = $query->execute()->as_array('id');

        if (count($goods)) return $goods;
        else return FALSE;
    }

    /**
     * получаем товары для Google Merchant пачками
     *
     * @param int $heap_size сколько товаров вытаскивать в 1 пачке
     * @param int $heap_number сколько пачек уже вытащено
     * @param array $where - доп условия
     * @return boolean
     */
    public static function for_google_merchant($heap_size, $heap_number)
    {
        $query = DB::select('good.*', 'file.SUBDIR', 'file.FILE_NAME','prop.img1600','prop.img500','prop.desc',
            ['brand.name','brand_name'],
            ['section.name','section_name'],
            ['section.market_category','market_category']
        )->from(['z_good', 'good'])

            ->join(['z_good_prop',   'prop'])    ->on('good.id',         '=', 'prop.id')
            ->join(['z_brand',       'brand'])   ->on('good.brand_id',   '=', 'brand.id')
            ->join(['z_section',     'section']) ->on('good.section_id', '=', 'section.id')
            ->join(['z_group',       'group'])   ->on('good.group_id',   '=', 'group.id')
            ->join(['b_file',        'file'])    ->on('prop.img1600',    '=', 'file.id')

            ->where('good.show',      '=', 1)
            ->where('good.qty', '!=', '0')
            ->where('good.section_id',  '>', 0)
            ->where('good.brand_id',    '>', 0)
            ->where('good.group_id',    '>', 0)
            ->where('prop.img1600',     '>', 0)
            ->where('section.active',   '=', 1)
            ->where('good.google_cat_id', '>', 0)
            ->where('group.active',     '=', 1)
            ->where('brand.active',     '=', 1)
            ->order_by('good.id')
            ->offset($heap_number * $heap_size)
            ->limit($heap_size);

        if ( ! empty($where)) {
            foreach($where as $w) $query->where($w[0], $w[1], $w[2]);
        }

        $goods = $query->execute()->as_array('id');

        if (count($goods)) return $goods;
        else return FALSE;
    }

    public static function get_wikimart_catalog()
    {
        $catalog = '';
        if (empty($catalog)) {
            $rarray = DB::select('z_good.*', 'wiki_categories.id', 'wiki_categories.category_id',
                'wiki_categories.parent_id', ['wiki_categories.name','wiki_name'])
                ->distinct('z_good.wiki_cat_id')
                ->from('z_good')
                ->join('wiki_categories')
                ->on('z_good.wiki_cat_id','=','wiki_categories.category_id')
                ->where('z_good.qty',   '!=', 0)
                ->where('z_good.show',  '=', 1)
                ->where('z_good.price', '>', 300)
                ->execute()
                ->as_array();

            $catalog = $zombies = array();

            foreach($rarray as $item) {
                if ($item->parent_id != 0) {
                    if (!empty($catalog[$item->parent_id])) {
                        $catalog[$item['parent_id']]->children[$item['id']] = $item;
                    } else {
                        $zombies[] = $item['parent_id'];
                    }
                } else {
                    $catalog[$item['id']] = $item;
                }
            }
        }
        return $catalog;
    }

    /**
     * вытаскивает товары для XML пачками
     *
     * @param int $heap_size сколько товаров вытаскивать в 1 пачке
     * @param int $heap_number сколько пачек уже вытащено
     * @param array $where - доп условия
     * @return boolean
     */
    public static function for_wikimart_yml($heap_size, $heap_number, $where = NULL)
    {

        $query = DB::select('good.*', 'file.SUBDIR', 'file.FILE_NAME','prop.img1600','prop.img500','prop.desc',
            ['brand.name','brand_name'],
            ['country.name', 'country_name']
        )->from(['z_good', 'good'])

            ->join(['z_good_prop',    'prop'])     ->on('good.id',         '=', 'prop.id')
            ->join(['z_brand',        'brand'])    ->on('good.brand_id',   '=', 'brand.id')
            ->join(['z_group',        'group'])    ->on('good.group_id',   '=', 'group.id')
            ->join(['b_file',         'file'])     ->on('prop.img1600',    '=', 'file.id')
            ->join(['z_country',      'country'], 'LEFT')  ->on('good.country_id', '=', 'country.id')

            ->where('good.show',        '=', 1)
            ->where('good.qty',         '!=', 0)
            ->where('good.brand_id',    '>', 0)
            ->where('good.group_id',    '>', 0)
            ->where('good.price',       '>=', 500)
            ->where('prop.img1600',     '>', 0)
            ->where('wiki_cat_id',      '>', 0)
            ->where('group.active',     '=', 1)
            ->where('brand.active',     '=', 1)
            ->order_by('good.id')
            ->offset($heap_number * $heap_size)
            ->limit($heap_size);

        if ( ! empty($where)) {
            foreach($where as $w) $query->where($w[0], $w[1], $w[2]);
        }

        $goods = $query->execute()->as_array('id');

        if (count($goods)) return $goods;
        else return FALSE;
    }

    /**
     * Для детского питания для новорожденных реклама запрещена
     * @return bool
     * @throws Cache_Exception
     */
    public function is_advert_hidden()
    {
        // Товар в нужной секции?
        if ($this->section_id == 29065) {
            //загрузить фильтры
            $filters = Cache::instance('memcache')->get('good_advert_hidden');
            if ( empty($filters)) {
                $filters = DB::select('good_id')
                    ->from('z_good_filter')
                    ->where('filter_id', '=', 1467)
                    ->where('value_id', 'IN', [13097, 13098, 13100, 13101, 13102])
                    ->execute()
                    ->as_array('good_id');
            }
            if ( ! empty($filters[$this->id])) return TRUE;   
        }
        return FALSE;
    }

    /**
     * Может ли к товару быть заказана бесплатная сборка
     */
    public function sborkable()
    {
        $ids_goods = [215082, 215081, 179693, 215080, 196230, 179692, 217823, 216028, 217780, 179369,
            221928, 217778, 217774, 217783, 217770, 179368, 179367, 217792, 217821, 217819, 218732, 221927, 221926,
            218430, 217822, 217820];

        return (!in_array($this->id, $ids_goods) && Model_Section::sborkable($this->section_id));
    }

    /**
     * Проверить, готова ли карточка товара к выгрузке на Озон
     * 
     * @return array
     */
    public function is_ozon_ready() {
        if ($this->qty < self::OZON_MIN_QTY) return FALSE;
        if ($this->show < 1) return FALSE;
        if ($this->section_id < 1) return FALSE;
        if ($this->brand_id < 1) return FALSE;
        if ($this->group_id < 1) return FALSE;
        if ( ! $this->prop->img500) return FALSE;
        if ( ! Txt::is_html_text_filled($this->prop->desc)) return FALSE;
        
        $section = ORM::factory('section', $this->section_id);
        if ( ! $section->active) return FALSE;
        
        $group = ORM::factory('group', $this->group_id);
        if ( ! $group->active) return FALSE;
        
        $brand = ORM::factory('brand', $this->brand_id);
        if ( ! $brand->active) return FALSE;
        return TRUE;
    }
    
    public static function for_ozon()
    {
        return ORM::factory('good')
            ->with('prop')
            ->where('prop.to_ozon', '>', 0)
            ->find_all()
            ->as_array('id');
    }


    /**
     * После сохранения товара в админке - сохранить его пропы
     */
    public function admin_save() {
        $messages = array();
        $request = Request::current();

		$prop = $request->post('prop');

        // { сохранение текстов вкладок
        $text = $request->post('good_text');
        if ( ! empty($text)) {
            $ins_text = DB::insert('z_good_text', ['good_id', 'name', 'content']);
            foreach ($text as $name => $content) {
                $ins_text->values(['good_id' => $this->id, 'name' => $name, 'content' => $content]);
            }
            DB::query(Database::INSERT, $ins_text . ' ON DUPLICATE KEY UPDATE content = VALUES(content)')->execute();
        }
        // }

		if (method_exists($this->prop, 'flag')) {  // reset checkboxes if no value only
			foreach($this->prop->flag() as $f) {
				$prop[$f] = empty($prop[$f]) ? '0' : '1';
			}
		}
		$this->prop->values($prop);

		if ($this->prop->changed()) {
			Model_History::log('good', $this->id, 'prop', array_intersect_key($this->prop->as_array(), array_keys($this->prop->changed())));
		}

		$this->prop->save();

		// обработка картинок
		$good_imgs  = $this->get_images();      // Картинки, которые уже есть в товаре
		$post_img   = $request->post('img');    // Картинки, которые пришли из формы

		$change_img = TRUE;     // по умолчанию ставим эту картинку как основную
		$del_img    = array();  // собираем id удалённых картинок

		$sorted = false;
		foreach( $good_imgs as $key => &$_ ){

			if( empty( $post_img[70][$key] ) || $post_img[70][$key] != $_[70]->ID ){
				$sorted = true;
				break;
			}
		}
		unset( $_ );

		if( $sorted ){

			DB::query(Database::DELETE, "DELETE FROM z_good_img WHERE good_id = $this->id")->execute();

			if( ! empty( $post_img ) )
			foreach( $post_img as $size => $ids ){

				foreach( $ids as $id ){
					DB::query(Database::INSERT, "INSERT INTO z_good_img SET good_id = $this->id, file_id = $id, size = '$size'")->execute();
				}
			}

			$good_imgs  = $this->get_images();      // Картинки, которые уже есть в товаре
		}

		//foreach( $post)
//		print_r( $post_img == $good_imgs );
		if ($post_img || (count($good_imgs) > 0)) { // идентификаторы картинок из поста
			foreach($good_imgs as $i => $imgs) {
				foreach($imgs as $size => $img) {                    
					if (isset( $post_img[$size] ) && is_array($post_img[$size]) && (  FALSE !== array_search($img->ID, $post_img[$size]))) {
						// Есть и в БД и в POST - оставляем как есть 
						unset($good_imgs[$i][$size]); // Больше не нужна, мы с ней закончили
						if (empty($good_imgs[$i])) unset($good_imgs[$i]); // Пустая пачка - больше не нужна, закончили и с пачкой
						$change_img = FALSE; // Не надо менять основную картинку товара, так как картинка уже загружена
					} else {
						$del_img[] = $img->ID;
						$img->delete();
					}
				}
			}
		}
		if (count($del_img)) $messages['messages'][] = Kohana::message('admin/good', 'img.deleted');
		// здесь в good_imgs только те картинки, что нет в посте - надо удалить
		if ( ! empty($del_img)) {
			$this->unbind_images($del_img); // отцепим картинки от товара
			Model_History::log('good', $this->id, 'image del', array($del_img));
		}

		// сохранение новой картинки среди всех картинок
		if ( ! empty($_FILES['img']) && Upload::not_empty($_FILES['img']) && Upload::valid($_FILES['img'])) {

			list($w, $h) = getimagesize($_FILES['img']['tmp_name']);
			if (empty($w) OR empty($h) OR ($w != $h) OR ($w < 600) OR ($h < 600)) { // only square not less than 600

				$messages['errors'][] = Kohana::message('admin/good', 'img.default');

			} else {

				$file = Model_File::image('img');
				$file->MODULE_ID = __CLASS__;
				$file->item_id = $this->id;
				$file->save(); // save original file

				$_1600 = $file->watermark();
				$_255 = $_1600->resize(255, 255, $file->ID);
				$_380 = $_1600->resize(380, 380, $file->ID);
				$_380x560 = $_1600->resize(380, 560, $file->ID);
				$_173x255 = $_1600->resize(173, 255, $file->ID);
				$_70 = $_1600->resize(70, 70, $file->ID);
				$this->bind_image($_70, $_255, $_380, $_1600, $_380x560, $_173x255);

				Model_History::log('good', $this->id, 'image add', array(1600 => $_1600->ID, 255 => $_255->ID, 380 => $_380->ID, 70 => $_70->ID));

				$messages['messages'][] = Kohana::message('admin/good', 'img.added');

				if ($change_img) {
					$this->prop->img1600 = $_1600;
					$this->prop->img380 = $_380;
					$this->prop->img255 = $_255;
					$this->prop->img70 = $_70;
					$this->prop->img380x560 = $_380x560;
					$this->prop->img173x255 = $_173x255;

					$this->image = $_255->ID; // проставим картинку товара
					$this->save();

					if (empty($this->prop->img500)) {
						$_500 = $file->resize(500, 500);
						$this->prop->img500 = $_500; // save img500 if not any
						$messages['messages'][] = Kohana::message('admin/good', 'img.added500');
					}

					$this->prop->save();
				}
			}
		}

		// сохранение новой картинки 500 на 500
		if ( ! empty($_FILES['img500']) AND Upload::not_empty($_FILES['img500']) AND Upload::valid($_FILES['img500'])) {

			list($w, $h) = getimagesize($_FILES['img500']['tmp_name']);

			if (empty($w) OR empty($h) OR ($w != $h) OR ($w < 500) OR ($h < 500)) { // only square not less than 500

				$messages['errors'][] = Kohana::message('admin/good', 'img.default');

			} else {

				$file = Model_File::image('img500');
				$file->MODULE_ID = __CLASS__;
				$file->item_id = $this->id;

				$_500 = $file->resize(500, 500);

				$file->save(); // save original file

				$this->prop->img500 = $_500->ID;
				$this->prop->save();

				Model_History::log('good', $this->id, 'image 500 add', $_500->as_array());
				$messages['messages'][] = Kohana::message('admin/good', 'img.added500');
			}
		}

		if ($request->post('tag_changed')) { // пересохранение тегов

			Model_History::log('good', $this->id, 'tags changed', $request->post('tag'));

			DB::delete('z_good_tag')->where('good_id', '=', $this->id)->execute();

			$insert = FALSE;
			$ins = DB::insert('z_good_tag')->columns(array('good_id', 'tag_id', 'sort'));
			foreach($request->post('tag') as $id => $sort) {
				$insert = TRUE;
				$ins->values(array($this->id, $id, $sort));
			}
			if ($insert) { 
				$ins->execute();
			}
		}

		// Ставим на место картинки товара
		$images = $this->get_images();

		if ( ! empty($images[0]['70']) AND ($images[0]['70'] instanceof Model_File)) {
			$this->prop->img70 = $images[0]['70']->ID;
		}
		if ( ! empty($images[0]['255']) AND ($images[0]['255'] instanceof Model_File)) {
			$this->image = $this->prop->img255 = $images[0]['255']->ID; // Обновим также и картинку в самом товаре, для расчета картинки группы
            
            if ($this->changed('image')) {
                
                $this->save(); //  пересохраним данные
            }
		}
		if ( ! empty($images[0]['1600']) AND ($images[0]['1600'] instanceof Model_File)) {
			$this->prop->img1600 = $images[0]['1600']->ID;
		}

		if ($this->prop->_graf AND $this->prop->_desc) {
			$this->prop->_new_item = 0;
		}

		$this->prop->save(); // Сохраняем еще раз, чтобы show пересчиталось

		self::on_qas_change(array($this->id));
        
        return $messages;
    }

    /**
     * проставить товару время последнего наличия
     */
    public function saveLastSeen()
    {
		$this->prop->last_seen = date('Y-m-d H:i:s');
		$this->prop->save();
		
        $this->save();
	}

    /**
     * может ли товар появиться в продаже снова ( да, если не был в наличии меньше 90 дней)
     * @return bool
     */
    public function can_appear()
    {
		return time() - strtotime($this->prop->last_seen) <  90 * 24 * 60 * 60;
	}

    /**
     * проверяет, находится ли товар в продаже
     * @return bool|string возвращаем дату когда товар был в наличии, если cейчас нет или FALSE
     */
    public function not_in_sale()
    {
        $return = FALSE;

        if ( ! $this->qty && $this->price > 0 && $this->prop->_desc && $this->prop->_graf && ( ! $this->zombie) ) { // нет в наличии
			$return = ($this->prop->last_seen > 0) ? $this->prop->last_seen : date('Y-m-d H:i:s');
		}
        return $return;
	}

    /**
     * Получение аналогов товара. Аналоги это товары в наличии желательно того же бренда с теми же фильтрами
     * @return array
     * @throws Kohana_Exception
     */
    public function analogy( $limit = 5 )
    {
		foreach ($this->filters_data() as $k => $v) {
			if ( ! $this->is_cloth() || in_array($v['filter_id'], [Controller_Product::FILTER_TYPE])) { // если одежда, то подбираем только тип
                $fdata[$v['filter_id']] = $k;
            }
		}

        $goods = [];

        if ( ! empty($fdata)) { // ищем 5 товаров в той же категории с теми же фильтрами и тот же бренд
			
			$q = DB::select('id')
                ->from('goods_zukk')
                ->where('x', '=', 1)
                ->where('section_id', '=', intval($this->section_id))
                ->where('fvalue', 'IN', $fdata)
                ->where('brand_id', '=' , intval($this->brand_id))
                ->order_by('popularity', 'DESC')
                ->limit($limit);

            $goods = array_merge($goods, Database::instance('sphinx')->query(Database::SELECT, $q)->as_array('id', 'id'));

            $found = count($goods);
            if ($found < $limit ) { // добиваем товарами других брендов

                $q = DB::select('id')
                    ->from('goods_zukk')
                    ->where('x', '=', 1)
                    ->where('section_id', '=', intval($this->section_id))
                    ->where('fvalue', 'IN', $fdata)
                    ->where('brand_id', '!=' , intval($this->brand_id))
                    ->order_by('popularity', 'DESC')
                    ->limit($limit - $found);

                $goods = array_merge($goods, Database::instance('sphinx')->query(Database::SELECT, $q)->as_array('id', 'id'));
            }
        }

        $found = count($goods);
        if ($found < $limit) { // добиваем товарами из группы
            $q = DB::select('id')
                ->from('goods_zukk')
                ->where('x', '=', 1)
                ->where('group_id', '=', intval($this->group_id))
                ->limit($limit - $found);

            $goods = array_merge($goods, Database::instance('sphinx')->query(Database::SELECT, $q)->as_array('id', 'id'));
        }

        $found = count($goods);
        if ($found < $limit) { // добиваем товарами из категории
            $q = DB::select('id')
                ->from('goods_zukk')
                ->where('x', '=', 1)
                ->where('section_id', '=', intval($this->section_id))
                ->limit($limit - $found);

            $goods = array_merge($goods, Database::instance('sphinx')->query(Database::SELECT, $q)->as_array('id', 'id'));
        }

		if (empty($goods)) return FALSE;

		return ORM::factory('good')->where('id', 'IN', $goods)->find_all()->as_array('id');
	}

    /**
     * Получить все данные о прикреплённвх к товару фильтрах
     * @return []
     */
    public function filters_data()
    {
		if (empty($this->filters_data)) {
			
			$result = DB::select(
                    DB::expr('v.id as vid'),
                    DB::expr('f.id as filter_id'),
                    DB::expr('f.name as filter_name'),
                    DB::expr('v.name as value_name')
				)
				->from(array('z_good_filter','r'))
                ->join(array('z_filter_value', 'v'))
					->on('v.id', '=', 'r.value_id'   )
                ->join(array('z_filter',   'f'))    
					->on('f.id', '=', 'r.filter_id'   )
				->where('good_id', '=' , $this->id)
                ->where('f.id', '!=', Model_Filter::WEIGHT)
                    ->order_by('f.sort', 'DESC')
                    ->order_by('v.sort', 'ASC')
                ->execute()
                ->as_array('vid');
		
			$this->filters_data =& $result;
		}
		
		return $this->filters_data;
	}
	
    /**
     * получить массив значений фильтров для показа в карточке товара
     * @param null $section
     * @return array массив с ключами - названия фильтров, значения - значения фильтров для текущего товара
     */
    public function get_filters($section = null)
    {
		$return = array();
		
		$result = $this->filters_data();

		if ( ! empty($result)) {
            foreach($result as $vf) {
                if( ! empty( $section ) && empty( $section->settings['list'] ) && ! empty( $section->settings['sub_filter'] ) && $vf['filter_id'] == $section->settings['sub_filter'] ) continue;
                $return[$vf['filter_name']][] = $vf['value_name'];
            }
        }

		return $return;
	}
	
    /**
     * Рассчитывает, можно ли показывать товар на сайте
     * 
     * @return int
     */
    public function calc_show()
    {
        $this->show = ($this->price > 0 && $this->active && $this->prop->_desc && $this->prop->_graf && ( ! $this->zombie)) ? '1' : '0';

        if ($this->changed('show')) {
            if ($this->show == 1 && $this->new && empty($this->prop->new_till)) { // появилась новинка - проставим срок месяц
                $this->prop->new_till = date('Y-m-d', strtotime('+1 month'));
                $this->prop->save();
            }
            Log::instance()->add(Log::INFO, 'Good #' . $this->id . ($this->show ? ' shown': ' hidden'));
        }
        return $this->show;
    }

    public static function on_qas_change($good_ids)
    {
        if (empty($good_ids)) return; // Нету товаров - нечего делать.
        
        Model_Action::check_by_goods($good_ids);
        
        return;
    }
    
    /**
     * Сохранение товара - проверяет товары в акциях и подарки
     * @param Validation $validation
     * @return ORM|void
     */
    public function save(\Validation $validation = NULL)
    {
        $this->calc_show();
        
        parent::save($validation);
       
        // возможно, надо поменять картинку группы товаров, !!! после сохранения товара - она тянет номера из БД
        $group_image = $this->group->image;
        if ($group_image != $this->group->get_best_image()) $this->group->save();
    }
    
    /**
     * Получить id товаров памперс
     * @return []
     */
    public static function get_pampers()
    {
        $good_ids = DB::select('id')
            ->from('z_good')
            ->where('brand_id', '=', self::PAMPERS_BRAND)
            ->where('section_id', '=', self::PAMPERS_SECTION)
            ->where('show', '=', 1)
            ->where('active', '=', 1)
            ->where('qty', '!=', 0)
            ->execute()
            ->as_array('id', 'id');

        return $good_ids;
    }

    /**
     * Получить 5 хитов продаж, исключить те что не в наличии, заменить заменами если есть замены
     * @param $section_id
     * @param $admin
     * @return Model_Good[]
     */
    static function get_hitz($section_id, $admin = FALSE)
    {
        $r = DB::select('good_id', 'sort')
            ->from('z_hit')
            ->where('section_id', '=', $section_id)
            ->order_by('sort')
            ->execute()
            ->as_array('sort', 'good_id');

        if (empty($r)) return FALSE;
        $goods_q = ORM::factory('good')->where('id', 'IN', $r);

        if ( ! $admin) {
            $goods_q
                ->where('show', '!=', 0)
                ->where('qty', '!=', 0);
        }

        $goods = $goods_q->find_all()->as_array('id');

        $return = $add = $used = [];

        if ($admin) {

            foreach($r as $k => $good_id) {
                $return[$k] = $goods[$good_id] ? $goods[$good_id] : FALSE;
            }
            return $return;
        }

        for($i = 1; $i <= 5; $i++) {
            $gid = $gid2 = 0;
            if ( ! empty($r[$i])) $gid = $r[$i]; // хит
            if ( ! empty($r[$i + 5])) $gid2 = $r[$i + 5]; // замена
            if ( ! empty($gid) && ! empty($goods[$gid])) { // основной хит есть
                $return[$i] = $goods[$gid];
                $used[] = $gid;
            } elseif ( ! empty($gid2) && ! empty($goods[$gid2])) { // замена есть
                $return[$i] = $goods[$gid2];
                $used[] = $gid2;
            } else {
                $add[] = $i; // надо будет добавить хит
            }
        }

        if ( ! empty($add)) { // добавим хитов

            $sidz = DB::select('id')
                ->from('z_section')
                ->where('parent_id', '=', $section_id)
                ->where('active', '=', 1)
                ->execute()
                ->as_array('id', 'id');

            if ( ! empty($sidz)) {
                $q = DB::select('id')->from('goods_zukk')
                    ->where('section_id', 'IN', array_map('intval', $sidz))
                    ->where('x', '=', 1)
                    ->order_by('popularity', 'DESC')
                    ->limit(5 - count($used));

                if ( ! empty($used)) $q->where('id', 'NOT IN', array_map('intval', $used));

                $ids = Database::instance('sphinx')
                    ->query(Database::SELECT, $q)
                    ->as_array('id', 'id');

                if ( ! empty($ids)) {
                    $goods = ORM::factory('good')->where('id', 'IN', $ids)->find_all()->as_array();
                    foreach($add as $i) {
                        if ( ! empty($goods)) $return[$i] = array_pop($goods);
                    }
                }

            }
        }

        return $return;
    }
    
    /**
     * Получить хиты продаж
     * @return Model_Good[]
     * @throws Cache_Exception
     */
    public static function get_hitz_sections()
    {
        $hitz_sections = Cache::instance()->get('hitz_sections');
        
        if ( empty( $hitz_sections )) {
            
            $hitz_sections = DB::select(['section_id', 'id'], ['z_section.name','name'])
                ->distinct('section_id')
                ->from('z_hit')
                    ->join('z_section')
                    ->on('z_section.id','=','z_hit.section_id')
                ->where('z_section.active', '=', 1)
                ->where('z_section.vitrina', '=', 'mladenec')
                ->where('z_section.parent_id', '=', 0)
                //->where('z_section.parent_id', '!=', Model_Section::CLOTHS_ROOT)

                ->order_by('z_section.sort','ASC')
                ->execute()
                ->as_array('id');

            Cache::instance()->set('hitz_sections', $hitz_sections, 3600);
        }
        
        return $hitz_sections;
    }

    /**
     * Определяем не относится ли товар к типу одежды
     */
    public function is_cloth()
    {
        $cloth_subs = Model_Section::get_cloth_subs();
        return ! empty($cloth_subs[$this->section_id]);
    }

    /**
     * Получить массив всех значений цветов => размеров для товарных групп, цвета д.б. всегда
     * @param [] $group_ids
     * @return array [colorsize => , colors => , sizes => , allsizes => , colorimage => , sizefilter => ]
     */
	public static function get_color_size($group_ids, $good_ids = [])
    {
        $init = ['colorsize' => [], 'colors' => [], 'sizes' => [], 'allsizes' => [], 'colorimage' => [], 'size_filter' => []];
        $return = [];

        $q = DB::select('good_id', 'color', 'group_id')
            ->from('good_color')
            ->where('group_id', 'IN', $group_ids);

        if ( ! empty($good_ids)) $q->where('good_id', 'IN', $good_ids);

        $colors = $q->execute()->as_array('good_id');

        if (empty($colors)) return FALSE;

        $colorimage = $goods = array();
        foreach($colors as $good_id => $data) { // собираем по цветам, пустые цвета склеиваем в один, для получения картинок + товары по группам
            if (empty($return[$data['group_id']])) $return[$data['group_id']] = $init;
            if (empty($data['color'])) $data['color'] = 'NO_COLOR';
            $goods[$data['good_id']] = $data['group_id'];
            if (empty($colorimage[$data['group_id']][$data['color']])) $colorimage[$data['group_id']][$data['color']] = $data['good_id'];
            $return[$data['group_id']]['colors'][$data['color']][] = $data['good_id'];
        }

        // получаем все размеры для этих товаров
        $good_sizes = DB::select(
            ['v.id', 'vid'],
            ['f.id', 'filter_id'],
            ['f.name', 'filter_name'],
            ['v.name', 'value_name'],
            'r.good_id'
        )
            ->from(['z_good_filter','r'])
            ->join(['z_filter_value', 'v'])
                ->on('v.id', '=', 'r.value_id')
            ->join(['z_filter', 'f'])
                ->on('f.id', '=', 'r.filter_id')
            ->where('good_id', 'IN' , array_keys($goods))
            ->where('r.filter_id', 'IN', [Controller_Product::FILTER_SIZE, Controller_Product::FILTER_GROWTH, Controller_Product::FILTER_AGE])
                ->order_by('f.sort', 'DESC')
                ->order_by('v.sort', 'ASC')
                ->order_by('v.name', 'ASC')
            ->execute()
            ->as_array();

        if ( ! empty($good_sizes)) {

            $group_sizes = []; // // раскладываем размеры по товарным группам
            foreach($good_sizes as $data) {
                if (empty($sizes[$goods[$data['good_id']]])) $sizes[$goods[$data['good_id']]] = [];
                $group_sizes[$goods[$data['good_id']]][] = $data;
            }

            foreach($group_sizes as $group_id => $sizes) { // заполняем по группам данные о цветах и размерах

                $return[$group_id]['size_filter'] = $size_filter = $sizes[0]['filter_id'];

                foreach ($sizes as $data) { // и заполняем связь товар -> размеры
                    if ($data['filter_id'] == $size_filter) {
                        $return[$group_id]['sizes'][$data['good_id']][$data['vid']] = $data['value_name'];
                        $return[$group_id]['allsizes'][$data['vid']] = $data['value_name'];
                    }
                }
                // теперь заполняем цветам какому размеру какой товар
                foreach ($return[$group_id]['colors'] as $color => $goodz) {
                    foreach ($goodz as $gid) {
                        if ( ! empty($return[$group_id]['sizes'][$gid])) {
                            foreach ($return[$group_id]['sizes'][$gid] as $vid => $vname) {
                                $return[$group_id]['colorsize'][$color][$vid][] = $gid;
                            }
                        }
                    }
                }
            }
        }

        $images = Model_Good::many_images([70], array_keys($goods)); // получим малые картинки для всех разных цветов
        foreach($colorimage as $group_id => $data) {
            foreach($data as $color => $good_id) {
                $return[$goods[$good_id]]['colorimage'][$color] = $images[$good_id][70]->get_img(0);
            }
        }
        return $return;
    }

    /**
     * Опции для бесплатной сборки КГТ
     */
    public static function sborka()
    {
        $opts = [
            '0' => 'не нужна',
            '1' => 'нужна'
        ];
        /*$opts = [ // временно пока не трубуется
            '' => 'не нужна',
            'на следующий день' => 'на следующий день',
            'через день' => 'через день',
            'через два дня' => 'через два дня',
            'через три дня' => 'через три дня',
        ];*/
        return $opts;
    }

    /**
     * Сохранить wikimart-категорию для набора товаров
     * @return object
     */
    public static function save_wikicat($wiki_cat_id, $goods_ids)
    {
        return DB::update(ORM::factory('good')->table_name())
            ->set(['wiki_cat_id' => $wiki_cat_id])
            ->where('id', 'IN', $goods_ids)
            ->execute();
    }

    /**
     * Сохранить google merchant-категорию для набора товаров
     * @return object
     */
    public static function save_googlecat($google_cat_id, $goods_ids)
    {
        return DB::update(ORM::factory('good')->table_name())
            ->set(['google_cat_id' => $google_cat_id])
            ->where('id', 'IN', $goods_ids)
            ->execute();
    }

    /**
     * получить товары по google merchant-категории
     * @param $google_cat_id
     * @return array
     * @throws Kohana_Exception
     */
    public function get_goodsgoogle($google_cat_id)
    {
        $res = ORM::factory('good')
            ->where('google_cat_id', '=', $google_cat_id)
            ->find_all()
            ->as_array('id');

        return $res;
    }

    /**
     * Сохранить озон-категорию для набора товаров,
     * если набор товаров пустой - скинуть эту озон-категорию у всех товаров
     * @return object
     */
    public static function save_ozon($ozon_type_id, $goods_ids)
    {
        $ins = DB::update(ORM::factory('good')->table_name());

        if ( ! empty($goods_ids)) {
            $ins->set(['ozon_type_id' => $ozon_type_id])
                ->where('id', 'IN', $goods_ids);
        } else {
            $ins->set(['ozon_type_id' => 0])
                ->where('ozon_type_id', '=', $ozon_type_id);
        }
        return $ins->execute();

    }

    /**
     * получить товары по wikimart-категории
     * @param $wiki_cat_id
     * @return array
     * @throws Kohana_Exception
     */
    public function get_goodswiki($wiki_cat_id)
    {
        $res = ORM::factory('good')
            ->where('wiki_cat_id', '=', $wiki_cat_id)
            ->find_all()
            ->as_array('id');

        return $res;
    }

    /**
     * получить витрины и категории в которых есть дубликат товара
     */
    public function get_dups()
    {
        return DB::select('s.name', 's.id', 's.vitrina')
            ->from('good_dups')
            ->join(['z_section', 's'])
                ->on('section_id', '=', 's.id')
            ->where('good_id', '=', $this->id)
            ->execute()
            ->as_array('id');
    }

    /**
     * Добавить дубликат товара в другой категории
     * @param $section_id
     * @return object
     */
    public function add_dup($section_id)
    {
        return DB::insert('good_dups')
            ->columns(['good_id', 'section_id'])
            ->values(['good_id' => $this->id, 'section_id' => $section_id])
            ->execute();
    }

    /**
     * Удалить дубликат товара в другой категории
     * @param $section_id
     * @return object
     */
    public function del_dup($section_id)
    {
        return DB::delete('good_dups')
            ->where('good_id', '=', $this->id)
            ->where('section_id', '=', $section_id)
            ->execute();
    }
}
