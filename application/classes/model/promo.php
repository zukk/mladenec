<?php
// aкции
class Model_Promo extends ORM {

    protected $_table_name = 'z_promo';

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'slider_header'=>'', 'active' => '','total' => ''
    );

    protected $_has_many = array(
        'goods' => array(
            'model' => 'good',
            'through'    => 'z_promo_good',
            'foreign_key'   => 'promo_id',
            'far_key'   => 'good_id',
        ),
        'brands' => array(
            'model' => 'brand',
            'through'    => 'z_promo_brand',
            'foreign_key'   => 'promo_id',
            'far_key'   => 'brand_id'
        ),
        'showningoods' => array(
            'model' => 'good',
            'foreign_key' => 'promo_id'
        )
    );
    /**
     * 
     * @param int $active
     * @return Model_Good
     * @throws Exception
     */
    public function get_goods($active = NULL) {
        if ( ! $this->loaded()) throw new Exception('Cannot get bundle for no object');
        
        $goods = $this->goods;
        if ( ! is_null($active)) {
            $goods = $goods->where('show', '=','1')->where('qty','>',0);

        }
        $goods_arr = $goods->find_all()->as_array();
        
        return $goods_arr;
    }
    
    /**
     * При удалении модели надо удалить связи и отцепить товары
     * 
     */
    function delete() {
        if ($this->id) {
            DB::update('z_good')
                ->set(array('promo_id'=>0))
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
