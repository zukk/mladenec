<?php
class Model_Set extends ORM
{
    const RULE_VITRINA          = 1;
    const RULE_EXCEPT_VITRINA   = 2;
    const RULE_SECTION          = 3;
    const RULE_EXCEPT_SECTION   = 4; // Not Section
    const RULE_BRAND            = 5;
    const RULE_EXCEPT_BRAND     = 6;
    const RULE_FILTERVAL        = 7;
    const RULE_EXCEPT_FILTERVAL = 8;
    const RULE_ID               = 9;
    const RULE_EXCEPT_ID        = 10;
    const RULE_MIN_PRICE        = 11;
    const RULE_MAX_PRICE        = 12;
    
    const RULE_ID_ACTION_ADD     = 1;
    const RULE_ID_ACTION_EXCLUDE = 2;
    const RULE_ID_ACTION_CLEAR   = 3;
    
    public $q;
    
    protected $_table_name = 'z_set';

    protected $_belongs_to = array(
        'set_rule' => array('model' => 'set_rule', 'foreign_key' => 'img225'),
	);
	
    protected $_has_many = array(
        'goods'   => array(
                   'foreign_key' => 'set_id',
                   'model' => 'good',
                   'through' => 'z_set_good',
                   'far_key' => 'good_id'
                   ),
        'rules'   => array(
                   'foreign_key' => 'set_id',
                   'model' => 'set_rule',
                   )
    );    

    protected $_table_columns = array(
        'id'            => '', 
        'name'          => '',
        'autoapply'     => '', // Apply rules automatically
        'lock'          => 0,  // User ID who locked the set
        'cnt'           => 0,
        'cnt_shown'     => 0
        );
    
    /**
     * 
     * @param int   $type
     * @param array $vals
     *
     * @return int
     */
    public function add_rules($type, $vals)
    {
        $this->clear_rules($type, $vals); // Сначала удаляем возможно существующие условия
        
        $ins = DB::insert('z_set_rule')
                ->columns(array('set_id','type','val'));
        
        if (is_array($vals))
        {
            foreach($vals as $v)
            {
                if ( ! empty($v))
                {
                    $ins->values(array($this->id, $type, $v));
                }
            }
        }
        else 
        {
            $ins->values(array($this->id, $type, $vals));
        }
        
        return $ins->execute();
    }
    /**
     * 
     * @param array $good_ids
     * @return array
     */
    public static function get_by_goods($good_ids)
    {
        $set_goods = array();
        
        $good_set_ids = DB::select('set_id', 'good_id')
                ->from('z_set_good')
                ->where('good_id', 'IN', $good_ids)
                ->execute()->as_array();
        
        foreach ($good_set_ids as $sg) 
        {
            $set_goods[$sg['set_id']][$sg['good_id']] = $sg['good_id'];
        }
        
        return $set_goods;
    }
    
    /**
     * 
     * @param mixed $type
     * @param array $val
     */
    public function clear_rules($type, $val = null)
    {
        $q = DB::delete('z_set_rule')
                ->where('set_id', '=', $this->id)
                ->where('type', is_array($type) ? 'IN' : '=', $type);
        
        if ( ! is_null($val)) $q->where('val',  is_array($val)  ? 'IN' : '=', $val);
        
        return $q->execute();
    }

    public function good_ids()
    {
        return DB::select(array('good_id'))
                ->from('z_set_good')
                ->where('set_id','=', $this->id)
                ->execute()->as_array();
    }
            
    public function apply()
    {
       // return 0;
        $good_ids = array();
        $exepts = FALSE;
        $by_ids = FALSE;
        
        DB::delete('z_set_good')->where('set_id','=',$this->id)->execute();
        
        $vitrina                = FALSE;
        $section_ids            = array();
        $section_except_ids     = array();
        $brand_ids              = array();
        $brand_except_ids       = array();
        $min_price              = array();
        $max_price              = array();
        $filterval_ids          = array();
        $filterval_except_ids   = array();
        
        $not_id_rules = DB::select('type', 'val')
                ->from('z_set_rule')
                ->where('set_id', '=', $this->id)
                ->where('type','NOT IN',array(self::RULE_ID,self::RULE_EXCEPT_ID))
                ->execute()->as_array('val','type');
        
        
        $q = DB::select(DB::expr($this->id),'id')->from('z_good')->where('z_good.show','=',1);
        $q->where_open();
        
        if (count($not_id_rules))
        {
            $q->where_open()->where(DB::expr('1'),'=','1');
            foreach($not_id_rules as $v=>$t)
            {
                switch($t)
                {
                    case self::RULE_VITRINA:
                        if (Conf::VITRINA_MLADENEC == $v) 
                        {
                            $section_except_ids = DB::select('id')->from('z_section')->where('vitrina','=','ogurchik')->execute()->as_array();
                        }
                        elseif (Conf::VITRINA_EATMART == $v) 
                        {
                            $section_except_ids = DB::select('id')->from('z_section')->where('vitrina','=','mladenec')->execute()->as_array();
                        }
                        break;
                    case self::RULE_EXCEPT_VITRINA:
                        if (Conf::VITRINA_MLADENEC == $v)
                        {
                            $section_except_ids = DB::select('id')->from('z_section')->where('vitrina', '=', 'mladenec')->execute()->as_array();
                        }
                        elseif (Conf::VITRINA_EATMART == $v)
                        {
                            $section_except_ids = DB::select('id')->from('z_section')->where('vitrina', '=', 'ogurchik')->execute()->as_array();
                        }
                        break;
                    case self::RULE_SECTION:
                        $section_ids[$v] = $v;
                        break;
                    case self::RULE_EXCEPT_SECTION:
                        $section_except_ids[$v] = $v;
                        if (isset($section_ids[$v])) unset($section_ids[$v]);
                        break;
                    case self::RULE_BRAND:
                        $brand_ids[$v] = $v;
                        break;
                    case self::RULE_EXCEPT_BRAND:
                        $brand_except_ids[$v] = $v;
                        if (isset($brand_ids[$v])) unset($brand_ids[$v]);
                        break;
                    case self::RULE_MIN_PRICE:
                        $min_price = $v;
                        break;
                    case self::RULE_MAX_PRICE:
                        $max_price = $v;
                        break;
                    case self::RULE_FILTERVAL:
                        $filterval_ids[$v] = $v;
                        break;
                    case self::RULE_EXCEPT_FILTERVAL:
                        $filterval_except_ids[$v] = $v;
                        break;
                }
            }
            
            if ( ! empty($section_ids))         $q->where('z_good.section_id',  'IN',       $section_ids);
            if ( ! empty($section_except_ids))  $q->where('z_good.section_id',  'NOT IN',   $section_except_ids);
            if ( ! empty($brand_ids))           $q->where('z_good.brand_id',    'IN',       $brand_ids);
            if ( ! empty($brand_except_ids))    $q->where('z_good.brand_id',    'NOT IN',   $brand_except_ids);
            $fv_join = FALSE;
            if ( ! empty($filterval_ids))
            {
                $filtervals = ORM::factory('filter_value')->where('id', 'IN', $filterval_ids)->find_all()->as_array('id');
                $fv_by_f = array();

                foreach($filtervals as $fv)
                {
                    $fv_by_f[$fv->filter_id][$fv->id] = $fv->id;
                }
                if ( ! empty($fv_by_f)) // damn magic here
                {

                    $q->join('z_good_filter')
                            ->on('z_good.id','=','z_good_filter.good_id');
                    $fv_join = TRUE;
                    foreach ($fv_by_f as $filt_w_val)
                    {
                        $q->where_open();
                        foreach ($filt_w_val as $fwfv)
                        {
                            $q->or_where('z_good_filter.value_id', '=', $fwfv);
                        }
                        $q->where_close();
                    }
                }
            }
            if ( ! empty($filterval_except_ids))
            {
                if ( ! $fv_join) $q->join('z_good_filter')->on('z_good.id','=','z_good_filter.good_id');

                $q->where('z_good_filter.value_id', 'NOT IN', $filterval_except_ids);
            }
            if ( ! empty($min_price))           $q->where('z_good.price',       '>',        $min_price);
            if ( ! empty($max_price))           $q->where('z_good.price',       '<',        $max_price);
            if ( ! empty($except_ids))          $q->where('z_good.id',          'NOT IN',   $except_ids);
            $q->where_close();
        }
        
        $except_ids = DB::select('val')->from('z_set_rule')->where('set_id', '=', $this->id)->where('type', '=', self::RULE_EXCEPT_ID)->execute()->as_array('val','val');
        $ids = DB::select('val')->from('z_set_rule')->where('set_id', '=', $this->id)->where('type', '=', self::RULE_ID)->execute()->as_array('val','val'); 
        
        if ( ! empty($ids)) $q->or_where_open()->where('z_good.id', 'IN', $ids)->or_where_close();
        
        if ( ! empty($except_ids)) $q->where('z_good.id', 'NOT IN', $except_ids);
        
        $q->where_close();
        
        if(count($not_id_rules) || count($ids) || count($except_ids))
        {
            $ins = DB::insert('z_set_good')->columns(array('set_id','good_id'))->select($q);
            $this->q = (string) $ins;
            $ins->execute();
        }
        
        $this->cnt = DB::select()->from('z_set_good')
                ->where('z_set_good.set_id', '=', $this->id)
                ->execute()->count();
        
        $this->cnt_shown = DB::select('id')->from('z_good')
                    ->join('z_set_good')->on('z_set_good.good_id', '=', 'z_good.id')
                    ->where('z_set_good.set_id', '=', $this->id)
                    ->where('z_good.show', '=', 1)
                    ->execute()->count();
        
        $this->save();
        
        return $this->cnt;
        
    }
    
    public function admin_save()
    {
        $misc = Request::current()->post('misc');
            
        if ( isset($misc['vitrina']))
        {
            $this->clear_rules(self::RULE_VITRINA, array(Conf::VITRINA_MLADENEC, Conf::VITRINA_EATMART, Conf::VITRINA_ALL));
            if ( ! empty($misc['vitrina'])) $this->add_rules(self::RULE_VITRINA, $misc['vitrina']);
        }
        if ( ! empty($misc['by_ids_action']) AND ! empty($misc['by_ids_vals']))
        {
            $misc['by_ids_vals'] = str_replace(',',       ' ', $misc['by_ids_vals']); // Меняем запятые на пробелы
            $misc['by_ids_vals'] = preg_replace('/\s+/u', " ", $misc['by_ids_vals']); // Все что похоже на пробелы делаем пробелами
            $vals_arr = array_map('trim', explode(' ', $misc['by_ids_vals'])); // Разбиваем в массив

            if (in_array($misc['by_ids_action'], array('add_by_code', 'ex_by_code', 'clear_by_code'))) // Артикулы -> id
            {
                $ids = DB::select('id')->from('z_good')->where('code','IN' , $vals_arr);
            }
            else 
            {
                $ids = $vals_arr;
            }
            switch ($misc['by_ids_action'])
            {
                case 'add_by_code':
                case 'add_by_id':
                    $this->add_rules(self::RULE_ID, $ids);
                    break;
                case 'ex_by_code':
                case 'ex_by_id':
                    $this->add_rules(self::RULE_EXCEPT_ID, $ids);
                    break;
                case 'clear_by_code':
                case 'clear_by_id':
                    $this->clear_rules(array(self::RULE_ID, self::RULE_EXCEPT_ID), $ids);
                    break;
            }

        }
        if ( ! empty($misc['section_action']) AND ! empty($misc['section_id']))
        {
            switch ($misc['section_action'])
            {
                case 'add':
                    $this->add_rules(self::RULE_SECTION, $misc['section_id']);
                    break;
                case 'except':
                    $this->add_rules(self::RULE_EXCEPT_SECTION, $misc['section_id']);
                    break;
                case 'clear':
                    $this->clear_rules(array(self::RULE_SECTION, self::RULE_EXCEPT_SECTION), $misc['section_id']);
                    break;
            }
        }
        if ( ! empty($misc['brand_action']) AND ! empty($misc['brand_id']))
        {
            switch ($misc['brand_action'])
            {
                case 'add':
                    $this->add_rules(self::RULE_BRAND, $misc['brand_id']);
                    break;
                case 'except':
                    $this->add_rules(self::RULE_EXCEPT_BRAND, $misc['brand_id']);
                    break;
                case 'clear':
                    $this->clear_rules(array(self::RULE_BRAND, self::RULE_EXCEPT_BRAND), $misc['brand_id']);
                    break;
            }
        }
        if ( ! empty($misc['brand_action']) AND ! empty($misc['brand_id']))
        {
            switch ($misc['brand_action'])
            {
                case 'add':
                    $this->add_rules(self::RULE_BRAND, $misc['brand_id']);
                    break;
                case 'except':
                    $this->add_rules(self::RULE_EXCEPT_BRAND, $misc['brand_id']);
                    break;
                case 'clear':
                    $this->clear_rules(array(self::RULE_BRAND, self::RULE_EXCEPT_BRAND), $misc['brand_id']);
                    break;
            }
        }
        if ( ! empty($misc['filtervals_action']) AND ! empty($misc['filtervals']))
        {
            $fv = array();
            foreach($misc['filtervals'] as $fv_arr) {
                if ( ! empty($fv_arr)) $fv = $fv + array_map('trim',explode(',',$fv_arr));
            }
            switch ($misc['filtervals_action'])
            {
                case 'add':
                    if ( ! empty($fv)) $this->add_rules(self::RULE_FILTERVAL, $fv);
                    break;
                case 'except':
                    if ( ! empty($fv)) $this->add_rules(self::RULE_EXCEPT_FILTERVAL, $fv);
                    break;
                case 'clear':
                    if ( ! empty($fv)) $this->clear_rules(array(self::RULE_FILTERVAL, self::RULE_EXCEPT_FILTERVAL), $fv);
                    break;
            }
        }
        if ( ! empty($misc['min_price']))
        {
            $this->clear_rules(self::RULE_MIN_PRICE);
            $this->add_rules(self::RULE_MIN_PRICE, $misc['min_price']);
        }
        else 
        {
            $this->clear_rules(self::RULE_MIN_PRICE);
        }
        if ( ! empty($misc['max_price']))
        {
            $this->clear_rules(self::RULE_MAX_PRICE);
            $this->add_rules(self::RULE_MAX_PRICE, $misc['max_price']);
        }
        else 
        {
            $this->clear_rules(array(self::RULE_MAX_PRICE,self::RULE_MIN_PRICE));
        }
        $this->apply(); // save inside
    }
}
