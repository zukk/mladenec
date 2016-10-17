<?php
class Model_Set_Rule extends ORM
{
    const TYPE_VITRINA          = 1;
    const TYPE_EXCEPT_VITRINA   = 2;
    const TYPE_SECTION          = 3;
    const TYPE_EXCEPT_SECTION   = 4; // Not Section
    const TYPE_BRAND            = 5;
    const TYPE_EXCEPT_BRAND     = 6;
    const TYPE_FILTERVAL        = 7;
    const TYPE_EXCEPT_FILTERVAL = 8;
    const TYPE_ID               = 9;
    const TYPE_EXCEPT_ID        = 10;
    const TYPE_MIN_PRICE        = 11;
    const TYPE_MAX_PRICE        = 12;
    
    protected $_table_name = 'z_set_rule';

    protected $_belongs_to = array(
        'set_rule' => array('model' => 'set', 'foreign_key' => 'set_id'),
	);

    protected $_table_columns = array(
        'set_id'    => '',
        'type'      => '', // S, NS, B, NB, FV, NFV, ID, NID
        'val'   => '',
        );

    public function name()
    {
        
    }
    
    /**
     * Условие "Кроме" ?
     * 
     * @return boolean
     */
    public function is_except()
    {
        if (in_array($this->type, array(
            self::TYPE_EXCEPT_VITRINA, 
            self::TYPE_EXCEPT_SECTION, 
            self::TYPE_EXCEPT_BRAND, 
            self::TYPE_EXCEPT_FILTERVAL, 
            self::TYPE_EXCEPT_ID, 
            )))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    
    public function good_ids()
    {
        $q = DB::select('id')->from('z_good')->where('active','=',1);
        switch($this->type)
        {
            case self::TYPE_SECTION:
            case self::TYPE_EXCEPT_SECTION:
                $q->where('section_id', '=', $this->val);
                break;
            case self::TYPE_BRAND:
            case self::TYPE_EXCEPT_BRAND:
                $q->where('brand_id', '=', $this->val);
                break;
            case self::TYPE_FILTERVAL:
            case self::TYPE_EXCEPT_FILTERVAL:
                $q->join('z_good_filter')->on('z_good.id', '=', 'z_good_filter.good_id');
                $q->where('z_good_filter.filter_id', '=', $this->val);
                break;
            case self::TYPE_BRAND:
            case self::TYPE_EXCEPT_BRAND:
                $q->where('brand_id', '=', $this->val);
                break;
            case self::TYPE_MIN_PRICE:
                $q->where('price', '>', $this->val);
                break;
            case self::TYPE_MAX_PRICE:
                $q->where('price', '<', $this->val);
                break;
        }
        $q->execute()->as_array('id', 'id');
    }
}