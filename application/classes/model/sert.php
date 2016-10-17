<?php
class Model_Sert extends ORM {

    protected $_table_name = 'z_sert';

    protected $_table_columns = array(
        'id' => '', 'sert_group_id' => '', 'name' => '', 'preview' => '', 'image' => '','expires'=>''
    );

    protected $_belongs_to = array(
        'small' => array('model' => 'file', 'foreign_key' => 'preview'),
        'big' => array('model' => 'file', 'foreign_key' => 'image'),
        'group' => array('model' => 'sert_group', 'foreign_key' => 'sert_group_id'),
    );
    
    protected $_has_many = array(
        'sections' => array(
                    'foreign_key' => 'sert_id',
                    'model' => 'section',
                    'through' => 'z_sert_rel',
                    'far_key' => 'section_id'
                    ),
        'brands'   => array(
                    'foreign_key' => 'sert_id',
                    'model' => 'brand',
                    'through' => 'z_sert_rel',
                    'far_key' => 'brand_id'
                    ),
        'groups'   => array(
                    'foreign_key' => 'sert_id',
                    'model' => 'group',
                    'through' => 'z_sert_rel',
                    'far_key' => 'group_id'
                    )
          );

    /**
     * @return array
     */ 
    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            ),
            'image' => array(
                array('not_empty'),
            )
        );
    }

    /**
     * Список картинок на заливку
     * @return array
     */
    public function img()
    {
        return array('image' => array(),'preview'=>array(70,70,'image'));
    }

    public function get_brands($have_groups = FALSE) {
        $brands_q = ORM::factory('brand');
        if($have_groups) {
            $brands_q->distinct('id') ->join(array('z_group','gr'))->on('gr.brand_id', '=','brand.id');
        }
        $brands = $brands_q->order_by('brand.name','asc')->find_all();
        return $brands;
    }
    
    public function get_binded() {
        $query_both = DB::query(Database::SELECT,'
            SELECT 
                    `z_sert_rel`.*, 
                    `z_section`.`name` AS `section_name`, 
                    `z_brand`.`name` AS `brand_name`
                FROM 
                    `z_sert_rel`, 
                    `z_section`,
                    `z_brand`
                WHERE 
                    (`z_sert_rel`.`section_id` = `z_section`.`id`)
                    AND (`z_sert_rel`.`brand_id` = `z_brand`.`id`)
                    AND `z_sert_rel`.`sert_id` = :sert_id
        ');
        $query_sections = DB::query(Database::SELECT,'
            SELECT 
                    `z_sert_rel`.*, 
                    `z_section`.`name` AS `section_name`
                FROM 
                    `z_sert_rel`, 
                    `z_section`
                WHERE 
                    `z_sert_rel`.`section_id` = `z_section`.`id`
                    AND `z_sert_rel`.`brand_id` = 0
                    AND `z_sert_rel`.`sert_id` = :sert_id
        ');
        $query_brands = DB::query(Database::SELECT,'
            SELECT 
                    `z_sert_rel`.*, 
                    `z_brand`.`name` AS `brand_name`
                FROM 
                    `z_sert_rel`,
                    `z_brand`
                WHERE 
                    `z_sert_rel`.`section_id` = 0
                    AND `z_sert_rel`.`brand_id` = `z_brand`.`id`
                    AND `z_sert_rel`.`sert_id` = :sert_id
        ');
        
        $query_both     ->param(':sert_id', $this->id);
        $query_sections ->param(':sert_id', $this->id);
        $query_brands   ->param(':sert_id', $this->id);
        
        $both_arr =     $query_both     ->execute()->as_array();
        $sections_arr = $query_sections ->execute()->as_array();
        $brands_arr =   $query_brands   ->execute()->as_array();
        
        $result = array_merge($both_arr,$sections_arr,$brands_arr);
        
        return $result;
    }
    
    public function bind_to_section_and_brand($section_id = 0, $brand_id = 0) {
        /* Nothing to do if all parameters empty */
        if ( ! ($section_id OR $brand_id)) return FALSE;
        
        /* To avoid duplicates */
        $exists = DB::select()
                ->from('z_sert_rel')
                ->where('sert_id',      '=', $this->id)
                ->where('section_id',   '=', $section_id)
                ->where('brand_id',     '=', $brand_id)
                    ->execute()->as_array();
        
        if ( count($exists) > 0) return FALSE;
        
        $columns = array('sert_id', 'section_id','brand_id');
        
        $query = DB::insert('z_sert_rel', $columns)
                ->values(array($this->id, $section_id, $brand_id))
                ->execute($this->_db);
    }
    
    public function unbind_section_and_brand($section_id = 0, $brand_id = 0) {
        /* Nothing to do if all parameters empty */
        if ( ! ($section_id OR $brand_id)) return FALSE;
        
        /* To avoid duplicates */
        $exists = DB::select()
                ->from('z_sert_rel')
                ->where('sert_id',      '=', $this->id)
                ->where('section_id',   '=', $section_id)
                ->where('brand_id',     '=', $brand_id)
                    ->execute()->as_array();
        if ( count($exists) > 1) return FALSE;

        DB::delete('z_sert_rel')
                ->where('sert_id',      '=', $this->id)
                ->where('section_id',   '=', $section_id)
                ->where('brand_id',     '=', $brand_id)
                    ->limit(1)
                    ->execute();
    }
    
    /**
     * Сохранение картинок в админке
     */
    public function admin_save()
    {
        $misc = Request::current()->post('misc');
        
        $section_id = 0;
        if ( ! empty($misc['section_id'])) $section_id = $misc['section_id'];
        $brand_id = 0;
        if ( ! empty($misc['brand_id'])) $brand_id = $misc['brand_id'];
        $group_id = 0;
        if ( ! empty($misc['group_id'])) $group_id = $misc['group_id'];
        
        if($section_id OR $brand_id) $this->bind_to_section_and_brand ($section_id, $brand_id);
        
        if ($group_id   > 0 AND ( ! $this->has('groups',    $group_id)))   $this->add('groups',    $group_id);

    }
}
