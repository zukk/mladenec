<?php

class Model_Brand extends ORM {

	use Seo;
	
    public $qty = 0; // for qty in brand

    protected $_table_name = 'z_brand';

    protected $_belongs_to = array(
        'image225' => array('model' => 'file', 'foreign_key' => 'img225'),
	);
	
    protected $_has_many = array(
        'serts'   => array(
                   'foreign_key' => 'brand_id',
                   'model' => 'sert',
                   'through' => 'z_sert_rel',
                   'far_key' => 'sert_id'
                   ),
        'promos'   => array(
                   'foreign_key' => 'brand_id',
                   'model' => 'promo',
                   'through' => 'z_promo_brand',
                   'far_key' => 'promo_id'
                   )
    );
    
    protected $_has_many_though = array(
        'section' => array('model' => 'section', 'foreign_key' => 'section_id')
    );

    protected $_table_columns = array('id' => '', 'name' => '', 'code' => '', 'section_id' => '', 'active' => '', 'sort' => '', 'description' => '', 'img225' => '', 'search_words' => '');

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
            ->order_by('sort')
            ->execute()
            ->as_array('id');
    }

	public function get_img()
    {
		return ORM::factory('file', $this->img225)->get_url();
	}
	
	public function img()
    {
		return array(
			'img225' => array( 225, 120 )
		);
	}
}