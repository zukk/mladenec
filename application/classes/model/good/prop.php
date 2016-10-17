<?php
class Model_Good_Prop extends ORM {

    protected $_table_name = 'z_good_prop';

    protected $_belongs_to = [
        'image70' => ['model' => 'file', 'foreign_key' => 'img70'],
        'image255' => ['model' => 'file', 'foreign_key' => 'img255'],
        'image380' => ['model' => 'file', 'foreign_key' => 'img380'],
        'image380x560' => ['model' => 'file', 'foreign_key' => 'img380x560'],
        'image173x255' => ['model' => 'file', 'foreign_key' => 'img173x255'],
        'image500' => ['model' => 'file', 'foreign_key' => 'img500'],
        'image1600' => ['model' => 'file', 'foreign_key' => 'img1600'],
    ];

    protected $_has_one = [
        'good' => ['model' => 'good', 'foreign_key' => 'id'],
    ];

    protected $_table_columns = [
        'id' => '', 'brand_id' => '', 'superprice'=>'', 'new_till' => '', 'discount' => '',
        'best' => '', 'off' => '', 'recommended' => '', 'img1600' => '', 'img500' => '', 'img255' => '','img380' => '',
        'img70' => '', 'img173x255' => '', 'img380x560' => '', 'title' => '', 'description' => '', 'desc' => '',
        'spoiler_title'  => '', // Заголовок спойлера
        'spoiler'        => '', // Тело спойлера
        'spoiler2_title' => '', // Заголовок второго спойлера
        'spoiler2'       => '', // Тело второго спойлера
        'spoiler3_title' => '', // Заголовок третьего спойлера
        'spoiler3'       => '', // Тело третьего спойлера
        'no_big_foto'    => '', '_new_item' => '', '_modify_item' => '', '_desc' => '',
        '_optim' => '', '_graf' => '', '_full_graf' => '', '_supervisor' => '', 'in_basket' => '',
        'to_yandex' => 1, 'to_wikimart' => '','to_ozon'=>1, 'weight' => '',
        'size' => '', 'tags' => '', 'search' => '',
		'last_seen' => '',
		'view_type' => '',   // Тип карточки товара 0 = обычная, 3 = nestle, 4 = одежда
		'tabs' => '',
		'advantage' => ''
    ];

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return ['new', 'superprice', 'to_yandex', 'to_wikimart', '_new_item', '_modify_item', '_desc', '_optim', '_graf', '_full_graf', '_supervisor','to_ozon'];
    }

    public function view_type_name()
    {
        return Kohana::message('good/view_type', $this->view_type, 'Ошибка');
    }

    /**
     * Показ слайдеров с промо внутри текста
     * @return mixed
     * @throws View_Exception
     */
    public function desc()
    {
		$desc = $this->desc;
		
		if (preg_match_all('#\[promo=([0-9]+)\]#ius', $desc, $matches)) {
			foreach($matches[0] as $key => $preg) {
				$promoId = $matches[1][$key];
				
				$goods = ORM::factory('promo', $promoId)->get_goods(TRUE);
				$total = count( $goods );
				$goods = array_slice( $goods, 0, 5 );
				$html = View::factory('smarty:common/goods_slider', ['goods' => $goods, 'total' => $total, 'rel' => '/slide/promo/' . $promoId, 'style' => 'margin: 0 -17px;'])->render();
				$desc = str_replace( $preg, $html, $desc);
			}
		}
		
		return $desc;
	}
    
    /**
     * Получить url картинки товара по размеру
     * @param int $size
     * @param null|Model_File[] $imgs - массив возможных картинок
     * @return string
     */
    public function get_img($size = 1600, $imgs = NULL)
    {
        $return = '/images/no_pic70.png';
        $prop = 'img'.$size;
        $id = $this->{$prop};
        if (empty($id)) return $return;
        if (is_array($imgs)) { // картинки только из массива пробуем брать
            if ( ! empty($imgs[$id][$size])) $return = $imgs[$id][$size]->get_url();
        } else {
            $return = ORM::factory('file', $id)->get_url();
        }

        return $return;
    }

    /**
     * Получить список категорий (ItemTypeId) озона в виде массива
     * array( $ItemTypeId => $ItemTypeName );
     *
     * @return array
     */
    public static function get_ozon_categories() {
        return(self::$ozon_categories);
    }

    /**
     * Массив типов/категорий товаров (ItemTypeId) на Ozon
     *
     * @var array
     */
    protected static $ozon_categories = array(
        '12475938' => 'Десерты',
        '12469061' => 'Детская вода | Детская вода и напитки',
        '12469068' => 'Соки и напитки для детей | Детская вода и напитки',
        '12469071' => 'Безмолочные | Детские каши',
        '12469074' => 'Молочные | Детские каши',
        '12469080' => 'Печенье | Детское печенье, хлебцы, гематоген',
        '12469083' => 'Хлебцы | Детское печенье, хлебцы, гематоген',
        '12469086' => 'Мясные пюре | Детское пюре',
        '12469095' => 'Безмолочные | Заменители материнского молока и сухие смеси',
        '12469098' => 'Молочные | Заменители материнского молока и сухие смеси',
        '12469109' => 'Йогурт | Молочная продукция',
        '12469106' => 'Кефир | Молочная продукция',
        '12469119' => 'Кисломолочные смеси | Молочная продукция',
        '12469112' => 'Коктейли | Молочная продукция',
        '12469103' => 'Молоко | Молочная продукция',
        '12469965' => 'Творог | Молочная продукция',
    );

    /**
     * при сохранении сбрасываем кеш суперцен
     */
    public function save(\Validation $validation = NULL)
    {
        if ($this->changed('superprice')) Cache::instance()->delete('good_superprice');
        parent::save($validation);
    }
}