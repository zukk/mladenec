<?php
class Model_Group extends ORM {

    public $variants = array(); // варианты товаров - нужны для табличного представления списка товаров

    protected $_table_name = 'z_group';

    protected $_belongs_to = array(
        'section' => array('model' => 'section', 'foreign_key' => 'section_id'),
        'brand' => array('model' => 'brand', 'foreign_key' => 'brand_id'),
        'img' => array('model' => 'file', 'foreign_key' => 'image')
    );

    protected $_has_many = array(
        'goods' => array('model' => 'good', 'foreign_key' => 'group_id'),
        'serts'   => array(
           'model' => 'sert',
           'foreign_key' => 'group_id',
           'through' => 'z_sert_rel',
           'far_key' => 'sert_id'
        )
    );
    
    protected $_table_columns = array(
        'id' => '', 'code' => '', 'name' => '', 'vitrina' => '', 'translit' => '', 'section_id' => '', 'active' => '',
        'sort' => '', 'image' => '', 'rating' => '', 
        'min_price' => '', // Цена самого дешевого товара в группе
        'max_price' => '', // Цена самого дорогого товара в группе
        'good_id' => '',
        'qty' => '', 'brand_id' => '', 'review_qty' => '', 'good' => '',
    );

    /**
     * Список картинок на заливку
     * @return array
     */
    public function img()
    {
        return array('image' => array());
    }
    
    public function get_image()
    {
        if ($this->image == 0) return Model_File::empty_image(null,128);

        return $this->img->get_img(array(
            'alt' => $this->name,
            'title' => $this->name,
        ));
    }
    
    public function get_link($html = true) {
     
        $href = sprintf('/product/%s/%d.%d.html', $this->translit, $this->id, $this->good_id);

        return $html ? HTML::anchor($href, $this->name, array('title' => HTML::entities($this->name))) : $href;
    }
    
    /**
     * Возвращает ID последней основной картинки товара группы
     * @return int
     */
    public function get_best_image() {
         return $this->image = DB::select(DB::expr('MAX(`image`) as `img`'))
                ->from('z_good')
                ->where('z_good.group_id','=',$this->id)
                ->where('z_good.show','=','1')
                ->execute()
                ->get('img');
    }
    
    /**
     * @return Model_Sert load binded certificates
     */
    public function get_serts($active = NULL) {
        
        $group_sert_ids = DB::select('z_sert_rel.sert_id')
                ->from('z_sert_rel')
                ->where('section_id', '=',0)
                ->where('brand_id', '=',0)
                ->where('group_id', '=', $this->id)
                ->execute()->as_array('sert_id', 'sert_id');
        
        $sert_ids = DB::select('z_sert_rel.sert_id')->from('z_sert_rel')
            ->where_open()
                ->where('section_id', '=', $this->section_id)
                ->or_where('section_id', '=', 0)
            ->where_close()
            ->where_open()
                ->where('brand_id', '=', $this->brand_id)
                ->or_where('brand_id', '=', 0)
            ->where_close()
            ->where('group_id', '=', 0)
            ->execute()->as_array('sert_id', 'sert_id');
        
        $ids = $group_sert_ids + $sert_ids;
        
        $ids = array_unique($ids);
        
        if (count($ids) > 0) {
            
            $serts_q = ORM::factory('sert')
                    ->where('id', 'IN', $ids);
            
            if ( ! is_null($active)) {
                if ($active) $serts_q->where('expires','>',date('Y-m-d'));
                else $serts_q->where('expires','<=',date('Y-m-d'));
            }
            
            $serts = $serts_q->find_all()->as_array();
            return $serts;
        } else {
            return NULL;
        }
        
    }
    
    /**
     * Пересчет количества активных отзывов
     * @return int
     */
    public function review_count()
    {
        
        $this->review_qty = DB::select(DB::expr('count(`z_good_review`.`id`) as `cnt`'))
            ->from('z_good_review')
            ->join('z_good')
                ->on('z_good_review.good_id','=','z_good.id')
            ->join('z_group')
                ->on('z_group.id','=','z_good.group_id')
            ->where('z_good.group_id', '=', $this->id)
            ->where('z_good_review.active', '=', 1)
            ->execute()->get('cnt');
        
        return $this->review_qty;
    }
    
    public function admin_save() {
        
        $misc = Request::current()->post('misc');
        
        if ( ! empty($misc['good_order']) AND is_array($misc['good_order'])) {
            
            $good_ids = array_keys($misc['good_order']);
            $goods = ORM::factory('good')->where('id', 'IN', $good_ids)->find_all()->as_array('id');
            
            foreach ($goods as $g) {
                
                $g->order = $misc['good_order'][$g->id];
                $g->save();
            }
        }
    }
}
