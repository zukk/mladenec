<?php
class Model_Good_Text extends ORM {

    protected $_table_name = 'z_good_text';

    protected $_has_one = array(
        'good' => array('model' => 'good', 'foreign_key' => 'id'),
    );

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'content'=>'', 'good_id' => ''
    );


    /**
     * заменить блоки промо в тексте слайдером с товарами
     * @param $text
     * @return mixed
     * @throws View_Exception
     */
    public static function desc($text)
    {
		$desc = $text;
		
		if( preg_match_all('#\[promo=([0-9]+)\]#ius', $desc, $matches ) ){
			foreach( $matches[0] as $key => $preg ){
				$promoId = $matches[1][$key];
				
				$goods = ORM::factory('promo', $promoId)->get_goods(true);
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