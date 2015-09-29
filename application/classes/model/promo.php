<?php
// aкции
class Model_Promo extends ORM {

    protected $_table_name = 'z_promo';

    protected $_table_columns = [
        'id' => '', 'name' => '', 'slider_header'=>'', 'active' => '', 'total' => ''
    ];

    protected $_has_many = [
        'goods' => [
            'model' => 'good',
            'through'    => 'z_promo_good',
            'foreign_key'   => 'promo_id',
            'far_key'   => 'good_id',
        ],
        'brands' => [
            'model' => 'brand',
            'through'    => 'z_promo_brand',
            'foreign_key'   => 'promo_id',
            'far_key'   => 'brand_id'
        ],
        'showningoods' => [
            'model' => 'good',
            'foreign_key' => 'promo_id'
        ]
    ];

    /**
     * 
     * @param int $active
     * @return Model_Good
     * @throws Exception
     */
    public function get_goods($active = NULL)
    {
        if ( ! $this->loaded()) throw new Exception('Cannot get bundle for no object');
        
        $q = $this->goods;
        if ( ! is_null($active)) {
            $q->where('show', '=', '1')->where('qty', '!=', 0);
        }
        
        return $q->find_all()->as_array();
    }
    
    /**
     * При удалении модели надо удалить связи и отцепить товары
     * 
     */
    function delete()
    {
        if ($this->id) {

            DB::update('z_good')
                ->set(['promo_id' => 0])
                ->where('id', '=', $this->id)
                ->execute();

            DB::delete('z_promo_good')
                ->where('promo_id', '=', $this->id)
                ->execute();
        }
        parent::delete();
    }
    
    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('active');
    }
    
    public function admin_save() {
        
        $misc = Request::current()->post('misc');
        if ( ! empty($misc['brand_id'])) {
            $brand_id = $misc['brand_id'];
            
            $link_exist = $this->has('brands', $brand_id);
            if ( ! $link_exist) {
                $this->add('brands', $brand_id);
            }
        }
    }
}
