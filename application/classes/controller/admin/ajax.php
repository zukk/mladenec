<?php
/**
 * Controller for ajax-based UI
 */
class Controller_Admin_Ajax extends Controller_Authorised {
    
    /**
     *
     * @var View
     */
    protected $view;

    /**
     * @var bool
     */
    protected $return_json = FALSE;
    
    /**
     *
     * @var ORM
     */
    protected $model;

    /**
     * array for template variables
     * @var array
     */
    protected $tmpl = array(); // 
    
    protected $errors = array();
    
    public function before() {
        
        parent::before();
    }
    
    public function after() {
        
        parent::after();
        
        if (empty($this->view)) { // Default view 
            $this->view = View::factory('smarty:/admin/ajax/' . Request::current()->action(),$this->tmpl);
        }
        
        // Profiler:
        if (Kohana::$environment === Kohana::DEVELOPMENT ||
            ( ! empty($this->user->login) && in_array($this->user->login, ['zukk']))
        ) {
            $this->layout->profile = View::factory('profiler/stats');
        }
        
        if($this->return_json)
        {
            $this->return_html($this->view->render());
        }
        else
        {
            exit($this->view->render());
        }
    }
    
    /**
     * Рисует списки сущностей по модели
     */
    public function action_list()
    {
        $this->model = ORM::factory($m = $this->request->param('model'));

        $f = 'action_'.$m.'_list'; 

        if (method_exists($this, $f)) { // собственный метод списка есть?

            $this->tmpl = $this->{$f}();

        } else { // рисуем стандартный список

            
            $pager = new Pager($this->model->count_all(), 100);
            
            if (method_exists($this->model, 'admin_order'))
            {
                $this->model->admin_order(); // специальный порядок
            }
            else
            {
                $this->model->order_by('id', 'DESC');
            }

            $this->tmpl = array(
                'list' => $this->model->offset($pager->offset)->limit($pager->per_page)->find_all(),
                'pager' => $pager,
            );
        }

        $this->tmpl['m'] = $m;

        $this->view = View::factory('smarty:admin/'.$m.'/list', $this->tmpl);
        
        if($this->request->method() == 'POST') $this->return_json = TRUE;
    }
    
    public function action_form()
    {
        if ($id = $this->request->param('id')) 
        {
            $this->model($m = $this->request->param('model'), $id); // пытаемся получить объект
            if ( ! $this->model->loaded()) throw new HTTP_Exception_404;
        }
        else
        {
            $this->model($m = $this->request->param('model')); // пытаемся создать
        }
        
        if ($this->request->post('edit'))
        {
            if (method_exists($this->model, 'img')) 
            {
                $this->model = $this->save_form_images($this->model); // upload-and-resize images
            }

            $this->tmpl['ok'] = $this->save_form($this->model, $this->request->post(), Controller_Admin::ignore_fields());
            $this->return_json = TRUE;
        }
        
        $f = 'action_'.$m.'_form'; 
        
        if (method_exists($this, $f))  // собственный метод add|edit есть?
        {
            $this->tmpl = $this->{$f}($this->model);
        }
        
        if ( empty($this->tmpl['name'])) $this->tmpl['name'] = Kohana::message('admin', $m);
        
        $this->tmpl['id']   = $this->model->loaded() ? $this->model->id : FALSE;
        $this->tmpl['i']    = $this->model;
        $this->tmpl['m']    = $m;
        
        $this->view = View::factory('smarty:admin/'.$m.'/form', $this->tmpl);
    }
    
    public function action_set_list()
    {
        $return = array();
        
        $search = $this->request->post('search');
        if ( ! empty($search))
        {
            $this->model->where('name', 'LIKE', '%' . $search . '%');
        }
        
        $pager = new Pager($this->model->reset(FALSE)->count_all(), 100);
        return array(
            'list'  => $this->model->offset($pager->offset)->limit($pager->per_page)->find_all(),
            'pager' => $pager,
            'search' => $search
        );
    }
    public function action_set_form()
    {
        $return = array();
        
        if ($this->request->post('edit')) $return['ok'] = true;
        
        $rules_vitrina = DB::select('val')
                ->from('z_set_rule')
                ->where('set_id','=',$this->model->id)
                ->where('type','=', Model_Set::RULE_VITRINA)
                ->execute()->as_array('val','val');
        
        $return['sections'] = ORM::factory('section')->find_all()->as_array('id');
        
        $top_sections_q = ORM::factory('section')->where('parent_id', '=', 0)->where('active', '=', 1);
        
        if ( isset($rules_vitrina[Conf::VITRINA_MLADENEC])) 
        {
            $return['rules_vitrina'] = Conf::VITRINA_MLADENEC;
            $top_sections_q->where('vitrina', '=', 'mladenec');
        }
        elseif ( isset($rules_vitrina[Conf::VITRINA_EATMART]))
        {
            $return['rules_vitrina'] = Conf::VITRINA_EATMART;
            $top_sections_q->where('vitrina', '=', 'ogurchik');
        }
        else $return['rules_vitrina'] = Conf::VITRINA_ALL;
        
        $return['top_sections'] = $top_sections_q->order_by('vitrina','ASC')
                ->order_by('sort','ASC')->find_all()->as_array();
        
        $return['rules_section'] = ORM::factory('section')->join('z_set_rule')->on('z_set_rule.val','=','section.id')
                ->where('z_set_rule.set_id','=',$this->model->id)
                ->where('z_set_rule.type','=', Model_Set::RULE_SECTION)
                ->order_by('section.parent_id','ASC')->order_by('section.sort','ASC')->find_all()->as_array('id');
        
        $return['rules_section_except'] = ORM::factory('section')->join('z_set_rule')->on('z_set_rule.val','=','section.id')
                ->where('z_set_rule.set_id','=',$this->model->id)
                ->where('z_set_rule.type','=', Model_Set::RULE_EXCEPT_SECTION)
                ->order_by('section.parent_id','ASC')->order_by('section.sort','ASC')->find_all()->as_array('id');
        
        $brands_q = ORM::factory('brand')->where('active','=',1);        
        $return['brands'] =  $brands_q->order_by('name','ASC')->find_all()->as_array();
        
        $return['rules_brand'] = ORM::factory('brand')->join('z_set_rule')->on('z_set_rule.val','=','brand.id')
                ->where('z_set_rule.set_id','=',$this->model->id)
                ->where('z_set_rule.type','=', Model_Set::RULE_BRAND)
                ->order_by('brand.name','ASC')->find_all()->as_array();
        
        $return['rules_brand_except'] = ORM::factory('brand')->join('z_set_rule')->on('z_set_rule.val','=','brand.id')
                ->where('z_set_rule.set_id','=',$this->model->id)
                ->where('z_set_rule.type','=', Model_Set::RULE_EXCEPT_BRAND)
                ->order_by('brand.name','ASC')->find_all()->as_array();
        
        $return['rules_filtervals'] = DB::select('val')->from('z_set_rule')
                ->where('set_id','=',$this->model->id)
                ->where('type','=', Model_Set::RULE_FILTERVAL)
                ->execute()->as_array('val','val');
        
        $return['rules_filtervals_except'] = DB::select('val')->from('z_set_rule')
                ->where('set_id','=',$this->model->id)
                ->where('type','=', Model_Set::RULE_EXCEPT_FILTERVAL)
                ->execute()->as_array('val','val');
        
        if ( ! empty($return['rules_section']))
        {
            $return['filters'] = ORM::factory('filter')
                    ->where('section_id', 'IN', array_keys($return['rules_section']))
                    ->find_all()->as_array('id');

            if ( ! empty($return['filters']))
            {
                $filterval = ORM::factory('filter_value')
                        ->where('filter_id', 'IN', array_keys($return['filters']))
                        ->find_all()->as_array('id');

                $return['filtervals'] = array();

                foreach($filterval as $fv) {
                    $return['filtervals'][$fv->filter_id][$fv->id] = $fv;
                }
            }
        }
        
        $return['rule_min_price'] = DB::select('val')
                ->from('z_set_rule')
                ->where('set_id','=',$this->model->id)
                ->where('type','=', Model_Set::RULE_MIN_PRICE)
                ->limit(1)->execute()->get('val');
        
        $return['rule_max_price'] = DB::select('val')
                ->from('z_set_rule')
                ->where('set_id','=',$this->model->id)
                ->where('type','=', Model_Set::RULE_MAX_PRICE)
                ->limit(1)->execute()->get('val');

        $return['rules_ids'] = DB::select('val')
                ->from('z_set_rule')
                ->where('set_id','=',$this->model->id)
                ->where('type','=', Model_Set::RULE_ID)
                ->execute()->as_array('val','val');
        
        $return['rules_except_ids'] = DB::select('val')->from('z_set_rule')->where('set_id','=',$this->model->id)->where('type','=', Model_Set::RULE_EXCEPT_ID)->execute()->as_array('val','val');
        
        
        
        $goods = ORM::factory('good')->join('z_set_good')->on('good.id', '=', 'z_set_good.good_id')->where('z_set_good.set_id', '=', $this->model->id)->reset(FALSE);
        $pager = new Pager($goods->count_all(), 100);
        
        $return['goods'] = $goods->offset($pager->offset)->limit($pager->per_page)->find_all()->as_array('id');
        $return['goods_pager'] = $pager;
        
        return $return;
    }
    
    public function action_autocomplete()
    {
        $result     = array();
        $modelname  = $this->request->query('model');
        $model      = ORM::factory($modelname);
        $term       = $this->request->query('term');
        $fields     = $this->request->query('fields');

        $section_id = $this->request->query('section_id');
        if ( ! empty($section_id)) {
            $children = ORM::factory('section')->where('parent_id',  '=', $section_id)->find_all()->as_array('id', 'id');
            if ( ! empty($children)) {
                $model->where('section_id', 'IN', $children);
                $model->and_where_open();
            }
        }

        if ( ! empty($fields)) {
            foreach ($fields as $field) {
                $model->or_where($field, 'like', '%' . $term . '%');
            }
            if (!empty($children)) {
                $model->and_where_close();
            }
            $data = $model->find_all()->as_array('id');

//            echo $model->last_query();

            if ( ! empty($data)) {
                //echo $model;

                if (method_exists($this, 'action_' . $modelname . '_autocomplete')) {
                    $result = $this->{'action_' . $modelname . '_autocomplete'}($data);
                } else {
                    $i = 1;
                    foreach ($data as $id => &$item) {
                        $result[$id] = [
                            'id' => $id,
                            'value' => $item->name,
                            'label' => $item->name,
                        ];

                        $i++;
                    }
                }
            }
        }

        $this->tmpl['result'] = json_encode($result);
	}

	protected function action_good_autocomplete($data = [])
    {
		$result = [];

		$goodIds = array_keys($data);
		$hits = DB::select('section_id', 'good_id')
            ->from('z_hit')
            ->where('good_id', 'in', $goodIds)
            ->limit(15)
            ->execute()
            ->as_array('good_id');

		foreach ($data as $id => &$good) {
			$result[$id] = array(
				'id' => $id,
				'value' => $good->id1c. ' ' . $good->group_name . ' ' . $good->name,
				'id1c' => $good->id1c,
				'group_name' => $good->group_name,
				'name' => $good->name,
				'hit' => ! empty($hits[$id]) ? $hits[$id]['section_id'] : '0'
			);
		}

		return $result;
	}

    /**
     * Загрузка файлов в медиа
     * @throws Exception
     */
    public function action_filemanager_upload()
    {
        /* files saving */
        $mdir_id = $this->request->query('mdir_id');
        
        if (empty($mdir_id)) {
            $mdir_id = $this->request->post('mdir_id');
        }
        $this->tmpl['mdir_id'] = $mdir_id;
        $this->tmpl['reload'] = NULL;
        if ( ! empty($_FILES['files']['tmp_name'])) {
            Log::instance()->add(Log::INFO,'Filemanager: uploading files');
            $files = array();
            foreach($_FILES['files']['tmp_name'] as $i => $tmp_name) {
                $files[$i]['name'] = $_FILES['files']['name'][$i];
                $files[$i]['type'] = $_FILES['files']['type'][$i];
                $files[$i]['tmp_name'] = $tmp_name;
                $files[$i]['error'] = $_FILES['files']['error'][$i];
                $files[$i]['size'] = $_FILES['files']['size'][$i];
            }
            $uploaded_mfiles = array();
            $success_filenames = array();
            foreach($files as $i => $file) {
                if ( ! empty($file) && Upload::not_empty($file) && Upload::valid($file)) {
                    $uploaded_mfiles[] = Model_Mfile::from_upload($file, $mdir_id);
                    $this->tmpl['reload'] = empty($mdir_id) ? 0 : $mdir_id;
                } else {
                    $this->msg('Ошибка загрузки файла ' . $file['name'],'errors');
                }
            }
            if ( ! empty($success_filenames)) {
                $this->msg('Успешно загружены файлы: <b>'.  implode(', ', $success_filenames) . '</b>');
            }
        }
    }

    /**
     * Ответ на коммент
     * @throws Kohana_Exception
     */
    public function action_comment_answer()
    {

		$ins = DB::insert('z_comment_answer');
		$ins->columns(array('q_id','answer','answer_by', 'active'));
		$ins->values(array(
			 (int)$this->request->post('question'),
			 $this->request->post('text'),
			 (int)$this->request->post('by'),
			 (int)$this->request->post('active')
		));
		
		$comment = ORM::factory('comment', $this->request->post('question'));
		
		$r = $ins->execute();
		
		$lastId = $r[0];
		
		$answer = ORM::factory('comment_answer')->where('id', '=', $lastId)->find();
		
		Model_History::log('comment_theme', $comment->theme_id, 'answer', $answer->as_array());

		if( $this->request->post('send') ){
			$answer->send();
		}
		
		$this->tmpl['answer'] = $answer;
		$this->tmpl['by'] = Model_Comment_Answer::$answer_by;
	}
	
	public function action_comment_delete_answer()
    {
		
		$answer = ORM::factory('comment_answer', $this->request->query('id'));
		$comment = ORM::factory('comment', $answer->q_id);
		
		Model_History::log('comment_theme', $comment->theme_id, 'answer_delete', $answer->as_array());
		
		$result = DB::delete('z_comment_answer')->where('id', '=', (int)$this->request->query('id'))->execute();
		$this->tmpl['result'] = $result;
	}
	
	public function action_comment_answer_save()
    {
		
		$answer = ORM::factory('comment_answer', $this->request->post('id'));
		$comment = ORM::factory('comment', $answer->q_id);
		
		$answer->answer = $this->request->post('answer');
		$answer->active = $this->request->post('active');
		
		if ($answer->save()) {
			
			Model_History::log('comment_theme', $comment->theme_id, 'answer_save', $answer->as_array());

			$result = 'ok';
		}
		else
			$result = 'no';
		
		$this->tmpl['result'] = $result;
	}
	
	public function action_comment_send_answer()
    {
		
		$answer = ORM::factory('comment_answer')->where('id', '=', (int)$this->request->query('id'))->find();

		if( !$answer->email_sent ){

			$comment = ORM::factory('comment', $answer->q_id);

			Model_History::log('comment_theme', $comment->theme_id, 'email', $answer->as_array());
			
			$answer->send();
		}
		
		$this->tmpl['answer'] = $answer;
	}
	
	public function action_comment_comment_save()
    {
		
		$comment = ORM::factory('comment')->where('id', '=', (int)$this->request->post('id'))->find();
		
		$comment->text = $this->request->post('text');
		$comment->active = $this->request->post('active');
		
		if( $comment->save() ){
			
			Model_History::log('comment_theme', $comment->theme_id, 'comment_save', $comment->as_array());
			$this->tmpl['result'] = 'ok';
		}
	}
	
	public function action_comment_comment_delete()
    {
		
		$comment = ORM::factory('comment')->where('id', '=', (int)$this->request->post('id'))->find();
		
		Model_History::log('comment_theme', $comment->theme_id, 'comment_delete', $comment->as_array());
		if( $comment->delete() ){
			
			$this->tmpl['result'] = 'ok';
		}
	}
	
	public function action_comment_theme_save()
    {
		
		$theme = ORM::factory('comment_theme')->where('id', '=', (int)$this->request->post('id'))->find();
		
		$theme->active = $this->request->post('active');
		$theme->name = $this->request->post('name');
		$theme->internal_rating = (int)$this->request->post('internal_rating');
		
		if( $theme->save() ){
			
			Model_History::log('comment_theme', $theme->id, 'save', $theme->as_array());
			$this->tmpl['result'] = 'ok';
		}
	}
	
	public function action_slider_order(){
		
		$item = ORM::factory('slider_banner', $this->request->post('id'));
		
		$item->order = (int)$this->request->post('value');
		
		$item->save();
		exit('ok');
	}
	
	public function action_filter_order()
    {
		$modelname = $this->request->post('model');
		
		if( empty( $modelname ) )
			$modelname = 'filter';
		
		$w = ORM::factory($modelname)->where('id', '=', (int)$this->request->post('id'))->find();
		if( $modelname == 'filter' ){
			$section = $w->section;
		}
		else{
			$section = $w->filter->section;
		}
		
		Cache::instance()->delete('section' . md5($section->id).'mladenec');
		Cache::instance()->delete('section' . md5($section->id).'ogurchik');
		
		$w->sort = (int)$this->request->post('value');
		$w->save();
		
		die('ok');
	}

	public function action_search_status()
    {
		
		$w = ORM::factory('searchwords')->where('id', '=', (int)$this->request->post('id'))->find();
		$w->status = (int)$this->request->post('value');
		$w->save();
		
		die('ok');
	}
	
    public function action_filemanager() {
        //var_dump($_GET);
        $current_dir_id = Request::current()->query('mdir_id');
        $order_by       = ('id'  == Request::current()->query('order_by'))  ? 'id'  : 'name';
        $order_dir      = ('ASC' == Request::current()->query('order_dir')) ? 'ASC' : 'DESC';
        
        if (empty($current_dir_id)) $current_dir_id = 0;
        
        $this->tmpl['current_dir_id'] = $current_dir_id;
        $this->tmpl['order_by']       = $order_by;
        $this->tmpl['order_dir']      = $order_dir;
        
        $this->tmpl['pathway'] = array();
        
        $pathway_dir_id = $current_dir_id;
        while($pathway_dir_id > 0) {
            $cpd = ORM::factory('mdir',$pathway_dir_id);
            if ( ! $cpd->loaded()) break;
            array_unshift($this->tmpl['pathway'], array('id' => $cpd->pk(),'name' => $cpd->name));
            $pathway_dir_id = $cpd->parent_id;
        }
        
        $this->tmpl['directories'] = ORM::factory('mdir')
            ->where('parent_id', '=', $current_dir_id)
            ->order_by('name','ASC')->find_all()->as_array();
        
        $this->tmpl['files'] = ORM::factory('mfile')
                ->where('mdir_id', '=', $current_dir_id)
                ->order_by($order_by, $order_dir)->find_all()->as_array();
    }
    
    public function action_filemanager_adddir() {
        
    }

    public function action_card_call()
    {
        $id = $this->request->post('id');

        $item = ORM::factory('order', $id);
        $item->call_card = 1;
        $item->save();

        Model_History::log('order', $id, 'Отзвонили карту');
        exit('ok');
    }

    public function action_card_cash()
    {
        $id = $this->request->post('id');

        $item = ORM::factory('order', $id);
        $item->pay_type = Model_Order::PAY_DEFAULT;
        $item->save();

        Model_History::log('order', $id, 'Перевели на НАЛ');
        exit('ok');
    }

    public function action_card_can_pay()
    {
        $id = $this->request->post('id');

        $item = ORM::factory('order', $id);
        // $item->can_pay = 1;
        $item->save();

        Model_History::log('order', $id, 'Разрешили оплату');
        exit('ok');
    }

    public function action_tag_recount()
    {
        Model_Tag::count();
        $this->return_reload();
    }
}
