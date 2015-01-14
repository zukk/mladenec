<?php

class Model_Ozon extends ORM {

    protected $_table_name = 'z_ozon';
	
	const T_CATEGORY = 1;
	const T_BRAND = 2;
	const T_GOOD = 3;
	
	public static $TYPES = array(
		self::T_CATEGORY => 'категория',
		self::T_BRAND => 'бренд',
		self::T_GOOD => 'товар'
	);

	public static $TYPES_MODELS = array(
		self::T_CATEGORY => 'section',
		self::T_BRAND => 'brand',
		self::T_GOOD => 'good'
	);
	
    protected $_table_columns = array(
        'id' => '',
        'type' => '',
        'id_item' => '',
        'scount' => '',
    );
}
