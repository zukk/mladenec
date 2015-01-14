<?php
class Model_Tag extends ORM {

	use Seo;
	
    /* How long to wait until recount goods
     * 3600 or 86400 for production */
    const GOODS_COUNT_PERIOD = 60;
    
    public $qty = 0; // for qty in brand

    protected $_table_name = 'z_tag';

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'tree_id' => '', 'code' => '', 'text' => '',
        'title' => '', 'description' => '', 'keywords' => '', 'anchor' => '', 'params' => '',
        'section_id' => '', 'goods_count'=> '', 'goods_count_ts' => ''
    );

    protected $_belongs_to = array(
        'tree' => array('model' => 'tag_tree', 'foreign_key' => 'tree_id'),
    );

    protected $_has_many = array(
        'sections' => array(
            'model' => 'section',
            'through'    => 'z_tag_section',
            'foreign_key'   => 'tag_id',
            'far_key'   => 'section_id'
        ),
        'filter_values' => array(
            'model' => 'filter_value',
            'through'    => 'z_tag_filter_value',
            'foreign_key'   => 'tag_id',
            'far_key'   => 'filter_value_id'
        ),
        'brands' => array(
            'model' => 'brand',
            'through'    => 'z_tag_brand',
            'foreign_key'   => 'tag_id',
            'far_key'   => 'brand_id'
        ),
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
            /*
            'tree_id' => array(
                array('not_empty'),
            ),
            */
            'code' => array(
                array('not_empty'),
                array(array($this, 'unique'), array('code', ':value')),
            )
        );
    }

    /**
     * @param bool $html 
     * @return string
     */
    public function get_link($html = true)
    {
        $url = Route::url('tag', ['code' => $this->code]);
        return $html ? HTML::anchor($url, $this->name) : $url;
    }
    
    public function get_filters() {
        if ( ! $this->loaded()) throw new Exception ('Unable to get filters for not loaded tag');
        
        $section_parent_ids = DB::select('id','parent_id')
                ->from('z_section')
                ->join('z_tag_section')
                ->on('z_tag_section.section_id','=','z_section.id')
                ->where('z_tag_section.tag_id','=',$this->id)
                ->execute()
                ->as_array('id','parent_id');
        
        $section_ids = array_merge(array_keys($section_parent_ids), array_unique($section_parent_ids));
        
        $filters = array();
        
        if ( ! empty($section_ids)) $filters = ORM::factory('filter')->where('section_id','IN', $section_ids)->distinct('filter.id')->find_all()->as_array();
        
        return $filters;
    }

    public function admin_save() {
    
        /* формирование строки параметров */

        $params_arr = $return = array();
        
        $sections_string = '';
        $brands_string   = '';
        
        $sections = $this->sections->order_by('id','asc')->find_all()->as_array('id','id');
        if ( ! empty($sections)) {
            $sections_string = implode('|', $sections);
        }
        
        $brands = $this->brands->order_by('id','asc')->find_all()->as_array('id','id');
        if ( ! empty($brands)) {
            $brands_string = implode('|', $brands);
        }
        
        if ( ! empty($sections_string)) {
            $params_arr[] = 'SECTION_ID=' . $sections_string;
        }
        if ( ! empty($brands_string)) {
            $params_arr[] = ',PROPERTY_BRAND=' . $brands_string;
        }
        
        /* Обработка фильтров */
        $misc = Request::current()->post('misc');
        $filters = array();
        if ( ! empty($misc['filters']) AND is_array($misc['filters'])) {
            $filters = $misc['filters'];
        }
        
        $existing_filter_values = $this->filter_values->order_by('id','asc')->find_all()->as_array('id','filter_id');
        
        /* Проверяем снятые галочки фильтров*/
        foreach($existing_filter_values as $v_id => $f_id) {
            if ( ! isset($filters[$f_id]) OR ! is_array($filters[$f_id]) OR FALSE === array_search($v_id,$filters[$f_id])) {
                $this->remove('filter_values', $v_id);
            }
        }
        
        /* Проверяем установленные галочки фильтров */
        foreach($filters as $filter => $values_array) {
            if ( ! empty($values_array)) {
                /* Заодно - собираем строку параметров */
                $params_arr[] = $filter . '=' . implode('|',$values_array);
            }
            
            foreach($values_array as $val_id) {
                if ( empty($existing_filter_values[$val_id])) {
                    /* Новая галочка установлена */
                     $this->add('filter_values', $val_id);
                }
            }
        }

        if ( ! empty($misc['old_code']) && $misc['old_code'] != $this->code) {
            $this->add_redirect($misc['old_code']);
            $return['messages'] = array('Добавлен редирект <strong>'.$misc['old_code'].'</strong> =>>> <strong>'.$this->code.'</strong>');
        }
        
        $this->params = implode(',', $params_arr);
        $this->save();

        return $return;
    }
    
    public function delete() {
        $sections = $this->sections->find_all()->as_array('id','id');
        $brands = $this->brands->find_all()->as_array('id','id');
        $filter_values = $this->filter_values->find_all()->as_array('id','id');
        
        foreach ($sections as $section_id) {
            $this->remove('sections', $section_id);
        }
        foreach ($brands as $brand_id) {
            $this->remove('sections', $brand_id);
        }
        foreach ($filter_values as $filter_val_id) {
            $this->remove('filter_values', $filter_val_id);
        }
        
        parent::delete();
    }
    
    /**
     * @static Получить ссылки из тегов и строки
     * @param Model_Tag[] $tags
     * @param $text
     * @return string
     */
    public static function links($tags, $text)
    {
        $return = array();
        $titles = explode(',', $text);

        foreach($tags as $k => $t) {
            if ( ! empty($titles[$k])) {
                $return[] = HTML::anchor($t->get_link(0), $titles[$k]);
            } else {
                $return[] = $t->get_link();
            }
        }
        return implode(', ', $return);
    }

    /**
     * Получить дерево тегов
     */
    public static function get_tree()
    {
        return DB::select()->from('z_tag_tree')->order_by('lft')->execute()->as_array('id');
    }

    /**
     * Получить параметры выборки тега
     */
    public function parse_params()
    {
        $return = array();
        if ( empty($this->params)) return $return;

        $parse = explode(',', $this->params);
        foreach($parse as $p) {
            if (strpos($p, 'SECTION_ID=') === 0) {
                $return['c'] = explode('|', substr($p, 11));
            } elseif (strpos($p, 'PROPERTY_BRAND=') === 0) {
                $return['b'] = explode('|', substr($p, 15));
            } elseif (strpos($p, '=') > 0) {
                list($fid, $vids) = explode('=', $p);
                $return['f'][$fid] = explode('|', $vids);
            }
        }
        return $return;
    }

    /**
     * проверяем, не нужен ли редирект для теговой
     */
    public static function check_redirect($from)
    {
        return DB::select('tto.url')
            ->from(array('tag_redirect', 'tfrom'))
            ->join(array('tag_redirect', 'tto'))
                ->on('tfrom.to_id', '=', 'tto.id')
            ->where('tfrom.url', '=', $from)
            ->execute()
            ->get('url');
    }

    /**
     * Добавить редирект на себя с другого урла
     * @param $from
     */
    public function add_redirect($from)
    {
        // может у нас уже есть id текущего урла?
        $to_id = DB::select('id')->from('tag_redirect')->where('url', '=', $this->code)->execute()->get('id');
        if (empty($to_id)) {
            $to_id = DB::insert('tag_redirect')->columns(array('url'))->values(array($this->code))->execute()[0];
        }
        DB::insert('tag_redirect')->columns(array('url', 'to_id'))->values(array($from, $to_id))->execute();
    }
    
    /**
     * 
     * @param string $code
     * @param string $name
     * @param string $title
     * @param array $sections
     * @param array $brands
     * @param array $filters [filter_id=>[fv_id,fv_id, ..., fv_id]]
     * @return \self
     */
    public static function make($code, $name, $title, $sections = array(), $brands = array(), $filters = array())
    {
        $i          = 1;
        $tag        = new self;
        
        $tag->name  = $name;
        $tag->title = $title;
        
        $dup = clone $tag;
        
        for($i=0,$i_str = ''; 0 != $dup->clear()->where('code', '=', $code . $i_str )->count_all();$i++)
        {
            $i_str = '-' . $i;
        }

        $tag->code = $code . $i_str;
        
        try
        {
            $tag->save();
        }
        catch(ORM_Validation_Exception $ve)
        {
            var_dump($ve->errors());
        }
        $tag->set_sections($sections)->set_brands($brands)->set_filters($filters);
        
        return $tag;
    }
    
    public function set_sections($sections)
    {
        $sq = DB::insert('z_tag_section')->columns(array('tag_id', 'section_id'));
        
        $sections_insert = FALSE;
        
        foreach($sections as $s)
        {
            $sq->values(array($this->id, $s));
            $sections_insert = TRUE;
        }
        
        if ($sections_insert)   $sq->execute ();
        
        return $this;
    }
    
    public function set_brands($brands)
    {
        $bq = DB::insert('z_tag_brand')         ->columns(array('tag_id', 'brand_id'));
        
        $brands_insert = FALSE;
        
        foreach($brands as $b)
        {
            $bq->values(array($this->id, $b));
            $brands_insert = TRUE;
        }
        
        if ($brands_insert)     $bq->execute ();
        
        return $this;
    }
    
    public function set_filters($filters)
    {
        $fq = DB::insert('z_tag_filter_value')  ->columns(array('tag_id', 'filter_value_id'));
        
        $filters_insert = FALSE;
        
        foreach($filters as $fk => &$fvalues)
        {
            if ( ! is_array($fvalues)) $fvalues = array_map('trim',explode(',',$fvalues));

            foreach($fvalues as $fv)
            {
                $fq->values(array($this->id,$fv));
                $filters_insert = TRUE;
            }
        }
        if ($filters_insert)    $fq->execute ();
        
        return $this;
    }
    
}