<?php
/**
 * Набор товаров
 *
 * @author pks
 */
class Model_Good_Set extends ORM {
    protected $_table_name = 'z_good_set';

    protected $_has_many = array(
        'goods' => array(
            'model' => 'good',
            'through'    => 'z_good_set_rel',
            'foreign_key'   => 'set_id',
            'far_key'   => 'good_id',
        ),
        'groups' => array(
            'model' => 'group',
            'through'    => 'z_good_set_rel',
            'foreign_key'   => 'set_id',
            'far_key'   => 'group_id',
        ),
        'brands' => array(
            'model' => 'brand',
            'through'    => 'z_good_set_rel',
            'foreign_key'   => 'set_id',
            'far_key'   => 'brand_id',
        ),
        'sections' => array(
            'model' => 'section',
            'through'    => 'z_good_set_rel',
            'foreign_key'   => 'set_id',
            'far_key'   => 'section_id',
        ),
        'filter_values' => array(
            'model' => 'filter_value',
            'through'    => 'z_good_set_rel',
            'foreign_key'   => 'set_id',
            'far_key'   => 'filter_value_id',
        )
    );
    
    protected $_table_columns = array(
        'id' => '', 'name' => '', 'cart' => 0, 'active' => 0
    );

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('cart','active');
    }
    
    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            )
        );
    }
    
    public function admin_save() {
        $messages   = array();
        $errors     = array();
        $misc = Request::current()->post('misc');
        if ( ! empty($misc['criteria'])) {
            $criteria = $misc['criteria'];
            
            $section_id = 0;
            if ( ! empty($criteria['section_id'])) {
                $section_id = $criteria['section_id'];
            }
            $brand_id = 0;
            if ( ! empty($criteria['brand_id'])) {
                $brand_id = $criteria['brand_id'];
            }
            $good_ids = array();
            if ( ! empty($criteria['good_ids'])) {
                $good_ids = array_unique(array_slice(array_map('trim',explode(',',$criteria['good_ids'])),0,30));
            }
            $ins = DB::insert('z_good_set_rel')->columns(array('set_id','section_id','brand_id','filter_value_id','good_id'));
            $do_insert = false;
            if ( $section_id > 0 OR $brand_id > 0) {
                if ( ! empty($criteria['filter_values'])) {
                    foreach($criteria['filter_values'] as $fv) {
                        $ins->values(array($this->id,$section_id,$brand_id,$fv,0));
                        $do_insert = true;
                    }
                } else {
                    $ins->values(array($this->id,$section_id,$brand_id,0,0));
                    $do_insert = true;
                }
            } elseif ( ! empty($good_ids) AND is_array($good_ids)) {
                $existing_ids = $this->get_good_ids(9999); // 9999 чтобы limit 50 по умолчанию обмануть
                foreach ($good_ids as $good_id) {
                    if (FALSE !== array_search($good_id, $existing_ids)) {
                        $errors[] = 'Товар с ID ' . $good_id . ' уже добавлен ранее.';
                        continue;
                    }   
                        
                    $tmp_good = ORM::factory('good',$good_id);
                    if ($tmp_good->loaded()) {
                        $ins->values(array($this->id,0,0,0,$good_id));
                        $do_insert = true;
                    } else {
                        $errors[] = 'Товар с ID ' . $good_id . ' не существует.';
                    }
                }
            }
            if ($do_insert) {
                $ins->execute();
                $messages[] = 'Условие добавлено.';
            }
        }
        if ( ! empty($misc['criteries_del'])) {
            DB::delete('z_good_set_rel')
                    ->where('id','IN',array_keys($misc['criteries_del']))
                    ->execute();
        }
        return array('errors'=>$errors,'messages'=>$messages);
    }
    
    public function get_criteries() {
        $criteries = DB::select('*')
                ->from('z_good_set_rel')
                ->where('set_id','=',$this->id)
                ->order_by('good_id','ASC')
                ->execute()
                ->as_array('id');
        
        $section_ids = $brand_ids = $filter_value_ids = $good_ids = array();
        
        $ret = array();
        foreach($criteries as $c) {
            if ( ! empty($c['section_id']))         { $section_ids[$c['section_id']]            = $c['section_id'];     }
            if ( ! empty($c['brand_id']))           { $brand_ids[$c['brand_id']]                = $c['brand_id'];       }
            if ( ! empty($c['filter_value_id']))    { $filter_value_ids[$c['filter_value_id']]  = $c['filter_value_id'];}
            if ( ! empty($c['good_id']))            { $good_ids[$c['good_id']]                  = $c['good_id'];        }
        }
        
        if ( ! empty($filter_value_ids)) {
            $ret['filters'] = ORM::factory('filter')
                    ->join('z_filter_value')
                        ->on('filter.id','=','z_filter_value.filter_id')
                    ->where('z_filter_value.id', 'IN', $filter_value_ids)
                    ->find_all()->as_array('id');
        } else { $ret['filters'] = array(); }
        
        
        $ret['criteries'] = $criteries;
        if ( ! empty($section_ids)) {      $ret['sections']      = ORM::factory('section')      ->where('id', 'IN', $section_ids)       ->find_all()->as_array('id');   }
        if ( ! empty($brand_ids)) {        $ret['brands']        = ORM::factory('brand')        ->where('id', 'IN', $brand_ids)         ->find_all()->as_array('id');   }
        if ( ! empty($filter_value_ids)) { $ret['filter_values'] = ORM::factory('filter') ->where('id', 'IN', $filter_value_ids)  ->find_all()->as_array('id');   }
        if ( ! empty($good_ids)) {         $ret['goods']         = ORM::factory('good')         ->where('id', 'IN', $good_ids)          ->find_all()->as_array('id');  }
        
        return $ret;
    }
    // Получить ID товаров набора
    public function get_good_ids($limit = 50, $offset = 0, $show = NULL, $qty = NULL) {
        
        $criteries = DB::select('*')
                ->from('z_good_set_rel')
                ->where('set_id','=',$this->id)
                ->where('good_id','=',0)
                ->execute()
                ->as_array('id');
        // оптимизация, чтобы через IN по id товаров тянулось
        // Просто добавить ID товаров нельзя, т.к. сломается сортировка и флаг отображения
        $criteries_gids = DB::select('good_id')
                ->from('z_good_set_rel')
                ->where('set_id','=',$this->id)
                ->where('good_id','>',0)
                ->execute()
                ->as_array('good_id','good_id');
        if ( empty($criteries) AND empty($criteries_gids)) return array();
        
        $good_ids_q = DB::select(array('z_good.id','gid'))->distinct(1)->from('z_good');
        
        if ( ! is_null($show)) { 
            $good_ids_q->where('show','=',$show);
        }
        if ( ! is_null($qty)) { 
            $good_ids_q->where('qty', $qty ? '>' : '=', 0);
        }
        $good_ids_q->where_open();
        
        $good_filter_joined = FALSE;
        foreach($criteries as $c) {
            $good_ids_q->or_where_open();
                if ($c['section_id'] > 0)   $good_ids_q->where('z_good.section_id', '=', $c['section_id']);
                if ($c['brand_id'] > 0)     $good_ids_q->where('z_good.brand_id',   '=', $c['brand_id']);
                if ($c['filter_value_id'] > 0)  {
                    if ( ! $good_filter_joined) {
                        $good_filter_joined = TRUE;
                        $good_ids_q->join('z_good_filter')
                            ->on('z_good.id','=','z_good_filter.good_id')
                                ->where('z_good_filter.value_id','=',$c['filter_value_id']);
                    }
                }
            $good_ids_q->or_where_close();
        }
        if($limit < 1) $limit = 1;
        
        if ( ! empty($criteries_gids)) {
            $good_ids_q->or_where('z_good.id','IN',$criteries_gids);
        }
        
        $good_ids = $good_ids_q
                ->where_close()
                ->offset($offset)
                ->limit($limit)
                ->execute()->as_array('gid','gid');
        
        return $good_ids;
    }
    
    public function get_goods($limit=50, $offset=0, $show = NULL, $instock = NULL) {
        $good_ids = $this->get_good_ids($limit, $offset, $show, $instock);
        
        if (empty($good_ids)) return FALSE;
        
        $goods = ORM::factory('good')->where('id','IN',$good_ids);
        if ( ! is_null($show)) {
            $goods->where('show', '=', $show);
        }
        return $goods->find_all()->as_array();
    }
    
    protected function get_link($html = true)
    {
        $link = sprintf('/comment/view/%d', $this->id);
        return $html ? HTML::anchor($link, $this->name) : $link;
    }
    
    public function get_filters() {
        if ( ! $this->loaded()) throw new Exception ('Unable to get filters for not loaded goods filter');
        
        $sections = $this->sections->find_all()->as_array();
        
        $filters = array();
        if (empty($sections) OR ! is_array($sections)) return $filters;
        
        foreach($sections as $section) {
            $filters = array_merge($filters,$section->filters->find_all()->as_array());
        }
        return $filters;
    }
}
