<?php
/**
 * Controller 
 *
 * @author mit08
 */
class Controller_Admin_Json extends Controller_Authorised {
    
    protected $_result = array();
    
    public function before() {
        
        parent::before(); 
    }
    
    /**
     * Взамен Controller_Admin::action_image()
     * Загрузка картинок
     * 
     * @return bool
     */
    public function action_mfile_upload() {
        
        $mdir_id = $this->request->post('mdir_id');
        
        if (empty($_FILES['file']) OR ! Upload::not_empty($_FILES['file']) OR ! Upload::valid($_FILES['file'])) {

            $this->_result = array('error' => 'No upload');

        } elseif ($file = Model_Mfile::from_upload($_FILES['file'],$mdir_id)) {
            Log::instance()->add(Log::INFO,'Uploaded a new file ' . $file->id);
            $this->_result = array('filelink' => $file->get_link( FALSE ));

        } else {

            $this->_result = array('error' => 'Bad file');
            
        }
    }
    
    public function action_mdir_add() {
        $name = $this->request->query('name');
        $parent_id = $this->request->query('parent_id');
        
        $dir = new Model_Mdir();
        $dir->parent_id = $parent_id;
        $dir->name = $name;
        $dir->save();
        
        $this->_result = array('id' => $dir->pk());
    }
    
    public function action_mdir_list() {
         $this->_result = Model_Mdir::list_array();
    }
    
    public function action_mediafiles() {
        
        $cat = Model_Mfile::STORAGE_ALIAS.'/';
        $dir = DOCROOT . $cat;
        
        $result_files = Cache::instance()->get('mediafiles_list');
        
        if ( ! empty($result_files) AND is_array($result_files)) {
            
            $this->_result = $result_files;
            return TRUE;
            
        }
        
        $result_files = array();
        
        $dirs = Model_Mdir::list_array();
        $root = array('id'=>0,'name'=>'');
        array_unshift($dirs, $root);
        
        foreach($dirs as $d) {
            
            $files = ORM::factory('mfile')->where('mdir_id','=',$d['id'])->order_by('name','asc')->find_all()->as_array();
            
            foreach($files as $file) {
                
                $file_arr = array(
                    'folder'=> '/' . $d['name']
                );
                $file_arr['title'] = $file->name;
                $file_arr['image'] = '/' . $cat . $file->get_path( FALSE );
                $file_arr['thumb'] = '/' . $file->get_thumb( FALSE );
                
                $result_files[] = $file_arr;
            } 
            
        }      

        Cache::instance()->set('mediafiles_list', $result_files);
        
        // Не отдаем ошибки и сообщения, т.к. это собьет с толку radactor.js
        $this->send_messages = FALSE;
        $this->send_errors = FALSE;
        
        $this->_result = $result_files;

    }
    
    /**
     * Получить объект ORM по id в виде массива
     * 
     * @throws HTTP_Exception_404
     */
    public function action_object() {
        
        $this->model($m = $this->request->param('model'), $this->request->param('id'));
        
        if ( ! $this->model->loaded()) throw new HTTP_Exception_404;
        
        $this->_result = $this->model->as_array();
        
    }
    
    public function action_bind() {
        
        $alias = $this->request->param('alias');
        
        $this->model($m = $this->request->param('model'), $this->request->param('id')); // пытаемся получить объект
        if ( ! $this->model->loaded()) throw new HTTP_Exception_404;
        
        $far_keys = $this->request->post('far_keys');
        
        if ( ! is_array($far_keys) ) return;
        
        $count = 0;
        foreach($far_keys as $fk) {
            
            $link_exist = $this->model->has($alias, $fk);

            if ( ! $link_exist) {

                $this->model->add($alias, $fk);
                $count++;
            }
        }
        $this->_result['count'] = $count;
    }
    
    public function action_unbind() {
        
    }
    
    /* Получить товары, участвующие в акции */
    public function action_promo_goods() {
        $promo_id = intval(   $this->request->post('promo_id'));

        $goods = ORM::factory('promo',$promo_id)->goods->distinct('id')->find_all()->as_array();

        $goodsArr = $this->to_array($goods);
        
        $this->_result = $goodsArr;
    }
    
    public function action_brands() {
        $brands = ORM::factory('brand')->find_all()->as_array();
        $this->_result = $this->to_array($brands);
    }
    
    public function action_sections() {
        $sectionsObj = Model_Section::get_catalog(TRUE);
        foreach ($sectionsObj as $section) {
            $childrens = $this->to_array($section->children);
            $sect = $section->as_array();
            $sect['children'] = $childrens;
            $this->_result[] = $sect;
        }
    }
    
    public function action_goods() {

        $section_id = intval(   $this->request->post('section_id'));
        $brand_id   = intval(   $this->request->post('brand_id'));
        $group_id   = intval(   $this->request->post('group_id'));
        $code       = trim(     $this->request->post('code'));
        $name       = trim(     $this->request->post('name'));
        $page       = trim(     $this->request->post('page'));
        
        $limit  = 30;
        $offset = $page * $limit;
        
        $goods = ORM::factory('good');
        if ($code) {
            $goods->where('good.code', 'LIKE', $code.'%');
        }
        if ($name) {
            
            $goods
                    ->where_open()
                    ->where('good.name', 'LIKE', $name.'%')
                    ->or_where('good.group_name', 'LIKE', $name.'%')
                    ->where_close();
             $this->_result['params']['name_works'] = 'yes';
        }
        
        if ($section_id) {
            $goods->join(array('z_section', 's'))
                    ->on('good.section_id', '=', 's.id')
                        ->where('s.id', '=', $section_id);
        }
        if ($brand_id) {
            $goods->join(array('z_brand', 'b'))
                    ->on('good.brand_id', '=', 'b.id')
                        ->where('b.id', '=', $brand_id);
        }
        if ($group_id) {
            $goods->join(array('z_group', 'g'))
                    ->on('good.group_id', '=', 'g.id')
                        ->where('g.id', '=', $group_id);
        }

        $this->_result['params']['name'] = $name;
        $this->_result['params']['code'] = $code;
        $this->_result['query'] = ''.$goods;
        $objects = $goods->order_by('name','asc')->limit($limit)->find_all()->as_array();
        $this->_result['total'] = $goods->count_all();

        $this->_result['goods'] = $this->to_array($objects);
    }

    public function action_groups() {

       $section_id = $this->request->query('section_id');
       $brand_id   = $this->request->query('brand_id');
       
       $result = array(); 
       $groups = ORM::factory('group');
        
       if ($section_id OR $brand_id) {
           if($section_id)  $groups->where('section_id',   '=', $section_id);
           if($brand_id)    $groups->where('brand_id',     '=', $brand_id  );
       }
        
       $objects = $groups->order_by('group.name','asc')->find_all()->as_array('id');

       $this->_result = $this->to_array($objects);
    }
    
    public function after() {
        if ($this->send_messages) {
            $this->_result['messages'] = $this->messages;
        }
        
        parent::after();
        
        if (Kohana::$environment !== Kohana::PRODUCTION) {
            ob_start();
           // echo(View::factory('profiler/stats'));
           //    $this->_result['profiling'] = ob_get_clean();
        }
        
        $this->return_json($this->_result);
    }
    
    /**
     * Сделать из массива объектов двумерный массив 
     * 
     * @param array $objects массив объектов
     * 
     * @return array двумерный массив
     */
    private function to_array($objects) {
        
        $array = array();
        
        foreach($objects as $obj) {
            $arr = $obj->as_array();
            if(method_exists($obj, 'get_link')) {
                $arr['link'] = $obj->get_link(FALSE);
            }
            if(method_exists($obj, 'get_link_admin')) {
                $arr['link_admin'] = $obj->get_link_admin(FALSE);
            }
            $array[] = $arr;
        }
        
        return $array;
    }
}
?>