<?php

class Model_Brand extends ORM {

	use Seo;
	
    public $qty = 0; // for qty in brand
    public $hit = FALSE; // флаг хита

    protected $_table_name = 'z_brand';

    protected $_belongs_to = [
        'image' => ['model' => 'file', 'foreign_key' => 'img'],
	];
	
    protected $_has_many = [
        'serts'   => [
           'foreign_key' => 'brand_id',
           'model' => 'sert',
           'through' => 'z_sert_rel',
           'far_key' => 'sert_id'
        ],
        'promos'   => [
           'foreign_key' => 'brand_id',
           'model' => 'promo',
           'through' => 'z_promo_brand',
           'far_key' => 'promo_id'
        ]
    ];
    
    protected $_table_columns = [
        //'id' => '', 'name' => '', 'translit' => '', 'text' => '',
        'id' => '', 'name' => '', 'translit' => '',
        'code' => '', 'active' => '', 'img' => '', 'search_words' => ''
    ];

    /**
     * Id - Name для чекбоксов
     * @param $idz
     * @return mixed
     */
    static public function id_name($idz)
    {
        return DB::select('id', 'name')
            ->from('z_brand')
            ->where('id', 'IN', $idz)
            ->order_by('name')
            ->execute()
            ->as_array('id');
    }

    /**
     * Получить картинку
     * @return mixed
     */
    public function get_img()
    {
		return ORM::factory('file', $this->img)->get_url();
	}

    /**
     * Поле для админки
     * @return array
     */
    public function img()
    {
		return ['img225'];
	}
}