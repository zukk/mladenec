<?php

class Controller_Admin extends Controller_Authorised {

    /**
     * Может быть массив вида url => текст, или строка
     * 
     * @var mixed
     */
    protected $breadcrumbs = '';

    /**
     * Url возврата
     * 
     * @var string
     */
    protected $search_query = '';
    
    /**
     * @var ORM
     */
    protected $model = '';
    
    /**
     *
     * @var Smarty_View
     */
    protected $layout;
    
    public static function ignore_fields()
    {
        return [
            'misc',             // Массив дополнительных полей, игнорируемых по умолчанию
            'ajax',
            'edit',             // кнопка submit
            'search_query',     // адрес возврата
            'goods', 'goods_b',           // except goods list ( for actions)
            'prop',             // except good prop ( for goods)
            'good_text',        // тексты для товаров
            'f', 'img', 'tag', 'tag_changed',
            'var',              // for polls
            'sort', 'free', 'new_var', 'new_sort', 'new_free', 'poll_changed',
            'choose_good', 'choose_good_param', 'choose_goods_all',
            'ids',              // Товары, отображаемые в акции
            'goods_show',       // Товары, отображаемые в акции
            'mail', 'spamit', 'clear_list',   // for spam
            'week_day', 'prices', 'new_price', 'new_min_sum' // for zones
        ];
    }
    
    /**
     * В админке свой шаблон и необходим юзер
     *
     * @throws HTTP_Exception_403
     * @throws HTTP_Exception_404
     */
    public function before()
    {
        $this->layout = View::factory('smarty:admin', $this->tmpl);
        
        parent::before();
        
        $this->layout->vitrina = $this->tmpl['vitrina'] = 'admin';
        $this->search_query = Session::instance()->get('search_query');
        Kohana::$hostnames = Kohana::$config->load('domains')->as_array();
        View::bind_global('vitrinas', Kohana::$hostnames);
    }

    /**
     * Профилирование и вывод
     */
    public function after()
    {
        if ($this->user->allow('admin')) $this->layout->profile = View::factory('profiler/stats');
        $this->layout->menu = View::factory('smarty:admin/menu');
        $this->layout->action = $this->request->action();
        
        if ( ! empty($this->breadcrumbs)) {
            $this->layout->breadcrumbs = $this->breadcrumbs;
        }
        if ( ! empty($this->search_query)) {
            $this->layout->search_query = $this->search_query;
        }

        if ( ! empty($this->model)) {
            $this->layout->m = $this->model->object_name();
            $this->layout->model_name = Kohana::message('admin', $this->layout->m);
        }

        $this->layout->messages = array_filter(array_map('array_filter', $this->messages));

        parent::after();
        
        $this->response->body($this->layout->render());
    }

    public function action_filemanager()
    {
        
        if ( ! ($this->user instanceof Model_User) OR ! $this->user->loaded())            throw new Exception ('Access denied');
        if ( ! $this->user->allow('filemanager'))                                         throw new HTTP_Exception_404();

            $vars = array();
        /* id текущей директории */
        $mdir_id = $this->request->param('mdir_id');
        $vars['mdir_id'] = $mdir_id = $mdir_id > 0 ? $mdir_id : 0;
        if($mdir_id > 0) {
            $current_dir = ORM::factory('mdir', $mdir_id);
            if ( ! $current_dir->loaded()) throw new HTTP_Exception_404();
            
        } else {
            $current_dir = NULL;
        }
        
        /* Удаление директории */
        $del_id = $this->request->query('del');
        if ($del_id > 0) {
            $delete_dir = ORM::factory('mdir', $del_id);
            /* Существует ли директория */
            if ( ! $delete_dir->loaded()) throw new Exception('Unable to delete non-existent directory');
            if ($delete_dir->files_count == 0 AND $delete_dir->childs_count == 0) {
                /* Удаляем только при случае когда директория пуста */
                $delete_dir -> delete();
                $this->msg('Директория удалена');
            }
        }
        
        if ($this->request->post('savedir')) {
            /* Обновление / создание директории
             * Данный участок может работать не только с 
             * текущей директорией, но и с другой, id которой
             * указан в поле id формы
             */
           
            $form_data = $this->request->post();
            $ignore_fields = array(
                'id',
                'savedir', // кнопка submit
            );
            if ( ! empty($form_data['id'])) {
                /* update */
                $save_id = $form_data['id'];
                unset($form_data['id']);
            } else {
                /* add */
                $save_id = 0;
                $form_data['created_ts'] = time();
            }
            $mdir = ORM::factory('mdir', $save_id);
            
            /* защита от ситуации когда при попытке обновления директории создастся новая: */
            if (($save_id > 0) AND ! $mdir->loaded()) throw new Exception('Unable to update non-existent directory');
            
            $new_parent_mdir    = FALSE;
            $parent_mdir        = FALSE;
            
            /**
             * @todo Перенести обновление родителей в модель->save()
             */
            $parent_id = $mdir->parent_id;
            if ($save_id > 0 AND $parent_id > 0) {
                /* Перенос в другую директорию */
                $parent_mdir = ORM::factory('mdir', $parent_id);
            }
            if (isset($form_data['parent_id']) AND ($form_data['parent_id'] > 0) AND $form_data['parent_id'] != $parent_id) {
                /* перенесли директорию в другое место, 
                 * пересчитываем потомков старого родителя
                 */
                $new_parent_mdir = ORM::factory('mdir', $form_data['parent_id']);
            } elseif($form_data['parent_id'] < 0) {
                unset($form_data['parent_id']);
            }
            /* Сохранение модели */
            if (empty($form_data['name'])) $form_data['name'] = 'Новая директория';
            $is_okey = $this->save_form($mdir, $form_data,$ignore_fields);
            
            /* Пересчет потомков после сохранения модели 
             * - чтобы учитывать уже обновленные данные 
             */
            if ($parent_mdir !== FALSE) {
                $parent_mdir->childs_count  = Model_Mdir::recount_childs( $parent_mdir->id);
                $parent_mdir->files_count   = Model_Mdir::recount_files(  $parent_mdir->id);
                $parent_mdir->save();
            }
            
            if ($new_parent_mdir !== FALSE) {
                $this->msg('Директория перенесена в '.$new_parent_mdir->name);
                $new_parent_mdir->childs_count  = Model_Mdir::recount_childs( $new_parent_mdir->id);
                $new_parent_mdir->files_count   = Model_Mdir::recount_files(  $new_parent_mdir->id);
                $new_parent_mdir->save();
            }
            
            $vars['ok'] = $is_okey ? TRUE : FALSE;
            if ($is_okey) {
                $this->msg('Данные директории сохранены');
                $search_query = $this->request->post('search_query'); // адрес возврата если есть
                if ( ! empty($search_query)) $this->request->redirect($search_query); // редирект на поиск, если просили и всё сохранилось ок
            }
        }
        /* files saving */
        if ( ! empty($_FILES['files']['tmp_name'])) {
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
                } else {
                    $this->msg('Ошибка загрузки файла ' . $file['name'],'errors');
                }
            }
            if ( ! empty($success_filenames)) {
                $this->msg('Успешно загружены файлы: <b>'.  implode(', ', $success_filenames) . '</b>');
            }
        }
        if (! is_null($current_dir)) {
            
            $vars['pathway'] = $current_dir->get_pathway();
            $vars['current_dir'] = $current_dir;
        }
        /* потомков корня получить отдельным запросом, т.к. объекта корневой директории не существует */
        $vars['root_subdirs'] = ORM::factory('mdir')->where('parent_id','=',0)->order_by('name','asc')->find_all()->as_array();
        if( $mdir_id > 0) {
            $vars['subdirs'] = ORM::factory('mdir')->where('parent_id','=',$mdir_id)->order_by('name','asc')->find_all()->as_array();
        } else {
            /* Не запрашиваем повторно потомков корня */
            $vars['subdirs'] = $vars['root_subdirs'];
        }
        
        $vars['files'] = ORM::factory('mfile')
                ->where('mdir_id','=',$mdir_id)->order_by('name','asc')
                ->find_all()->as_array();
        
        $vars['subdirs_count']      = count($vars['subdirs']);
        $vars['directories_array']  = Model_Mdir::list_array();
        $vars['model_name'] = 'Файловый менеджер';
        $vars['module_name'] = 'Файловый менеджер';
        
        $this->layout->body = View::factory('smarty:admin/filemanager/index', $vars)->render();
    }
    
    /**
     * Главная страница админки - TODO - убрать запросы из контроллера!
     */
    public function action_index() 
    {
        $vals = array();
        $from = date('Y-m-d', strtotime('1 month ago'));
		$vals['orders'] = DB::query(Database::SELECT, "SELECT *, sdate as date FROM z_stat ORDER BY id DESC LIMIT 10")->execute();
		
        $vals['regs'] = DB::query(Database::SELECT, "
            SELECT DATE( FROM_UNIXTIME(created) ) AS date, COUNT( * ) AS regs
            FROM  `z_user`
            WHERE created > UNIX_TIMESTAMP('".$from."')
            GROUP BY 1
            ORDER BY 1 DESC
        ")->execute();

        $vals['goods'] = DB::query(Database::SELECT, "
            SELECT count(*) as total, sum(new) as new FROM `z_good` WHERE `show` = 1 AND price > 0 AND qty != 0
        ")->execute()->current();

        $vals['comments'] = DB::query(Database::SELECT, "SELECT count(*) as total, sum(IF(answer_by = 0, 1, 0)) as new FROM  `z_comment`")->execute()->current();
        $vals['return'] = DB::query(Database::SELECT, "SELECT count(*) as total, sum(IF(fixed = 0, 1, 0)) as new FROM  `z_return`")->execute()->current();
        $vals['reviews'] = DB::query(Database::SELECT, "SELECT count(*) as total, sum(IF(active = 0, 1, 0)) as new FROM  `z_good_review`")->execute()->current();
        $vals['brands'] = DB::query(Database::SELECT, "SELECT count(*) as total FROM  `z_brand`")->execute()->current();

        $this->layout->body = View::factory('smarty:admin/index', $vals)->render();
	}

	public function action_searchwords_list()
    {
		
		$returner = array();
		
        $query = ORM::factory('searchwords')->distinct('id');
		
		$from = $this->request->query('from');
		if( empty( $from ) )
			$from = date('Y-m-d', time()-604800 /*неделя*/);

		$to = $this->request->query('to');
		if( empty( $to ) )
			$to = date('Y-m-d');
		
		$returner['from'] = $from;
		$returner['to'] = $to;
		
		$from .= ' ' . date('H:i:s');
		$to .= ' ' . date('H:i:s');

		$counts = DB::query(Database::SELECT, "
			SELECT count(id) as total, word_id FROM `search_words_stat` WHERE `time` >= '$from' AND `time` <= '$to' GROUP BY word_id
		")->execute()->as_array('word_id');

		if( !empty( $counts) ){
			
			$query->where('id', 'in', array_keys( $counts ) );
			
			$s0 = $this->request->query('status0');
			if(  $s0 != '' ){
				if( $s0 == 1 )
					$query->where('status','=', '0');
				else
					$query->where('status','!=', '0');
			}
			
			$s0 = $this->request->query('status1');
			if(  $s0 != '' ){
				if( $s0 == 1 )
					$query->where('status','=', '1');
				else
					$query->where('status','!=', '1');
			}
			
			$s0 = $this->request->query('status2');
			if(  $s0 != '' ){
				if( $s0 == 1 )
					$query->where('status','=', '2');
				else
					$query->where('status','!=', '2');
			}
			
			$s0 = $this->request->query('is_error');
			if(  $s0 != '' ){
				$query->where('is_error','=', $s0);
			}
			
			$offset = $this->request->query('offset');
			if( !empty( $offset ) )
				$offset = (int)$this->request->query('offset');
			else $offset = 0;
			
			$returner['list']  = $query->order_by('id', 'desc')->offset($offset)->limit(30)->find_all()->as_array('id');
			$returner['count'] = $query->count_all();
			
			foreach( $counts as $key => &$c ){
				if( !isset( $returner['list'][$key] ) )
					continue;
				
				$returner['list'][$key] = $returner['list'][$key]->as_array();
				if( empty( $counts[$key] ) ){
					unset( $returner['list'][$key] );
				}
				else{
					$returner['list'][$key]['count']  = $counts[$key]['total'];
				}
			}

			usort($returner['list'], function($a, $b){

				if( $a['count'] < $b['count'] ){
					
					return true;
				}
				
				return false;
			});

			$returner['brandRels'] = [];
			$brandIds = [];
			if( !empty( $returner['list'] ) ){
				
				$result = DB::query(Database::SELECT, "SELECT * FROM search_words_brands WHERE word_id IN (" . implode( ', ', array_keys( $returner['list'] ) ) . ")")->execute();

				while( $row = $result->current()){

					$brandIds[] = $row['brand_id'];
					$returner['brandRels'][$row['word_id']][] = $row['brand_id'];
					$result->next();
				}
			}
			
			if( !empty( $brandIds ) ){

				$returner['brands'] = DB::query(Database::SELECT, "SELECT * FROM z_brand WHERE id IN (" . implode( ', ', $brandIds ) . ")")->execute()->as_array('id');
			}
			else
				$returner['brands'] = array();
		}
		else{
			$returner['list'] = [];
			$returner['brandRels'] = [];
			$returner['brands'] = [];
			$returner['count'] = 0;
			$returner['rand'] = 0;
			$offset = 0;
			$counts = array();
		}
		
		$returner['counts'] = $counts;
		
		if( $offset > 0 ){
		
	        die(View::factory('smarty:admin/searchwords/trs', $returner)->render());
		}
		
		return $returner;
	}
    
    /**
     * Рисует списки сущностей по модели
     */
    public function action_list()
	{
        $this->model = ORM::factory($m = $this->request->param('model'));

        if ($this->request->post('save')) $this->update_many($this->request->post()); // сохранение всего списка TODO - проверить использование

        $f = 'action_'.$m.'_list'; // собственный метод списка есть?

        if (method_exists($this, $f)) {

            $tmpl_vars = $this->{$f}();

        } else { // рисуем стандартный список

            $pager = new Pager($this->model->count_all(), 20);

            if (method_exists($this->model, 'admin_list')) $tmpl_vars = $this->model->admin_list(); // Фильтры в списке
            
            if (method_exists($this->model, 'admin_order')) {

                $this->model->admin_order(); // специальный порядок

            } else {

                $this->model->order_by('id', 'DESC');
            }

            $tmpl_vars['list']  = $this->model->offset($pager->offset)->limit($pager->per_page)->find_all();
            $tmpl_vars['pager'] = $pager;
        }

        $tmpl_vars['m'] = $m;

        $this->layout->body = View::factory('smarty:admin/'.$m.'/list', $tmpl_vars)->render();
    }

    /**
     * клон описания размазать по группе
     */
    public function action_clone_group_txt()
    {
        $id =  $this->request->param('id');
        $good = ORM::factory('good', $id);

        if ($good->loaded()) {
            $clones = ORM::factory('good')
                ->with('prop')
                ->where('_desc', '=', 0)
                ->where('_optim', '=', 0)
                ->where('group_id', '=', $good->group_id)
                ->where('qty', '!=', 0)
                ->find_all()
                ->as_array('id');

            $text = $good->text->find_all()->as_array('name', 'content');
            $photos = $good->get_images();

            foreach ($clones as $id => $clone) {

                // описания
                $ins_text = DB::insert('z_good_text', ['good_id', 'name', 'content']);
                foreach ($text as $name => $content) {
                    if ($name == 'Полное описание') { // сгенерим текст для клона
                        $content = preg_replace('~^(<p>.*</p>)(.*)$~isuU', '<p><strong>' . $clone->group_name . ' ' . $clone->name . '</strong></p>$2', $content);
                    }
                    $ins_text->values(['good_id' => $clone->id, 'name' => $name, 'content' => $content]);
                }
                DB::query(Database::INSERT, $ins_text . ' ON DUPLICATE KEY UPDATE content = VALUES(content)')->execute();

                $clone->prop->_desc = 1;

                // фотки
                $clone_photo = DB::insert('z_good_img')
                    ->columns(['good_id', 'file_id', 'size']);

                foreach ($photos as $n => $imgs) {
                    foreach ($imgs as $size => $id) {
                        $prop = 'img' . $size;
                        $clone->prop->{$prop} = $id;
                        $clone_photo->values(['good_id' => $clone->id, 'file_id' => $id, 'size' => $size]);

                    }
                    $clone->image = $clone->prop->img255;
                }
                $clone_photo->execute();

                $clone->prop->img500 = $good->prop->img500;

                $clone->save();

                $clone->prop->_graf = 1;
                $clone->prop->save();
            }
        }

        exit('ok');

    }
    /**
     * Добавление/список тегов для акций
     * @return array
     * @throws Kohana_Exception
     */
    public function action_actiontag_list()
    {
        $actiontagArr = [];

        $title = $this->request->post('title');
        $url = $this->request->post('url');
        $order = $this->request->post('order');

        if ( ! empty($title) && ! empty($url)) {
            $tag = new Model_Actiontag();

            $tag->values(['title' => $title, 'url' => $url, 'order' => $order])
                ->save();
        }

        $query = $this->model;
        $actiontagArr['pager'] = $pager = new Pager($query->count_all(), 20);
        $actiontagArr['actiontag'] = $query
            ->order_by('id', 'DESC')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all();

        return $actiontagArr;
    }

    public function action_wikicategories_list()
    {
        $goods_ids = $this->request->post('goods');
        $wiki_cat_id = $this->request->post('wiki_cat_id');

        $return = [];
        if ( ! empty($goods_ids) && ! empty($wiki_cat_id)) {
            $return = Model_Good::save_wikicat($wiki_cat_id, $goods_ids);
        }
        return array($return);
    }

    public function action_ozontypes_list()
    {
        $goods_ids = $this->request->post('goods');
        $ozon_type_id = $this->request->post('ozon_type_id');

        $return = [];
        if ( ! empty($goods_ids) && ! empty($ozon_type_id)) {
            $return = Model_Good::save_ozon($ozon_type_id, $goods_ids);
        }
        return array($return);
    }

    public function action_getwikigoods()
    {
        $wiki_cat_id = $this->request->post('id');

        $good = new Model_Good();
        $all_goods = $good->get_goodswiki($wiki_cat_id);

        $tmpl = array(
            'goods' => $all_goods
        );

        exit(View::factory('smarty:admin/good/chosen', $tmpl)->render());
    }

    public function action_valupd()
    {
        $good = ORM::factory('good', $this->request->post('id'));

        if ($good->loaded()) {
            $good->wiki_cat_id = 0;
            $good->save();
        }
        exit;
    }

    public function action_actiontags()
    {
        $actiontag = new Model_Actiontag();
        $res = $actiontag->actiontags();

        $this->return_json($res);
    }

    public function action_actiontag_edit(Model_Actiontag $data)
    {

        $edit_id = $this->request->post('id');

        $tag = new Model_Actiontag();
        if(isset($edit_id) && !empty($edit_id)){
            $form_vars['res'] = $tag->edit_actiontag($edit_id, $data);
            $this->request->redirect(Route::url('admin_list', array('model' => 'actiontag')));
        } else {
            $id = $data->id;
            $form_vars['res'] = $tag->get_actiontag($id);
        }

        return $form_vars;
    }

    /**
     * Добавление сущности
     */
    public function action_add()
    {

        $this->model($m = $this->request->param('model'));

        $errors = array();

        if ($this->request->post('edit')) {

            if (method_exists($this->model, 'img')) { // upload-and-resize images
                $this->model = $this->save_form_images($this->model);
            }
            $post = $this->request->post();

            if (isset($post['week_day']) && is_array($post['week_day'])) {
                $post['week_day'] = array_reduce($post['week_day'], function($a, $b) {return $a | $b;});
            }
            foreach($post as $n => $v) if (is_array($v) AND isset($v['Date_Day'])) $post[$n] = $this->read_date($v); // read dates if any

            $this->model->values($post);

            if ($this->model->validation()->check()) {

                $this->model->save();
				
				if(method_exists($this->model, 'seo_save' ) ){
                    $this->messages_add($this->model->seo_save($post['seo']));
				}
				
                Model_History::log($m, $this->model->id, 'add', $this->model->as_array());

                $this->request->redirect(Route::url('admin_edit', array('model' => $m, 'id' => $this->model->id))); // перейдём к редактированию

            } else {

                $errors = $this->model->validation()->errors('admin/'.$m);
            }
        }

        // собственный метод add есть?
        $f = 'action_'.$m.'_add'; 
        if (method_exists($this, $f)) $form_vars = $this->{$f}($this->model);

        $form_vars['i'] = $this->model;
        
        $this->layout->body = View::factory('smarty:admin/add', array(
            'form' => View::factory('smarty:admin/'.$m.'/form', $form_vars)->render(),
            'name' => Kohana::message('admin', $m),
            'm'    => $m,
        ))->render();

        $this->messages_add(array('errors' => $errors));
    }

    public function action_googlecategories_list()
    {
        $google_cat_id = $this->request->post('google_cat_id');
        if(isset($google_cat_id) && !empty($google_cat_id)){

            $goods_ids = $this->request->post('goods');

            if ( ! empty($goods_ids) && ! empty($google_cat_id)) {
                Model_Good::save_googlecat($google_cat_id, $goods_ids);
            }
        }

        $google_categories['data'] = DB::select('category_id', 'parent_id', 'name_cat')
            ->from('google_categories')
            ->as_object()
            ->execute();

        return $google_categories;
    }

    public function action_valgooglecatupd()
    {
        $good = ORM::factory('good', $this->request->post('id'));

        if ($good->loaded()) {
            $good->google_cat_id = 0;
            $good->save();
        }
        exit;
    }

    public function action_getgooglegoods()
    {
        $google_cat_id = $this->request->post('id');

        $good = new Model_Good();
        $all_goods = $good->get_goodsgoogle($google_cat_id);

        $tmpl = array(
            'goods' => $all_goods
        );

        exit(View::factory('smarty:admin/good/chosen', $tmpl)->render());
    }
	
	/**
	 * @param Model_Good $good
	 * @return array
	 */
	public function action_good_edit(Model_Good $good)
	{
		$returner = [];
		$returner['sectionTabs'] = empty($good->section->settings['goodTabs']) ? ['Полное описание'] : $good->section->settings['goodTabs'];

/*
        if (empty($good->prop->tabs)) {
			$good->prop->tabs = json_encode(
					['Полное описание' => $good->prop->desc]
				);
		}
*/

		$returner['goodTabs'] = $good->text->find_all()->as_array('name', 'content');
		
		return $returner;
	}

    /**
     * Редактирование сущности
     * @throws HTTP_Exception_404
     */
    public function action_edit()
    {
        $this->model($m = $this->request->param('model'), $this->request->param('id')); // пытаемся получить объект
        if ( ! $this->model->loaded()) throw new HTTP_Exception_404;

        $view = View::factory('smarty:admin/edit');

        if ($this->request->post('edit')) {
            $form_data = $this->request->post();
            $model_name = $this->model->object_name();
            if($model_name == 'good') {
                if (isset($form_data['seo_auto'])) {
                    $form_data['seo_auto'] = 1;
                } else {
                    $form_data['seo_auto'] = 0;
                }
            }
            if (method_exists($this->model, 'img')) { // upload-and-resize images
                $this->model = $this->save_form_images($this->model);
            }

            $is_okey = $this->save_form($this->model, $form_data, self::ignore_fields());

            if ($is_okey && method_exists($this->model, 'seo_save')) $this->messages_add($this->model->seo_save($form_data['seo']));

            $view->ok = $is_okey;

            if ($is_okey) {
                $search_query = $this->request->post('search_query'); // адрес возврата если есть
                if ( ! empty($search_query)) $this->request->redirect($search_query); // редирект на поиск, если просили и всё сохранилось ок
            }
        }

        // собственный метод edit есть?
        $f = 'action_'.$m.'_edit';
        if (method_exists($this, $f)) {
            $form_vars = $this->{$f}($this->model);
        }
        $form_vars['i'] = $this->model;

        $activate_coupon = $this->request->post('activate_coupon');

        if(isset($activate_coupon)){
            $i = $form_vars['i'];
            $orderdata = $i->getorderdata();
            $get_goods = $i->get_goods();

            foreach($orderdata as $order_data){
                $ship_date = date('d.m.y', strtotime($order_data['ship_date']));
                $city = $order_data['city'];
                $street = $order_data['street'];
                $house = $order_data['house'];
                if ($order_data['correct_addr'] == 1){
                    $correct_addr = 'Y';
                } else {
                    $correct_addr = 'N';
                }
                $latlong = $order_data['latlong'];
                $enter = $order_data['enter'];
                $lift = $order_data['lift'];
                $floor = $order_data['floor'];
                $domofon = $order_data['domofon'];
                $kv = $order_data['kv'];
                $mkad = $order_data['mkad'];
                $comment = $order_data['comment'];
            }

            if($i->status != 'F'){
                $string = '';
                $string .= "ЗАКАЗ\n";
                $string .= $ship_date."©".$i->id."©".$i->user_id."©F©0©".$i->price."©0©0©0©0©0\n";
                $string .= "АДРЕС: ".$city."|".$street."|".$house."©".$correct_addr."©".$latlong."©".$enter."|".$lift."|".$floor."|".$domofon."|".$kv."|".$mkad."|".$comment."\n";
                $string .= "СКИДКА: ".$i->discount."\n";
                $string .= "ОПЛАТА: ".$i->pay_type."©".$i->price."©N\n";
                foreach ($get_goods as $g){
                    $string .= $g->code."©".$g->quantity."©".$g->price." \n";
                }
                $string .= "КОНЕЦЗАКАЗА";

                $domains = Kohana::$config->load('domains')->as_array(); // = Kohana::$config->load('domains')->as_array();
                $host = $domains['mladenec']['host'];

                $url = $host.'/1c/orders_import.php?encoding=utf8';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $string);

                $data = curl_exec($ch);
                $form_vars['res_gift'] = 0;
                if (curl_errno($ch)) {
                    print "Error: " . curl_error($ch);
                } else {
                    $form_vars['res_gift'] = 1;
                    $is_okey = true;
                    $view->ok = $is_okey;
                }
                curl_close($ch);
            }
        }
        $view->form = View::factory('smarty:admin/'.$m.'/form', $form_vars)->render();
        if ( empty($form_vars['name'])) {
            $view->name = Kohana::message('admin', $m);
        } else {
            $view->name = $form_vars['name'];
        }
        $view->m = $m;

        // если пришли со списка этой же модели - пропишем в форму адрес возврата
        if (parse_url($this->request->referrer(), PHP_URL_PATH) == Route::url('admin_list', array('model' => $m))) {
            $this->search_query = $view->search_query = parse_url($this->request->referrer(), PHP_URL_QUERY);
            Session::instance()->set('search_query', $this->search_query);
        }

        $this->layout->body = $view->render();

        $this->layout->history = ORM::factory('history')
            ->with('user')
            ->where('module', '=', $m)
            ->where('item_id', '=', $this->model->id)
            ->order_by('id', 'DESC')
            ->limit(20)
            ->find_all();
    }
    
    /**
     * 
     * @throws HTTP_Exception_404
     */
    public function action_bind() {
        
        $this->model($m = $this->request->param('model'), $this->request->param('id')); // пытаемся получить объект
        if ( ! $this->model->loaded()) throw new HTTP_Exception_404;
        
        $alias      = $this->request->param('alias');
        $far_key    = $this->request->param('far_key');
        
        $link_exist = $this->model->has($alias, $far_key);
        
        if ( ! $link_exist) {
            
            $this->model->add($alias, $far_key);
        }
        
        $this->request->redirect(Route::url('admin_edit',array('model'=> $m, 'id' => $this->request->param('id'))));
    }
    
    /**
     * 
     * @throws HTTP_Exception_404
     */
    public function action_unbind() {
        $m = $this->request->param('model');
        $item_id = $this->request->param('id');
                
        $this->model($m, $item_id); // пытаемся получить объект
        if ( ! $this->model->loaded()) throw new HTTP_Exception_404;
        
        $alias = $this->request->param('alias');
        $far_key = $this->request->param('far_key');
        
        $link_exist = $this->model->has($alias, $far_key);
        
        if ($link_exist) {
            
            $this->model->remove($alias, $far_key);
        }
        
        $this->request->redirect(Route::url('admin_edit', array('model' => $m, 'id' => $item_id)));
    }
    
    /**
     * 
     * @throws HTTP_Exception_404
     */
    public function action_sert_unbind() {

        $sert_id = $this->request->param('id');
        $section_id = $this->request->query('section_id');
        $brand_id = $this->request->query('brand_id');
                
        $this->model = ORM::factory('sert', $sert_id); // пытаемся получить объект
        if ( ! $this->model->loaded()) throw new HTTP_Exception_404;
        
        $this->model->unbind_section_and_brand($section_id, $brand_id);
        
        $this->request->redirect(Route::url('admin_edit',array('model'=>'sert','id'=>$sert_id)));
    }
    
    public function action_group_ajax_get() {
        /* is a brand or section selected? */
        $section_id =   $this->request->post('section_id');
        $brand_id =     $this->request->post('brand_id');

        $result = array();
        
        $groups = ORM::factory('group');
        
        if ($section_id OR $brand_id) {
            if($section_id)  $groups->where('section_id',   '=', $section_id);
            if($brand_id)    $groups->where('brand_id',     '=', $brand_id  );
        }
        
        $result_obj = $groups->order_by('group.name','asc')->find_all()->as_array('id');
        
        $result = array();
        
        foreach($result_obj as $obj) {
            
            $result[] = $obj->as_array();
        }
        
        $this->return_json($result);
    }
    
    public function action_good_sert_ajax_search()
    {
        // is a brand or section selected? 
        $section_id     = $this->request->post('section_id');
        $brand_id       = $this->request->post('brand_id');
        $name           = $this->request->post('name');
        $code           = $this->request->post('code');
        $code1c         = $this->request->post('code1c');
        $filter_values  = $this->request->post('filterVals');
        $goods_page     = $this->request->post('goods_page');
        $active         = $this->request->post('goods_active');
        $show           = $this->request->post('goods_show');
        
        $goods = ORM::factory('good');
        $fvals = array();
        if ( ! empty($filter_values) AND is_array($filter_values)) {
            $goods->join(array('z_good_filter','good_filter'))->on('good.id','=','good_filter.good_id');
            foreach ($filter_values as $fv) {
                $fvals[$fv] = $fv;
            }
            $goods->where('good_filter.value_id','IN',$fvals);
        }
        
        $vars = array(
            'sections'      => Model_Section::get_catalog(TRUE),
            'section_id'    => $section_id,
            'brands'        => ORM::factory('brand')->where('active','=',1)->order_by('name','asc')->find_all()->as_array(),
            'brand_id'      => $brand_id,
            'name'          => $name,
            'code'          => $code,
            'active'        => $active,
            'show'          => $show,
            'code1c'        => $code1c,
            'filter_values' => $fvals,
            'page'          => $goods_page
        );
        
        if ($section_id > 0) {
            $section = ORM::factory('section',$section_id);
            if ($section->loaded()) {
                $goods->where('section_id','=',$section_id);
                $vars['filters'] = $section->filters->find_all()->as_array();
            }
        }
        if ($brand_id > 0) { $goods->where('brand_id','=',$brand_id); }
        if ($active > 0) { $goods->where('active','=',1); }
        if ($show > 0) { $goods->where('show','=',1); }
        
        if ($section_id OR $brand_id) {
            $vars['goods'] = $goods->reset(FALSE);
            $vars['goods_total'] = $goods->count_all();
            $vars['goods'] = $goods->limit(30)->offset(($goods_page) * 30)->find_all()->as_array();
        }
        exit(View::factory('smarty:admin/good_set/search', $vars)->render());
    }

    /**
     * Удаление экземпляра сущности
     * @throws HTTP_Exception_404
     */
    public function action_del()
    {
        $this->model($m = $this->request->param('model'), $this->request->param('id'));
        if (!$this->model->loaded()) throw new HTTP_Exception_404;

        if (method_exists($this->model, 'img')) {

            foreach($this->model->img() as $i => $tmp) {
                if (!empty($this->model->{$i})) {
                    $f = new Model_File($this->model->{$i});
                    if ($f->loaded()) $f->delete();
                }
            }
        }

        $this->model->delete();
        Model_History::log($m, $this->model->id, 'delete', $this->model->as_array());

        if ( ! $return_url = $this->request->query('return_url')) {
            $return_url = Route::url('admin_list', array('model' => $m));
        }
        $this->request->redirect($return_url);
    }

    
    public function action_medialib() {
        
        $cat = '/upload/medialibrary';
        $dir = DOCROOT . $cat;
        
        $images = Cache::instance()->get('medialib_contents');
        if ( ! empty($images) AND is_array($images)) $this->return_json($images);
        
        $images = array();
        
        $files = Fs::ls($dir, TRUE);
            
        foreach($files as $f) {
            
            if ( ! is_dir($f)) {
                
                $filename = basename($f);
                
                $img = array();
                $img['folder'] = $folder = str_replace($dir, '', dirname($f)).'/';
                $img['image']  = $cat . $folder . $filename;
                $img['title']  = $filename;
                
                $thumb_path = $folder . '.thumb/' . $filename;
                
                if (file_exists($dir . $thumb_path)) {
                    
                    $img['thumb'] = $cat . $thumb_path;
                    
                } else {
                    list($w, $h) = getimagesize($dir . $folder . $filename);

                    if(($w < 150) AND ($h < 150)) {
                        /* small image, no matter to make thumb, using itself */
                         $img['thumb'] = $cat . $folder . $filename;
                         
                    } else {
                        
                        if( ! file_exists($dir . $folder . '.thumb')) {
                            mkdir($dir . $folder . '.thumb', '0755');
                        }
                        
                        if (is_writable($dir . $folder . '.thumb')) {
                            /* trying to make a thumb */
                            $thumb = Image::factory($dir . $folder . $filename);
                            $thumb->resize(150, 150, Image::INVERSE);
                            $thumb->save($dir . $folder . '.thumb/'.$filename);
                            $img['thumb'] = $cat . $folder . '.thumb/'.$filename;
                            
                        } else {

                            $img['thumb'] = '/i/no_128.png';
                        }
                    }
                }
                $images[] = $img;
            }
            
        }
        
        Cache::instance()->set('medialib_contents', $images);
        
        $this->return_json($images);
    }

    /**
     * @unused
     * @deprecated since version 2013.05.24 use Controller_Json::action_upload
     * 
     * Загрузка картинок
     * @return bool
     */
    public function action_image() {
        /* */
        if (empty($_FILES['file']) OR ! Upload::not_empty($_FILES['file']) OR ! Upload::valid($_FILES['file'])) {

            $return = array('error' => 'No upload');

        } elseif ($file = Model_File::image('file')) {

            $return = array('filelink' => $file->get_url());

        } else {

            $return = array('error' => 'Bad file');
        }

        $this->return_json($return);
    }

    /**
     * Тест писем
     */
    public function action_mail()
    {

        $to = 'zutest@mail.ru';
        // письма о заказах
        $o = ORM::factory('order')->where('status', '=', 'F')->where('delivery_type', '=', Model_Order::SHIP_COURIER)->order_by('id', 'DESC')->find(); // доставленный заказ
        Mail::htmlsend('order', array('o' => $o, 'od' => $o->data, 'got_status' => 1), $to, 'Заказ доставлен');
        $o->status = 'N';
        Mail::htmlsend('order', array('o' => $o, 'od' => $o->data), $to, 'Заказ принят');
        $o->status = 'D';
        Mail::htmlsend('order', array('o' => $o, 'od' => $o->data), $to, 'Заказ сформирован');
        $o->status = 'X';
        Mail::htmlsend('order', array('o' => $o, 'od' => $o->data), $to, 'Заказ отменён');

        // восстановление пароля
        Mail::htmlsend('forgot', array('u' => $this->user, 'time' => date('d.m.y H:59:59')), $to, 'Восстановление пароля');

        // запрос партнёра
        $p = ORM::factory('partner')->order_by('id', 'DESC')->find();
        Mail::htmlsend('partner', array('p' => $p), $to, 'Поступила заявка на сотрудничество');
        Mail::htmlsend('partner_answer', array('r' => $p), $to, 'Ответ на заявку на сотрудничество');

        // регистрация
        Mail::htmlsend('register', array('user' => $this->user, 'passwd' => $to), $to, 'Добро пожаловать!');

        // ответ на отзыв
        $c = ORM::factory('comment')->where('answer_by', '>', 0)->find();
        Mail::htmlsend('answer', array('i' => $c), $to, 'Re: '.$c->name);

        // обнуление пароля
        Mail::htmlsend('reset', array('user' => $this->user, 'passwd' => $to), $to, 'Для Вас был создан новый пароль пользователя');

        // уведомление о поставке
        $g = ORM::factory('good')->where('qty', '>', 0)->where('show', '>', 0)->find();
        Mail::htmlsend('warn', array('user' => $this->user, 'g' => $g), $to, 'Товар «'.$g->group_name.' '.$g->name.'» появился в наличии');

        // претензия
        $p = ORM::factory('return')->order_by('id', 'DESC')->find();
        Mail::htmlsend('return', array('r' => $p), $to, 'Претензия');
        Mail::htmlsend('return_answer', array('r' => $p), $to, 'Ответ на претензию');

    }
    
    public function action_spam_stat()
    {
        $domains_raw = DB::select(
                    DB::expr('SUBSTRING_INDEX(`email`,\'@\',-1) as `dmn`'), 
                    DB::expr('count(`id`) as `cnt`')
                )
                ->from('z_user')
                ->where('sub','=',1)
                ->group_by('dmn')
                ->order_by('cnt', 'DESC')
                ->execute()
                ->as_array();
        $total_mails = DB::select(DB::expr('count(`id`) as `cnt`'))
                ->from('z_user')
                ->where('sub','=',1)
                ->execute()
                ->get('cnt');
        $domains = array();
        foreach($domains_raw as $d) {
            switch($d['dmn']) {
		case 'mail.ru':
		case 'list.ru':
		case 'bk.ru':
		case 'inbox.ru':
			$domain = 'mail.ru';
			break;
		case 'yandex.ru':
		case 'ya.ru':
		case 'narod.ru':
			$domain = 'yandex.ru';
			break;
                default:
                    $domain = $d['dmn'];
            }
            if (empty($domains[$domain])) { $domains[$domain] = 0; }
            $domains[$domain] += $d['cnt'];
                //'p'     => round($d['cnt'] / $total_mails,2)
        }
        $total_domains = count($domains);
        $vars = array(
            'domains'       => $domains,
            'total_domains' => $total_domains,
            'total_mails'   => $total_mails
        );
        $this->layout->body = View::factory('smarty:admin/spam/stat', $vars)->render();
    }

    /**
     * Апдейт многих моделей
     * @param $update_arr
     */
    public function update_many($update_arr)
    {
        $cols = array('id');
        $on_duplicate = ' ON DUPLICATE KEY UPDATE ';

        foreach ($update_arr as $key => $item) {
            if (is_array($item)) {
                $cols[] = $key;
                $on_duplicate .= sprintf('`%s` = VALUES(`%s`), ', $key, $key);
            }
        }
        $query = DB::insert($this->model->table_name(), $cols);

        foreach($update_arr[$cols[1]] as $id => $val) {
            $values = array('id' => $id);
            foreach($cols as $col) {
                if ($col != 'id') {
                    $values[$col] = ( ! empty($update_arr[$col][$id])) ? $update_arr[$col][$id] : 0;
                }
            }
            $query->values($values);
        }

        $query .= substr($on_duplicate, 0, -2);

        DB::query(Database::INSERT, $query)->execute();
    }

    /**
     * Получение дерева страниц для админки
     * @return array
     */
    public function action_menu_list()
    {
        $array = ORM::factory('menu')->order_by('parent_id')->order_by('sort')->find_all()->as_array('id');

        $return = array();
        foreach($array as $item) {
            if ($item->parent_id > 0) {
                $return[$item->parent_id]['children'][$item->id] = $item->as_array();
            } else {
                $return[$item->id] = $item->as_array() + array('children' => '');
            }
        }

        return array('list' => $return);
    }

    /**
     * Список разделов каталога
     * @return array
     */
    public function action_section_list()
    {
        $s = ORM::factory('section');
        if ( ! ($v = $this->request->query('vitrina'))) $v = 'mladenec';
        return ['list' => $s->get_catalog(TRUE, $v)];
    }

    /**
     * @param \Model_Section $section
     * @return array
     */
	public function action_section_edit(\Model_Section $section)
    {
        $return = [];

        $misc = $this->request->post('misc');
        if ( ! empty($misc['hits'])) // хиты продаж для категорий уровня 0
        {
            DB::delete('z_hit')->where('section_id', '=', $section->id)->execute();

            $query = DB::insert('z_hit', ['section_id', 'good_id', 'sort']);

            $he = FALSE;
            foreach ($misc['hits'] as $k => $id) {
                if ( ! empty($id)) {
                    $he = TRUE;
                    $query->values([$section->id, $id, $k]);
                }
            }

            if ($he) $query->execute();
        }

        $sorts = $this->request->post('sort');
        if ( ! empty($sorts['filter'])) { // сортировки фильтров
            $upd = DB::insert('z_filter')->columns(['id', 'sort']);
            foreach($sorts['filter'] as $id => $sort) {
                $upd->values([$id, $sort]);
            }
            DB::query(Database::UPDATE, $upd.' ON DUPLICATE KEY UPDATE sort = VALUES(sort)')->execute();
        }
        if ( ! empty($sorts['value'])) { // сортировки значений
            $upd = DB::insert('z_filter_value')->columns(['id', 'sort']);
            foreach($sorts['value'] as $id => $sort) {
                $upd->values([$id, $sort]);
            }
            DB::query(Database::UPDATE, $upd.' ON DUPLICATE KEY UPDATE sort = VALUES(sort)')->execute();
        }

		$ds = ! empty($section->settings['orderByItems']) ? $section->settings['orderByItems'] : ['rating', 'name', 'price', 'new'];
		
		foreach ($ds as $f) $return['sortableOrderItems'][$f] = Kohana::message('sorts', $f);

		$br = $section->getSortedBrands();
		
		$return['sBrands'] = &$br;

        $sphinx = new Sphinx('section', $section->id, FALSE);
        $sphinx->clear_stats_cache(); // нужно на слуяай смены сортировки
        $return['subs'] = $subs = $sphinx->stats();

        $defaultGoodTabs = ['Полное описание', 'Отзывы'];
		$return['defaultGoodTabs'] = $defaultGoodTabs;
		$return['goodTabs'] = ! empty($section->settings['goodTabs']) ? $section->settings['goodTabs'] : $defaultGoodTabs;
		
		return $return;
	}

    /**
     * Список групп
     */
    public function action_group_list()
    {

        $return = array();

        $query = $this->model;

        if (($section_id = $this->request->query('section_id')) != "") {
            $query->where('section_id', '=', $section_id);
        }

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list'] = $query->order_by('id', 'desc')->offset($pager->offset)->limit($pager->per_page)->find_all();
        $return['sections'] = Model_Section::get_catalog(TRUE);

        return $return;
        
    }

    /**
     * Список фильтров
     * @return array
     */
    public function action_filter_list()
    {
        $return = array();

        $query = $this->model;

        if (($section_id = $this->request->query('section_id')) != "") {
            $query->where('section_id', '=', $section_id);
        }

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list'] = $query->order_by('sort', 'desc')->offset($pager->offset)->limit($pager->per_page)->find_all();

        return $return;
    }

    /**
     * Список значений фильтров
     * @return array
     */
    public function action_filter_value_list()
    {
        $return = array();

        $query = $this->model;

        if (($section_id = $this->request->query('filter_id')) != "") {
            $query->where('filter_id', '=', $section_id);
        }

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list'] = $query->order_by('filter_id', 'DESC')->order_by('sort')->offset($pager->offset)->limit($pager->per_page)->find_all();

        return $return;
    }

    /**
     * Список сертификатов
     */
    public function action_sert_list()
    {
        $return = array();
        $query = ORM::factory('sert')->distinct('id');
        
        $section_id = $this->request->query('section_id');
        $brand_id = $this->request->query('brand_id');
        $group_id   = $this->request->query('sert_group_id');
        
        switch ($this->request->query('active')) {
            case 'active':
                $query->where('expires', '>=', date('Y-m-d'));
                break;
            case 'expired':
                $query->where('expires', '<',  date('Y-m-d'));
                break;        
        } 
        
        
        if ( ! empty($section_id)) {
            
            $query->join(array('z_sert_rel', 'sr'))->on('sert.id', '=', 'sr.sert_id')
                    ->where('sr.section_id', '=', $section_id);
        }
        
        if ( ! empty($brand_id)) {
            
            $query->join(array('z_sert_rel', 'srb'))->on('sert.id', '=', 'srb.sert_id')
                    ->where('srb.brand_id', '=', $brand_id);
        }
        
        if ( ! empty($group_id)) {
            
            $query->join(array('z_sert_rel', 'srg'))->on('sert.id', '=', 'srg.sert_id')
                    ->where('srg.group_id', '=', $group_id);
        }
        
        if (($this->request->query('order_by')) == "expires") {
            
            $query->order_by('expires','asc');
        }

        $top_sections = ORM::factory('section')->where('parent_id','=','0')->find_all();
        
        $sections = ORM::factory('section')->distinct('id')
                ->join(array('z_sert_rel','sr'))
                    ->on('sr.section_id', '=','section.id')
                        ->find_all();
        
        $brands = ORM::factory('brand')->distinct('id')
                ->join(array('z_sert_rel','sr'))
                    ->on('sr.brand_id', '=','brand.id')
                        ->find_all();
        
        $query->reset(FALSE);
            
        $return['pager']        = $pager = new Pager($query->count_all(), 50);
        $return['list']         = $query->order_by('id', 'desc')->offset($pager->offset)->limit($pager->per_page)->find_all();
        $return['sert_groups']  = ORM::factory('sert_group')->find_all();
        $return['sections']     = $sections;
        $return['brands']       = $brands;

        return $return;
        
    }
    
    /**
     * Список товаров - извратный обработчик
     * @return array
     */
    function action_good_list()
    {
        $return = array();

        $return['brands'] = ORM::factory('brand')->where('active','=',1)->order_by('name')->find_all();
        $return['sections'] = Model_Section::get_catalog(TRUE);

        $query = $this->model->with('group')->with('img')->with('prop');

        $code = $this->request->query('code');
        $code1c = $this->request->query('code1c');

        if ( ! empty($code) OR ! empty($code1c))
        {
            if ( ! empty($code)) 
            {
                $query->where('good.code', 'LIKE', '%' . $code . '%');
            }
            elseif( ! empty($code1c))
            {
                $query->where('good.code1c', 'LIKE', '%' . $code1c . '%');
            }
        } 
        else 
        {
            if ($name = $this->request->query('name'))
            {
                $query->and_where_open();
                    $query->where('good.name', 'LIKE', '%'.$name.'%');
                    $query->or_where('good.group_name', 'LIKE', '%'.$name.'%');
                $query->and_where_close();
            }
            if ($id1c = $this->request->query('id1c'))
            {
                $query->where('good.id1c', '=', $id1c);
            }
            if ($brand_id = $this->request->query('brand_id'))
            {
                $query->where('good.brand_id', '=', $brand_id);
            }
            if ($section_id = $this->request->query('section_id'))
            {
                $query->where('good.section_id', '=', $section_id);
            }
            if (($show = $this->request->query('show')) != "")
            {
                $query->where('good.show', $show == 0 ? '=' : '!=', 0);
            }
            if (($active = $this->request->query('active')) != "")
            {
                $query->where('good.active', $active == 0 ? '=' : '!=', 0);
            }
            
            switch ($this->request->query('zombie'))
            {
                case 'show':
                    $query->where('good.zombie', '=', 1);
                    break;
                case 'all': // Надо ничего не делать, т.е. проигнорировать
                    break;
                default:
                    $query->where('good.zombie', '=', 0);

            }
            
            if (($ozon = $this->request->query('ozon')) != "")
            {
                $query->where('prop.to_ozon', $ozon == 0 ? '=' : '>', 0);
            }
            if (($old_price = $this->request->query('old_price')) != "")
            {
                $query->where('good.old_price', $old_price == 0 ? '=' : '>', 0);
            }

            if (($_present = $this->request->query('_present')) != "")
            {
                if (0 == $_present)
                {
                    $query->where('good.group_id', 'NOT IN', array(32799,32801,30283,30284));
                }
                else
                {
                    $query->where('good.group_id', 'IN', array(32799,32801,30283,30284));
                }
            }
            if (($big = $this->request->query('big')) != "")
            {
                $query->where('good.big', $big == 0 ? '=' : '>', 0);
            }
            if (($move = $this->request->query('move')) != "")
            {
                $query->where('good.move', $move == 0 ? '=' : '>', 0);
            }
            if (($wiki_cat = $this->request->query('wiki_cat')) != "")
            {
                $query->where('good.wiki_cat_id', $wiki_cat == 0 ? '>' : '=',  0);
            }
            if (($google_cat = $this->request->query('google_cat')) != "")
            {
                $query->where('good.google_cat_id', $google_cat == 0 ? '>' : '=',  0);
            }


            $flags = array('_new_item', 'superprice', '_modify_item',  '_desc',  '_optim',  '_graf',  '_full_graf',  '_supervisor', 'cpa_model');
            
            $has_flags = array();
            
            foreach($flags as $f)
            {
                if (($val = $this->request->query($f)) != "")
                {
                    $has_flags[$f] = $val;
                }
            }
            if ( ! empty($has_flags))
            {
                foreach($has_flags as $f => $val)
                {
                    $query->where($f, $val == 0 ? '=' : '!=', 0);
                }
            }
        }
        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $o = $this->request->query('orderBy');
        if (empty($o)) $o = 'name';
        $return['list'] = $query->order_by($o, 'desc')->order_by('popularity', 'desc')->offset($pager->offset)->limit($pager->per_page)->find_all();

        return $return;
    }

    /**
     * Список отзывов - с поиском
     * @return array
     */
    function action_good_review_list()
    {
        $return = array();

        $query = $this->model;

        $from    = $this->read_date($this->request->query('from'));
        $to      = $this->read_date($this->request->query('to'));
        $user_id = $this->request->query('user_id');
        
        if ( ! empty($from)) {
            $return['from'] = $from;
            $query->where('time', '>=', DB::expr("UNIX_TIMESTAMP('$from')"));
        }
        if ( ! empty($to)) {
            $return['to'] = $to;
            $query->where('time', '<=', DB::expr("UNIX_TIMESTAMP('$to')"));
        }
        if ( ! empty($user_id)) {
            $return['user_id'] = $user_id;
            $query->where('user_id', '=', $user_id);
        }
        if (($active = $this->request->query('active')) != "") {
            $query->where('active', $active == 0 ? '=' : '!=', 0);
        }
        if (($bad = $this->request->query('bad')) != "") {
            $query->where('hide', $bad == 0 ? '=' : '!=', 0);
        }

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list'] = $query
            ->order_by('hide', 'asc')
            ->order_by('id', 'desc')
//            ->order_by('active', 'asc')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all();

        return $return;
    }
    
    /**
     * Список тем отзывов - с поиском
     * @return array
    */
    function action_comment_theme_list()
    {
        $return = [];
        $return['count'] = $this->model->count_all();

		$this->breadcrumbs = array(Route::url('admin_list', array('model' => 'comment_theme')) => 'Отзывы о сайте');

        $return['counts'] = DB::select(DB::expr('COUNT(*) as c'), 'internal_rating')
            ->from('z_comment_theme')
            ->group_by('internal_rating')
            ->execute()
            ->as_array('internal_rating', 'c');

		$return['fields'] = [
			'id' => '#',
			'date' => 'дата',
			'active' => 'активность',
			'email_sent' => 'ответ',
			'user_name' => 'автор',
			'to' => 'кому',
			'name' => 'название',
			'internal_rating' => 'рейтинг'
		];
		
        $orderField = $this->request->query("order");
		if (empty($orderField)) $orderField = 'id';

		$desc = $this->request->query("desc");
		if (empty($desc)) $desc = 'desc';
		
		$return['desc'] = $desc;
		$return['sort'] = $orderField;

		$range_rating = $this->request->query('rating_range');
        $user_id = $this->request->query('user_id');
        
		$query = $this->model
            ->join('z_comment')
                ->on('z_comment.theme_id', '=', 'comment_theme.id')
			->join('z_comment_answer', 'LEFT')
                ->on('z_comment_answer.q_id', '=', 'z_comment.id')
			->where('comment_theme.name', '!=', '');

		$q = array();
		if ( ! empty($range_rating)) {
			
			list($range_min, $range_max ) = explode('-', $range_rating );
			
			$range_min = (int)$range_min;
			$range_max = (int)$range_max;
			
            $query->where('comment_theme.internal_rating', '>=', $range_min);
            $query->where('comment_theme.internal_rating', '<=', $range_max);
		
			$q['rating_range'] = $range_rating;
		} else {
			$range_min = 0;
			$range_max = 5;
		}
		
		$return['rating_range'] = array(
			'min' => $range_min,
			'max' => $range_max
		);

        $return['date_from'] = $date_from = $this->request->query("date_from");
		if ( ! empty($date_from)) {
            $query->where('z_comment.date', '>=', $date_from);
			$q['date_from'] = $date_from;
		}

        $return['date_to'] = $date_to = $this->request->query("date_to");
		if ( ! empty($date_to)){
            $query->where('z_comment.date', '<=', $date_to);
			$q['date_to'] = $date_to;
		}

        if ( ! empty($user_id)) {
            $return['user_id'] = $user_id;
            $query->where('comment_theme.user_id', '=', $user_id);
			$q['user_id'] = $user_id;
        }
        if (($answered = $this->request->query('answered')) != '') {
            $query->where('z_comment_answer.email_sent', $answered == 0 ? '=' : '!=', 0);
			$q['answered'] = $answered;
        }
        if (($active = $this->request->query('active')) != '') {
            $query->where('comment_theme.active', $active == 0 ? '=' : '!=', 0);
			$q['active'] = $active;
        }

		$return['filterquery'] = http_build_query($q);

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);

        $return['list'] = $query
            ->select('comment_theme.id', 'z_comment.date', 'z_comment_answer.email_sent')
            ->order_by($orderField, $desc)
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->group_by('comment_theme.id')
            ->find_all();
		
        return $return;
    }
	
    /**
     * 
     * @param Model_Comment_Theme $theme
     * @return array
     */
    function action_comment_theme_edit($theme)
    {

		$list_url = Route::url('admin_list', array('model' => 'comment_theme'));
		
		$this->breadcrumbs = array(
			 $list_url => 'Отзывы о сайте'
		);
		
		return array("list_url" => $list_url, 'data' => $theme->getData(), 'by' => Model_Comment_Answer::$answer_by);
    }
	
    function action_slider_banner_list()
    {
        $query = $this->model->reset(false);
        
        if (($name   = $this->request->query('name'))   != "")  $query->where('name',   'LIKE', '%' . $name . '%');
        if (($sliderId = $this->request->query('slider_id'))   > 0)  $query->where('slider_id',   '=', $sliderId);
        if (($active = $this->request->query('active')) != "")  $query->where('active', '=',    $active);

		$query->order_by('order', 'ASC');
        
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list']  = $query->offset($pager->offset)->limit($pager->per_page)->find_all();

		return $return;
	}
    
    function action_brand_list()
    {
		
        $query = $this->model;
		
        if (($name = $this->request->query('name')) != "") {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }
		
		$query->where('name', 'like', '%' . $name . '%')->reset(false);
		
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list'] = $query->offset($pager->offset)->limit($pager->per_page)->find_all();

		return $return;
	}
	
    function action_stat_list()
    {
		
        $query = $this->model;

		if( $this->request->query("update") ){
			
			Model_Stat::updateStat();
			$this->request->redirect($this->request->query("url"));
		}
		
		$from = $this->request->query('from');
		if( empty( $from ) )
			$from = date('Y-m-d', time() - 60*60*24*30);
		else
			$from = date('Y-m-d', strtotime($from)+60*60*24*30);
		
		$to = $this->request->query('to');
		
		if( empty( $to ) )
			$to = date('Y-m-d', time());
		else
			$to = date('Y-m-d', strtotime($to)+60*60*24*30);
		
		$query->where('sdate', '>=', $from);
		$query->where('sdate', '<', $to);
		
        $return['list'] = $query->find_all();
		$return['from'] = explode( '-', $from );
		$return['to'] = explode( '-', $to );
		
		return $return;
	}
	
    function action_stat_monthly_list(){
		
        $query = $this->model;

		$months = array(
			12 => 'последний год',
			24 => 'последние два года',
			36 => 'последние три года',
			200 => 'за все время',
		);

		$limit = (int)$this->request->query('limit');
		if( empty( $limit ) ){
			$limit = 12;
		}
		
		$to = $this->request->query('to');
		if( empty( $to ) ){
			$to = date('Y-m-01');
		}

		$from = $this->request->query('from');
		if( empty( $from ) ){
			$from = date('Y-m-01', time() - 31536000 );
		}
		
        $return['list'] = $query->where('sdate', '>=', $from)->where('sdate', '<=', $to)->find_all()->as_array();

		$return['from'] = $from;
		$return['to'] = $to;
		$return['months'] = $months;
		$return['limit'] = $limit;
		$startmonth = $return['list'][count($return['list'])-1]->sdate;
		$return['monthp'] = explode( '-', $startmonth);
		
		$return['list'] = array_reverse($return['list']);
		
		return $return;
	}
	
    /**
     * Список претензий - с поиском
     * @return array
    */
    function action_return_list()
    {
        $return = array();
        $query = $this->model;

        $user_id = $this->request->query('user_id');
        
        if ( ! empty($user_id)) {
            $return[] = $user_id;
            $query->where('user_id', '=', $user_id);
        }
        if (($answered = $this->request->query('answered')) != "") {
            $query->where('answer_sent', $answered == 0 ? '=' : '!=', 0);
        }
        if (($fixed = $this->request->query('fixed')) != "") {
            $query->where('fixed', $fixed == 0 ? '=' : '!=', 0);
        }

        $query->reset(FALSE);
        
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list']  = $query->order_by('id', 'desc')->offset($pager->offset)->limit($pager->per_page)->find_all();
        
        return $return;
    }

    /**
     * Список заказов
     * @return array
     */
    function action_order_list()
    {
        $return = array();

        //$query = $this->model->join('z_order_data')->on('order.id','=','z_order_data.id');
        $query = $this->model->with('data');

        $query->reset(false);
		
        if ($order_id = $this->request->query('order_id')) {
            $query->where('order.id', '=' , $order_id);
        }
        if ($user_id = $this->request->query('user_id')) {
            $query->where('user_id', '=' , $user_id);
        }
        if ($status = $this->request->query('status')) {
            $query->where('status', '=' , $status);
        }
        if ($pay_type = $this->request->query('pay_type')) {
            $query->where('pay_type', '=' , $pay_type);
        }
        if ($name = $this->request->query('name')) {
            $query->where('data.name', 'LIKE', $name.'%');
        }
        if ($email = $this->request->query('email')) {
            $query->where('data.email', 'LIKE', $email.'%');
        }
        if ($cname = $this->request->query('coupon_name')){
            $query->with('coupon')
                ->where('coupon.name', 'LIKE', $cname.'%');
        }
        if ($this->request->query('coupon')) {
            $query->where('coupon_id', '>', 0);
        }

        $from = $this->request->query('from');
        if ( ! empty($from['date'])) {
            $query->where('sent', '>=' , $from['date'].' '.$from['time']);
        }

        $to = $this->request->query('to');
        if ( ! empty($to['date'])) {
            $query->where('sent', '<=' , $to['date'].' '.$to['time']);
        }

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $query->reset(FALSE);
        $return['list'] = $query->order_by('order.id', 'desc')->offset($pager->offset)->limit($pager->per_page)->find_all();

		$countAll = $query->where('status', '!=', 'C')->count_all();
		$return['oformlennikh'] = $countAll;
        $return['from'] = $from;
        $return['to'] = $to;
		
        return $return;
    }

    /**
     * Проблемный безнал
     * @return array
     */
    function action_order_card()
    {
        $return = array();

        //$query = $this->model->join('z_order_data')->on('order.id','=','z_order_data.id');
        $query = ORM::factory('order')->with('data')->with('card')
            ->where('order.id', '>', '500000')
            ->where('pay_type', '=', Model_Order::PAY_CARD)
            ->where('order.status', '=', 'N')
            ->where('order.call_card', '=', '0')
            ->and_where_open()
                ->where('card.status', 'NOT IN', [Model_Payment::STATUS_Authorized, Model_Payment::STATUS_ChargeApproved])
                ->or_where('card.status', 'IS', DB::expr('NULL'))
            ->where_close()
        ;

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $query->reset(FALSE);
        $return['list'] = $query->order_by('order.id')->offset($pager->offset)->limit($pager->per_page)->find_all();

        $this->layout->body = View::factory('smarty:admin/order/card', $return)->render();;
    }

    /*
     * Экспорт списка заказов в Excel
     */
    function action_order_excel()
    {
        $content = '';
        
        $this->model = ORM::factory('order');
        $query = $this->model->with('data');
        $query->reset(FALSE);

        if ($order_id = $this->request->query('order_id')) {
            $query->where('order.id', '=', $order_id);
        }
        if ($user_id = $this->request->query('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
        if ($status = $this->request->query('status')) {
            $query->where('status', '=', $status);
        }
        if ($name = $this->request->query('name')) {
            $query->where('data.name', 'LIKE', $name . '%');
        }
        if ($email = $this->request->query('email')) {
            $query->where('data.email', 'LIKE', $email . '%');
        }

        if ($this->request->query('coupon')) {
            $query->where('coupon_id', '>', 0);
        }
        $from = $this->request->query('from');
        if ( ! empty($from['date'])) {
            $query->where('sent', '>=' , $from['date'].' '.$from['time']);
        }

        $to = $this->request->query('to');
        if ( ! empty($to['date'])) {
            $query->where('sent', '<=' , $to['date'].' '.$to['time']);
        }

        $orders = $query->find_all();

        Txt::as_excel([
            'id'        => '№ заказа',
            'price'     => 'Сумма',
            'user_id'   => '№ клиента',
            'sent'      => 'Отправлен',
            'num'       => 'Номер',
            'coupon_id' => 'Купон',
            'status'    => 'Статус',
            'source'    => 'Источник',
        ], $orders, 'orders', [
            'status' => function($row) { return $row->status(); },
            'source' => function($row) { $return = Txt::parse_source($row->data->source); return $return['type'];},
            'num' => function($row) { return $row->data->num;},
        ]);

        exit;
    }

    /**
     * Список опросов, может возращать также результаты опроса
     * @return array
     * @throws Kohana_Exception
     */
    function action_poll_list()
    {
        $return = array();
        
        $query = $this->model;
        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list'] = $query->order_by('id', 'desc')->offset($pager->offset)->limit($pager->per_page)->find_all();
        
        $current_poll = FALSE;
        $current_poll_id = $this->request->post('current_poll_id');
        
        if ( ! empty($current_poll_id)) {
            $current_poll = ORM::factory('poll', $current_poll_id);
        } elseif ( ! empty($return['list'][0])) {
            $current_poll = $return['list'][0];
        }
        
        if (($current_poll instanceof Model_Poll) AND $current_poll->loaded()) {

            $questions = $current_poll->questions->order_by('sort', 'ASC')->find_all()->as_array('id'); // все вопросы
            $variants = []; // Запросим позже из вопросов

            if ($this->request->post('excel')) { // получить ответы в excel

                include_once(APPPATH.'classes/PHPExcel.php');
                $excel = new PHPExcel();
                $sheet = $excel->getActiveSheet();
                $i = 0;
                $sheet->setCellValueByColumnAndRow($i++, 1, 'Пользователь');

                $map = []; // запишем связь ответ => ячейка
                foreach($questions as $q) { // заполняем первую строку - вопросы и вторую - варианты ответов
                    $variants[$q->id] = $q->variants->order_by('sort', 'ASC')->find_all()->as_array('id'); // варианты ответов
                    $sheet->setCellValueByColumnAndRow($i, 1, $q->name);

                    if ($q->type == Model_Poll_Question::TYPE_MULTI || $q->type == Model_Poll_Question::TYPE_PRIORITY) { // надо склеить колонку по числу вариантов
                        $count = count($variants[$q->id]);
                        $j = $i;
                        foreach($variants[$q->id] as $v) { // заполнить вторую строку вариантами
                            $map[$q->id][$v->id] = $j;
                            $sheet->setCellValueByColumnAndRow($j++, 2, $v->name);
                        }
                        $i += $count;
                    } else {
                        $map[$q->id] = $i;
                        $i++;
                    }
                }

                $votes = DB::select(DB::expr('v.*'))
                    ->from(['z_poll_vote', 'v'])
                    ->join(['z_poll_question', 'q'])
                        ->on('q.id', '=', 'v.question_id')
                    ->where('v.poll_id', '=', $current_poll->id)
                    ->order_by('v.user_id', 'ASC')
                    ->order_by('q.sort', 'ASC')
                    ->execute()
                    ->as_array();

                $user_id = 0;
                $row = 2;
                foreach($votes as $v) { // заполняем ответы по пользователям
                    if ($v['user_id'] != $user_id) {
                        $row++;
                        $user_id = $v['user_id'];
                        $sheet->setCellValueByColumnAndRow(0, $row, $v['user_id']);
                    }
                    if (in_array($questions[$v['question_id']]->type, [Model_Poll_Question::TYPE_MULTI, Model_Poll_Question::TYPE_PRIORITY])) { // в ответе галочки
                        if ($questions[$v['question_id']]->type == Model_Poll_Question::TYPE_MULTI) {
                            $value = empty($v['var_text']) ? 'да' : $v['var_text'];
                        } else {
                            $value = $v['value'];
                        }
                        $sheet->setCellValueByColumnAndRow($map[$v['question_id']][$v['var_id']], $row, $value);
                    } else {
                        $value = $v['var_id'] == 0 ? $v['var_text'] : (empty($v['value']) ? $variants[$v['question_id']][$v['var_id']]->name : $v['value']);
                        $sheet->setCellValueByColumnAndRow($map[$v['question_id']], $row, $value);
                    }
                }

                $fname = 'poll_result'.$current_poll->id.'.xlsx';
                $io = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $io->save('/tmp/'.$fname);

                header('Content-Description: File Transfer');
                header('Content-Type: application/excel');
                header('Content-Disposition: attachment; filename='.$fname);
                echo file_get_contents('/tmp/'.$fname);
                exit;

            } else { // вывести ответы на экран

                $votes_begin = DB::select(DB::expr('MIN(`ts`) as `min_ts`'))
                    ->from('z_poll_vote')
                    ->where('poll_id', '=', $current_poll->id)
                    ->execute()
                    ->get('min_ts');

                $min_year = date('Y', strtotime($votes_begin));
                $min_month = date('m', strtotime($votes_begin));
                $max_year = date('Y');
                $max_month = date('m');
                $votes = array();
                $months = array();

                foreach ($questions as $qstn) {
                    $variants[$qstn->id] = $qstn->variants->order_by('sort', 'ASC')->find_all()->as_array();
                    foreach ($variants[$qstn->id] as $var) {
                        $var_votes = $var->get_votes_by_months();
                        // Заполняем отсутствующие значения по месяцам
                        for ($i = $min_year; $i <= $max_year; $i++) {
                            // В промежуточных годах заполняем все месяцы
                            if ($i > $min_year) $mm = 1; else $mm = $min_month; // в первый год считаем С указанного месяца
                            if ($i < $max_year) $mam = 12; else $mam = $max_month; // в последний год считаем ДО указанного месяца

                            for ($y = $mm; ($y <= $mam); $y++) {
                                $month_str = $i . ' ' . str_pad($y, 2, '0');
                                $time_key = $i . str_pad($y, 2, '0');
                                if ( ! empty($var_votes[$month_str])) {
                                    $votes[$qstn->id][$var->id][$time_key] = $var_votes[$month_str];
                                } else {
                                    $votes[$qstn->id][$var->id][$time_key]['cnt'] = '&mdash;';
                                }
                                // Попутно заполняем массив месяцев
                                if (empty($months[$time_key])) {
                                    $months[$time_key] = Txt::ru_month($y) . ' ' . $i;
                                }
                            }
                        }
                    }
                    if (isset($month_str)) unset($month_str);
                    if (isset($var)) unset($var);
                    if (isset($i)) unset($i);
                    if (isset($y)) unset($y);
                }

                $return['current_poll_id'] = $current_poll->id;
                $return['current_poll'] = $current_poll;
                $return['questions'] = $questions;
                $return['variants'] = $variants;
                $return['votes'] = $votes;
                $return['variant_months'] = $months;
            }
            
        } else {
            $return['current_poll_id']  = FALSE;
            $return['current_poll']     = FALSE;
        }
        return $return;
    }

    /**
     * список пользователей - получение (общее с выдачей в ексель или в html
     */
    private function _user_list()
    {
        $query = $this->model;

        if ($id = $this->request->query('id')) {
            $query->where('id', '=', $id);
        }
        if ($name = $this->request->query('name')) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
        if ($email = $this->request->query('email')) {
            $query->where('email', 'LIKE', $email.'%');
        }
        if ($phone = $this->request->query('phone')) {

            $first_digit = substr($phone, 0,1);
            if ($first_digit != 8 AND $first_digit != 7) $phone = '+7' . $phone;
            if (0 !== strpos($phone,'+'))  $phone = '+' . $phone;
            if (0 !== strpos($phone,'+7')) $phone = str_replace ('+8', '+7', $phone);

            $query->where_open()
                ->where('phone',     'LIKE', $phone.'%')
                ->or_where('phone2', 'LIKE', $phone.'%')
                ->where_close();
        }
        if (($sub = $this->request->query('sub')) != "") {
            $query->where('sub', $sub == 0 ? '=' : '!=', 0);
        }
        if (($lk = $this->request->query('lk')) != "") {
            $query->where('status_id', '=', $lk);
        }

        if ($admin = $this->request->query('admin')) {
            $query->distinct('id')->join('z_user_admin')->on('z_user_admin.user_id','=','user.id');

            $access_res = DB::select()->from('z_user_admin')->execute()->as_array();
            $return['access'] = array();
            foreach ($access_res as $a_res) {
                $return['access'][$a_res['user_id']][$a_res['module']] = $a_res['module'];
            }
        }

        if (($childs = $this->request->query('childs')) != "") {
            if ($childs == 1) {
                $query->join('z_user_child')
                    ->on('z_user_child.user_id', '=', 'user.id')
                    ->group_by('user.id');
            } else {
                $query->join('z_user_child','left')
                    ->on('z_user_child.user_id', '=', 'user.id')
                    ->where('z_user_child.user_id', '=', NULL)
                    ->group_by('user.id');
            }
        }

        $from = $this->request->query('from');
        if ( ! empty($from['date'])) {
            $query->where('created', '>=' , strtotime($from['date'].' '.$from['time']));
        }

        $to = $this->request->query('to');
        if ( ! empty($to['date'])) {
            $query->where('created', '<=' , strtotime($to['date'].' '.$to['time']));
        }

        $query->reset(FALSE);
        return $query;
    }

    /**
     * Список пользователей - извратный обработчик
     * @return array
     */
    function action_user_list()
    {
        $return = array();

        if ($id = $this->request->post('reset_password')) {
            $new_pass = $this->request->post('password');
            $user = Model_User::reset_password($id, $new_pass);
            if ($user instanceof Model_User) {
                $this->layout->errors = ['password' => 'Пароль пользователя  '.$user->email.' изменён '.($new_pass ? $new_pass : '')];
            } else {
                $this->layout->errors = $user;
            }
        }

        $query = $this->_user_list();
        $return['pager'] = $pager = new Pager($query->find_all()->count(), 50);

        $list = $query
            ->order_by('user.id', 'desc')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all()
            ->as_array('id');

        if ( ! empty($list)) {
            $kids = $orders = [];
            $user_id = array_keys($list);
            $childs = ORM::factory('user_child')
                ->where('user_id', 'IN', $user_id)
                ->order_by('user_id')
                ->order_by('birth')
                ->find_all()
                ->as_array('id');

            if ($childs) {
                foreach ($childs as $child) {
                    $kids[$child->user_id][$child->id] = $child;
                }
                $return['kids'] = $kids;
            }
            foreach ($list as $row) {
                if ($row->last_order) $orders[] = $row->last_order;
            }
            if ($orders) {
                $orders = ORM::factory('order')
                    ->where('id', 'IN', $orders)
                    ->find_all()
                    ->as_array('id');

                $return['orders'] = $orders;
            }
        }

        $return['list'] = $list;

        return $return;
    }
    
    /*
     * Экспорт списка пользователей в Excel (csv для GR)
     * только подписчики, кто был на сайте
     */
    function action_user_excel()
    {
        $this->model = ORM::factory('user')
            ->with('segment');
        $query = $this->_user_list()
            ->where('user.last_visit', '>', 0)
            ->where('user.sub', '=', 1)
            ->limit(10000)
            ;
        $users = $query->find_all();

        Txt::as_csv([
            'id' => 'ID',
            'email' => 'Email' ,
            //'phone' => 'Телефон',
            'name' => 'name' ,
            /*/'last_name' => 'Фамилия',
            //'second_name' => 'Отчество',
            //'sum' => 'Сумма заказов, руб.',
            //'qty' => 'Количество заказов',
            //'avg_check' => 'Средний чек',
            'last_order' => 'Дата последнего заказа',
            'lk'    => 'Любимый',
            'sub'   => 'Рассылка',
            'created' =>  'Создан',
            'pregnant' => 'Беременность',
            'pregnant_week' => 'Срок (недель)',
            'kids' => 'Дети',
            */
            'last_visit' => 'last_visit',
            'md5' => 'md5',
            'register_date' => 'register_date',
            'arpu' => 'arpu', 
            'last_order_sum' => 'last_order_sum',
            'last_order' => 'last_order',
            'orders_count' => 'orders_count', 
            'orders_sum' => 'orders_sum', 
            'sum_big' => 'sum_big', 
            'sum_diaper' => 'sum_diaper', 
            'sum_eat' => 'sum_eat', 
            'sum_toy' => 'sum_toy', 
            'sum_care' => 'sum_care', 
            'sum_dress' => 'sum_dress', 
            'buy_big' => 'buy_big', 
            'buy_diaper' => 'buy_diaper', 
            'buy_eat' => 'buy_eat', 
            'buy_toy' => 'buy_toy', 
            'buy_care' => 'buy_care', 
            'sert_use' => 'sert_use', 
            'buy_dress' => 'buy_dress', 
            'childs' => 'childs',
            'child_birth_1' => 'child_birth_1',
            'child_birth_2' => 'child_birth_2',
            'child_birth_3' => 'child_birth_3',
            'child_birth_4' => 'child_birth_4',
            'child_birth_5' => 'child_birth_5',
            'child_birth_6' => 'child_birth_6',
            'has_boy' => 'has_boy', 
            'has_girl' => 'has_girl', 
            'child_birth_min' => 'child_birth_min', 
            'child_birth_max' => 'child_birth_max', 
            'pregnant_week' => 'pregnant_week',
            'is_pregnant' => 'is_pregnant',

        ], $users, 'users', [
            'avg_check' => function($row) { return $row->avg_check(); },
            'md5' => function($row) { return md5(Cookie::$salt . $row->email);},
            'register_date' => function($row) { return date('Y-m-d', $row->created);},
            'lk' => function($row) { return $row->status_id ? 'Да' : ''; },
            'sub' => function($row) { return $row->sub ? 'Да' : ''; },
            'is_pregnant' => function($row) { return $row->pregnant ? 'Да' : 'Нет'; },
            'last_order' => function($row) { $order = ORM::factory('order', $row->last_order); return $order->loaded() ? $order->created : ''; },
            'pregnant_week' => function($row) { return $row->pregnant ? $row->get_pregnant_weeks() : ''; },

            'childs' => function($row) { return $row->segment->childs ? count(explode(',', $row->segment->childs)) : 0; },
            'child_birth_1' =>function($row) {
                if (empty($row->segment->childs)) return '';
                if ($births = explode(',', $row->segment->childs)) {
                    if ( ! empty($births[0])) return $births[0];
                }
                return '';
            },
            'child_birth_2' =>function($row) {
                if (empty($row->segment->childs)) return '';
                if ($births = explode(',', $row->segment->childs)) {
                    if ( ! empty($births[1])) return $births[1];
                }
                return '';
            },
            'child_birth_3' =>function($row) {
                if (empty($row->segment->childs)) return '';
                if ($births = explode(',', $row->segment->childs)) {
                    if ( ! empty($births[2])) return $births[2];
                }
                return '';
            },
            'child_birth_4' =>function($row) {
                if (empty($row->segment->childs)) return '';
                if ($births = explode(',', $row->segment->childs)) {
                    if ( ! empty($births[3])) return $births[3];
                }
                return '';
            },
            'child_birth_5' =>function($row) {
                if (empty($row->segment->childs)) return '';
                if ($births = explode(',', $row->segment->childs)) {
                    if ( ! empty($births[4])) return $births[4];
                }
                return '';
            },
            'child_birth_6' =>function($row) {
                if (empty($row->segment->childs)) return '';
                if ($births = explode(',', $row->segment->childs)) {
                    if ( ! empty($births[5])) return $births[5];
                }
                return '';
            },
            'user_id' => function($row) { return $row->segment->user_id; },
            'last_visit' => function($row) { return $row->segment->last_visit; },
            'arpu' =>function($row) { return $row->segment->arpu; },
            'last_order_sum' =>function($row) { return $row->segment->last_order_sum; },
            'orders_count' =>function($row) { return $row->segment->orders_count; },
            'orders_sum' =>function($row) { return $row->segment->orders_sum; },
            'sum_big' =>function($row) { return $row->segment->sum_big; },
            'sum_diaper' =>function($row) { return $row->segment->sum_diaper; },
            'sum_eat' =>function($row) { return $row->segment->sum_eat; },
            'sum_toy' =>function($row) { return $row->segment->sum_toy; },
            'sum_care' =>function($row) { return $row->segment->sum_care; },
            'sum_dress' =>function($row) { return $row->segment->sum_dress; },
            'buy_big' =>function($row) { return $row->segment->buy_big; },
            'buy_diaper' =>function($row) { return $row->segment->buy_diaper; },
            'buy_eat' =>function($row) { return $row->segment->buy_eat; },
            'buy_toy' =>function($row) { return $row->segment->buy_toy; },
            'buy_care' =>function($row) { return $row->segment->buy_care; },
            'sert_use' =>function($row) { return $row->segment->sert_use; },
            'buy_dress' =>function($row) { return $row->segment->buy_dress; },
            'has_boy' =>function($row) { return $row->segment->has_boy; },
            'has_girl' =>function($row) { return $row->segment->has_girl; },
            'child_birth_min' =>function($row) { return $row->segment->child_birth_min; },
            'child_birth_max' =>function($row) { return $row->segment->child_birth_max; },
        ]);
    }

    /**
     * 
     * @param Model_User $user
     * @return array
     */
    function action_user_edit($user)
    {
        
        $sessions = DB::select()
                ->from('z_session')
                ->where('user_id', '=', $user->id)
                ->execute()
                ->as_array('id');

        foreach ($sessions as &$s) {
            $s['hash'] = md5($s['id'].Cookie::$salt);
        }
        
        return array(
            'sessions' => $sessions,
            'orders'   => $user->orders       ->order_by('id', 'DESC')->limit(20)->find_all()->as_array(), // Заказы
            'address'  => $user->address(), //Адреса
            'comments' => ORM::factory('comment_theme')->getLast(20, 0, $user->id), // Отзывы о сайте
            'reviews'  => $user->good_reviews ->order_by('id', 'DESC')->limit(20)->find_all()->as_array(), // Отзывы о товаре
            'returns'  => $user->returns      ->order_by('id', 'DESC')->limit(20)->find_all()->as_array(),  // Претензии
            'childs'   => $user->kids->find_all()->as_array('id') // Дети
        );
    }
	
    public function action_tag_edit(\Model_Tag $tag)
    {
		$returner = [];
		
        if ($tag->in_google != 1) {
            $json = json_decode(file_get_contents('http://www.google.com/uds/GwebSearch?v=1.0&q="' . $tag->code . '"%20site:www.mladenec-shop.ru'), true);

            if ( ! empty($json['responseData'])) {
                $tag->in_google = empty($json['responseData']['results']) ? 1 : 2;
            } else {
                $tag->in_google = 3;
            }
        }

		$returner['google_code'] = $tag->in_google;
		
		parse_str($tag->query, $query);

        $filtersIds = [];
        foreach($query as $k => $v) {
            if (strpos($k, 'f') === 0) {
                $name = substr($k, 1);
                if ($name > 0) $filtersIds[(int)$name] = (int)$name;
            }
        }

		$notFound = [];
		if ( ! empty($filtersIds)) {
			$items = ORM::factory('filter')
                ->select('id')
                ->where('id', 'IN', $filtersIds)
                ->find_all()
                ->as_array('id');

			foreach($items as $id => $v) unset($filtersIds[$id]);
		}

		$returner['notFound'] = $notFound;
		
		return $returner;
	}
	
    /**
     * Список теговых страниц - с фильтрацией по категории и строке
     * @return array
     */
    function action_tag_list()
    {
        $return = array();

		$return['log_exists'] = is_file( APPPATH . "cache/tags_log.txt" );
		
		if (isset($_GET['log'])) {
		
			header('Content-Description: File Transfer');
			header('Content-Type: text/txt');
			header('Content-Disposition: attachment; filename=log.txt');
			echo file_get_contents(APPPATH . 'cache/tags_log.txt');
			exit;
		}
		
        if (isset($_FILES['excel']) && Upload::not_empty($_FILES['excel'])) { // parse Excel for data

            $total = $changed = $added = 0;
            include(APPPATH.'classes/PHPExcel/IOFactory.php');

            $excel = PHPExcel_IOFactory::load($_FILES['excel']['tmp_name']);
            $sheet = $excel->getActiveSheet();

            $map = array('url', 'code', 'title', 'description', 'keywords', 'name', 'anchor', 'text');
            $row = 2; // первый ряд пропускаем - там имена полей

            do {
                $t = $where = $link_b = $link_f = array();
                foreach($map as $n => $field_name) {
                    $t[$field_name] = $sheet->getCellByColumnAndRow($n, $row)->getValue();
                }
                $url = $t['url'];
                if ( ! empty($t['url']) && strpos($t['url'], '#!')) {
                    $params = substr($t['url'], strrpos($t['url'], '/') + 1);
                    list($section, $hash) = explode('#!', $params);
                    $tmp = substr($section, 0, -5);
                    $where[] = 'SECTION_ID=' . $tmp; // условие на категорию
                    $link_s = $tmp; // привязать эту категорию
                    $t['section_id'] = $tmp; // и в поле тоже запишем
                    $hash_params = array_filter(explode(';', $hash));

                    foreach ($hash_params as $item) {
                        switch ($item{0}) {
                            case 'b':
                                $tmp = substr($item, 2);
                                $link_b = explode('_', $tmp); // привязать эти бренды
                                $where[] = 'PROPERTY_BRAND=' . str_replace('_', '|', $tmp); // условие на бренд
                                break;
                            case 'f':
                                $tmp = substr($item, 1);
                                $link_f = array_merge_recursive($link_f, explode('_', $tmp));
                                $where[] = str_replace('_', '|', $tmp); // условие на фильтры
                                break;
                        }
                    }
                    $t['params'] = implode(',', $where);
                    unset($t['url']);

                    $t['code'] = substr($t['code'], strpos($t['code'], '/catalog') + 1);

                    $tag = ORM::factory('tag')->clear()->where('code', '=', $t['code'])->find();
                    if ( ! empty($tag->id)) {
                        $changed++;
                    } else {
                        $added++;
                    }
                    $tag->values($t);
                    $tag->save();

                   // добавим привязки к категории
                    $ins = DB::insert('z_tag_section')->columns(array('tag_id', 'section_id'))->values(array($tag->id, $link_s));
                    DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE ', $ins))->execute();

                    if ( ! empty($link_b)) { // к бренду
                        $ins = DB::insert('z_tag_brand')->columns(array('tag_id', 'brand_id'));
                        foreach($link_b as $b) $ins->values(array('tag_id' => $tag->id, 'brand_id' => $b)); // $b = ;
                        DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();
                    }

                    if ( ! empty($link_f)) { // к фильтрам
                        $ins = DB::insert('z_tag_filter_value')->columns(array('tag_id', 'filter_value_id'));
                        foreach($link_f as $f) {
                            $p = strpos($f, '=');
                            $ins->values(array('tag_id' => $tag->id, 'filter_value_id' => $p ?  substr($f, $p + 1) : $f));

                        }
                       DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();
                    }
                    $total++;
                }
                $row++;
            } while ( ! empty($url));

            $this->messages_add(['messages' => [sprintf('Обработано %d строк, %d страниц изменено, %d страниц добавлено', $total, $changed, $added)]]);
        }

        $query = $this->model->with('tree');

        if ($name = $this->request->query('name')) {
            $query->where_open();
                $query->where('tag.name', 'LIKE', '%'.$name.'%');
                $query->or_where('tag.code', 'LIKE', '%'.$name.'%');
            $query->where_close();

        }
        if ($tree_id = $this->request->query('tree_id')) {
            $query->where('tree_id', '=', $tree_id);
        }

        $empty = $this->request->query('empty');
        
        if ('0' === $empty) {
            $query->where('goods_count', '>', 0);
        } elseif($empty) {
            $query->where('goods_count', '=', 0);
        }
        
        $filter_not_exists = $this->request->query('filter_not_exists');
        
        if ( ! empty( $filter_not_exists ) ) {
            $query->where('filter_not_exists', '=', 1);
        } elseif($filter_not_exists === '0') {
            $query->where('filter_not_exists', '=', "0");
        }
		
        $checked = $this->request->query('checked');
        
        if ( ! empty( $checked ) ) {
            $query->where('checked', '=', 1);
        } elseif($checked === '0')  {
            $query->where('checked', '=', 0);
        }
        
        $not_redirected = $this->request->query('not_redirected');
        
        if ( ! empty($not_redirected)) {
            $query->where('tag.code', 'NOT IN', DB::expr('(SELECT url FROM tag_redirect WHERE to_id <> 0)'));
        } elseif ($not_redirected === '0') {
            $query->where('tag.code', 'IN', DB::expr('(SELECT url FROM tag_redirect WHERE to_id <> 0)'));
        }
		
        if ($section_id = $this->request->query('section_id')) {
            $query->join('z_tag_section')->on('tag.id','=','z_tag_section.tag_id')->where('z_tag_section.section_id', '=', $section_id);
        }
        
        if ($brand_id = $this->request->query('brand_id')) {
            $query->join('z_tag_brand')->on('tag.id','=','z_tag_brand.tag_id')->where('z_tag_brand.brand_id', '=', $brand_id);
        }
       
        $query->reset(FALSE);

        $return['brands'] = ORM::factory('brand')->distinct('id')
                ->join('z_tag_brand')->on('brand.id','=','z_tag_brand.brand_id')
                ->where('active','=',1)
                ->order_by('name','asc')->find_all()->as_array();

        $return['pager'] = $pager = new Pager($query->count_all(), 50);

        $return['list'] = $query->order_by('tag.goods_empty_ts', 'DESC')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all();

        return $return;
    }
    
    public function action_direct()
    {
        $vars = array();
        
        if (isset($_FILES['excel']) && Upload::not_empty($_FILES['excel'])) 
        { 
            $total = $changed = $added = 0;
            include(APPPATH.'classes/PHPExcel/IOFactory.php');

            $excel = PHPExcel_IOFactory::load($_FILES['excel']['tmp_name']);
            $sheet = $excel->getActiveSheet();
            
            $map = array(
                'number'=>0, 
                    'mo'            => 3,
                    'khimki'        => 4,
                    'schelkovo'     => 5,
                    'ubileiny'      => 6,
                    'pushkino'      => 7,
                    'mytischi'      => 8,
                    'moscow'        => 9,
                    'korolev'       => 10,
                    'ivanteevka'    => 11,
                'link'      =>16,
                'active'    =>21
                );
            
            $vars['size']   = $sheet->calculateWorksheetDataDimension();
            $vars['height'] = intval(preg_replace('/[^0-9]/', '', $vars['size']));
            $vars['result'] = array();
            
            for($row = 0; $row <= $vars['height']; $row++)
            {
                $link = $sheet->getCellByColumnAndRow($map['link'], $row)->getValue();
                
                if (FALSE === strpos($link, '/product/')) continue;
                
                $good_id_found = preg_match('/\\.(\d+)\\.html/', $link,$matches);
                
                $number = $sheet->getCellByColumnAndRow($map['number'], $row)->getValue();
                
                $vars['result'][$number]['number'] = $number;
                $vars['result'][$number]['mo']          = $sheet->getCellByColumnAndRow($map['mo'], $row)->getValue();
                $vars['result'][$number]['khimki']      = $sheet->getCellByColumnAndRow($map['khimki'], $row)->getValue();
                $vars['result'][$number]['schelkovo']   = $sheet->getCellByColumnAndRow($map['schelkovo'], $row)->getValue();
                $vars['result'][$number]['ubileiny']    = $sheet->getCellByColumnAndRow($map['ubileiny'], $row)->getValue();
                $vars['result'][$number]['pushkino']    = $sheet->getCellByColumnAndRow($map['pushkino'], $row)->getValue();
                $vars['result'][$number]['mytischi']    = $sheet->getCellByColumnAndRow($map['mytischi'], $row)->getValue();
                $vars['result'][$number]['moscow']      = $sheet->getCellByColumnAndRow($map['moscow'], $row)->getValue();
                $vars['result'][$number]['korolev']     = $sheet->getCellByColumnAndRow($map['korolev'], $row)->getValue();
                $vars['result'][$number]['ivanteevka']  = $sheet->getCellByColumnAndRow($map['ivanteevka'], $row)->getValue();
                $vars['result'][$number]['link']    = $link;
                $vars['result'][$number]['active']  = $sheet->getCellByColumnAndRow($map['active'], $row)->getValue();
                
                if($good_id_found)
                {
                    $vars['result'][$number]['good_id'] = $matches[1];
                    $vars['number_good'][$number] = $matches[1];
                }
            }
            
            if ( ! empty($vars['number_good']))
            {
                $goods = ORM::factory('good')->where('id','IN',$vars['number_good'])->find_all()->as_array('id');
                
                foreach ($vars['number_good'] as $num => $gid)
                {
                    if ( ! empty($goods[$gid]) AND $goods[$gid]->qty != 0 AND $goods[$gid]->active != 0 AND $goods[$gid]->show = 1)
                    {
                        $vars['result'][$num]['ok'] = TRUE;
                        //$sheet->setCellValueByColumnAndRow($map['active'], $pRow, $gid)
                    }
                    else
                    {
                        $vars['result'][$num]['ok'] = FALSE;
                    }
                    
                    if (
                            ($vars['result'][$num]['active'] == 'Активно' AND FALSE == $vars['result'][$num]['ok'])
                            OR
                            ($vars['result'][$num]['active'] != 'Активно' AND TRUE == $vars['result'][$num]['ok'])
                            )
                    {
                        $vars['result'][$num]['change'] = TRUE;
                    }
                }
            }
            
            $vars['uploaded'] = TRUE;
        }
        else
        {
            $vars['uploaded'] = FALSE;
        }
        
        $this->layout->body = View::factory('smarty:admin/direct/index', $vars)->render();
    }
    
    /**
     * Обработчик для баннеров
     * @return array
     */
    function action_ad_list()
    {
        $query = $this->model->reset(FALSE);
        $ad = $this->model->init();
        if ($code = $this->request->query('code')) {
            $query->where('code', '=', $code);
        }
        $return['pager'] = $pager = new Pager($query->count_all(), 20);
        $return['list'] = $query
            ->order_by('active', 'desc')
            ->order_by('from', 'desc')
            ->order_by('to', 'desc')
            ->order_by('id', 'desc')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all();
        $return['ad'] = $ad;

        return $return;
    }
    
    /**
     * Список акций
     * @return array
     */
    function action_action_list()
    {
        $return = array();
        
        $active         =      $this->request->query('active');
        $show           =      $this->request->query('show');
        $allowed        =      $this->request->query('allowed');
        $pr_inst        =      $this->request->query('presents_instock');
        $name           =      $this->request->query('name');
        $main           =      $this->request->query('main');
        $wow            =      $this->request->query('show_wow');
        $show_actions   =      $this->request->query('show_actions');
        $vitrina_active = trim($this->request->query('vitrina_active'));
        $vitrina_show   = trim($this->request->query('vitrina_show'));
        
        $query = ORM::factory('action')->reset(FALSE);
        
        if ('0' === $active  OR '1' === $active)  $query->where('active',           '=', $active);
        if ('0' === $show    OR '1' === $show)    $query->where('show',             '=', $show);
        if ('0' === $allowed OR '1' === $allowed) $query->where('allowed',          '=', $allowed);
        if ('0' === $pr_inst OR '1' === $pr_inst) {
            $query->where('presents_instock', '=', $pr_inst)
                ->where('type','IN', array( Model_Action::TYPE_GIFT_SUM, Model_Action::TYPE_GIFT_QTY));
        }
        
        if ( ! empty($name))            $query->where('name',    'LIKE', '%' . $name .'%');
        
        if ('1' === $main)              $query->where('main',         '=', 1); // на главной
        elseif ('0' === $main)          $query->where('main',         '=', 0);
        if ('1' === $wow)               $query->where('show_wow',     '=', 1); // в вау-акциях
        elseif ('0' === $wow)           $query->where('show_wow',     '=', 0);
        if ('1' === $show_actions)      $query->where('show_actions', '=', 1); // в общем списке акций
        elseif ('0' === $show_actions)  $query->where('show_actions', '=', 0);
        
        if ( ! empty($vitrina_active) AND 'ignore' != $vitrina_active) $query->where('vitrina_active', '=', $vitrina_active);
        if ( ! empty($vitrina_show)   AND 'ignore' != $vitrina_show)   $query->where('vitrina_show',   '=', $vitrina_show);
        
        $from = $this->request->query('from');
        if (is_array($from) AND ! (empty($from['Date_Day']) AND empty($from['Date_Month']) AND empty($from['Date_Year']))) {
            
            if (empty($from['Date_Year'])) {
                $from['Date_Year']  = date('Y');
            } elseif (empty($from['Date_Day']) AND empty($from['Date_Month'])) {
                $from['Date_Month'] = 1;
            }
            if (empty($from['Date_Month'])) $from['Date_Month'] = date('n');
            if (empty($from['Date_Day']))   $from['Date_Day']   = 1;
            
            $return['from'] = $from_date = $this->read_date($from);
            $query->where_open();
                $query->where('from', '<=' , $from_date);
                $query->or_where('from', 'IS' , NULL);
            $query->where_close();
            
        }
        $to = $this->request->query('to');
        if (is_array($from) AND ! (empty($to['Date_Day']) AND empty($to['Date_Month']) AND empty($to['Date_Year']))) {
            
            if (empty($to['Date_Year'])) {
                $to['Date_Year']  = date('Y');
            } elseif(empty($to['Date_Month']) AND empty($to['Date_Day'])) {
                $to['Date_Month'] = 12;
                $to['Date_Day']   = 31;
            }
            if (empty($to['Date_Month'])) $to['Date_Month'] = date('n');
            if (empty($to['Date_Day']))   $to['Date_Day']   = date('t');
            
            $to_timestamp = strtotime($this->read_date($to));
            $to_timestamp = $to_timestamp + 86399;

            $return['to'] = $to_date = date("Y-m-d H:i:s",$to_timestamp);
            
            $query->where_open();
                $query->where('to', '>=' , $to_date);
                $query->or_where('to', 'IS' , NULL);
            $query->where_close();
        }
        $return['pager'] = $pager = new Pager($query->count_all(), 20);
        $return['list'] = $query
            ->order_by('id', 'desc')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all();

        return $return;
    }
    
    public function action_action_edit()
    {
        return array(
            'subactions'    => ORM::factory('action')->where('parent_id', '=', $this->model->id)->find_all()->as_array(),
            'goods'         => $this->model->goods->with('action_good')->find_all(),
            'b_goods'       => $this->model->goods_b->with('action_good_b')->find_all(),
        );
    }

    /**
     * Поиск товаров толпой, для акций и не только
     * @return array
     */
    function action_goods()
    {
        $return = ['sections' => Model_Section::get_catalog(FALSE, $this->request->post('vitrina'))]; // запрос на категории

        $query = ORM::factory('good') // запрос на товары
            ->where('price', '>', '0')
            ->where('section_id', '>', '0')
            ->where('brand_id', '>', '0')
            ->where('show', '=', '1')
            ->where('active', '=', '1')
            ->where('qty', '!=', '0');

        $not_wiki = $this->request->post('not_wiki'); // условие на викикатегорию

        if($not_wiki == 1){
            $query->where('wiki_cat_id', '=', '0');
            $return['not_wiki'] = $not_wiki;
        }
        $not_google = $this->request->post('not_google'); // условие на Гуглкатегорию

        if($not_google == 1){
            $query->where('google_cat_id', '=', '0');
            $return['not_google'] = $not_google;
        }

        $not_ozon = $this->request->post('not_ozon'); // условие на озон-категорию

        if($not_ozon == 1){
            $query->where('ozon_type_id', '=', '0');
            $return['not_ozon'] = $not_ozon;
        }

        $brands_q = ORM::factory('brand')->where('active', '=', 1)->order_by('name'); // запрос на бренды

        $id1c = array_unique(array_filter(preg_split('~\D+~isu', $this->request->post('id1c'))));  // если есть коды товаров - учитываем только их

        if ( ! empty($id1c)) {
            $query->where('id1c', 'IN', $id1c);
            $return['id1c'] = $id1c;

        } else {
            $name = $this->request->post('name'); // условие на название
            if ( ! empty($name)) {
                $query->and_where_open();
                    $query->where('name', 'LIKE', '%' . $name . '%');
                    $query->or_where('group_name', 'LIKE', '%' . $name . '%');
                $query->and_where_close();
                $return['name'] = $name;
            }

            $brand_id = $this->request->post('brand_id'); // условие на бренд
            if (is_array($brand_id)) $brand_id = array_filter($brand_id);
            if ($brand_id) {
                $query->where('brand_id', 'IN', $brand_id);
                $return['brand_id'] = $brand_id;
            }

            $section_id = $this->request->post('section_id'); // условие на категорию
            if (is_array($section_id)) $section_id = array_filter($section_id);
            if ( ! empty($section_id)) {
                $query->where('section_id', 'IN', $section_id);

                $bs = DB::select('brand_id', 'section_id') // если есть категории - берём бренды только из этих категорий
                    ->from('z_section_brand')
                    ->where('section_id', 'IN', $section_id)
                    ->execute()
                    ->as_array('brand_id', 'section_id');

                if ( ! empty($bs)) $brands_q->where('id', 'IN', array_keys($bs));

                $return['section_id'] = $section_id;
            }
        }

        $query->reset(FALSE);

        $return['brands']   = $brands_q->find_all();
        $return['pager']    = $pager = new Pager($query->count_all(), 50);
        $return['list']     = $query
            ->order_by('group_name')
            ->order_by('name')
            ->limit($pager->per_page)
            ->offset($pager->offset)
            ->find_all();

        $return['choice'] = FALSE;

        exit(View::factory('smarty:admin/good/search', $return)->render());
    }

    /**
     * Показать отобранные в акцию товары, добавив к ним ещё несколько
     * @throws HTTP_Exception_404
     */
    public function action_chosen()
    {
        if ($choice = $this->request->post('choice')) { // есть отобранные галочками

            $query = ORM::factory('good')->where('id', 'IN', $choice);

        } else { // отбор по успловию - условия как в action_goods

            $query = ORM::factory('good')
                ->where('price', '>', '0')
                ->where('section_id', '>', '0')
                ->where('brand_id', '>', '0')
                ->where('active', '=', '1')
                ->where('show', '=', '1')
                ->where('qty', '!=', '0');


            $id1c = array_unique(array_filter(preg_split('~\D+~isu', $this->request->post('id1c'))));  // если есть коды товаров - учитываем только их

            if ( ! empty($id1c)) {
                $query->where('id1c', 'IN', $id1c);
                $return['id1c'] = $id1c;

            } else {
                $name = $this->request->post('name'); // условие на название
                if ( ! empty($name)) {
                    $query->and_where_open();
                        $query->where('name', 'LIKE', '%' . $name . '%');
                        $query->or_where('group_name', 'LIKE', '%' . $name . '%');
                    $query->and_where_close();
                }

                $brand_id = $this->request->post('brand_id'); // условие на бренд
                if (is_array($brand_id)) $brand_id = array_filter($brand_id);
                if ($brand_id) {
                    $query->where('brand_id', 'IN', $brand_id);
                }

                $section_id = $this->request->post('section_id'); // условие на категорию
                if (is_array($section_id)) $section_id = array_filter($section_id);
                if ( ! empty($section_id)) {
                    $query->where('section_id', 'IN', $section_id);
                }
            }
        }

        $add = $query->find_all()->as_array('id'); // добавочные

        $tmpl = array(
            'goods' => $add,
            'total' => count($add),
            'mode'  => $this->request->post('mode'),
            'discount' => $this->request->post('discount'),
            'min_qty' => $this->request->post('min_qty')
        );

        exit(View::factory('smarty:admin/good/chosen', $tmpl)->render());
    }
    
    /**
     * Список и удаление дат доставки
     */
    public function action_daemon_quest_list()
    {
        $return = array();
        if ('sleep' == $this->request->query('pause'))      Daemon::pause();
        elseif ('wakeup' == $this->request->query('pause')) Daemon::pause(TRUE);
        
        if ('clear' == $this->request->query('task'))      Daemon::no_more_tasks();
        elseif ('new' == $this->request->query('task'))    Daemon::new_task();
        
        if ('stop' == $this->request->query('stop'))    Daemon::stop();
        elseif ('allow' == $this->request->query('stop'))   Daemon::stop(TRUE);

        if ($this->request->query('pause') OR $this->request->query('stop')) {
            $this->request->redirect(Route::url('admin_list',array('model'=> 'daemon_quest')));
        }
        
        if (file_exists(APPPATH . Daemon::PAUSE_FILE))  $return['pause'] = TRUE;
        if (file_exists(APPPATH . Daemon::STOP_FILE))   $return['stop']  = TRUE;
        if (file_exists(APPPATH . Daemon::ALIVE_FILE))  $return['alive'] = TRUE;
        
        $query = $this->model;
        
        $query->reset(FALSE);
        
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        
        $return['list'] = ORM::factory('daemon_quest')
                ->order_by('created','DESC')
                ->offset($pager->offset)
                ->limit($pager->per_page)
                ->find_all()->as_array();
        
        //echo(SIGUSR1);
        
        return $return;
    }
    
    /**
     * Список и удаление дат доставки
     */
    public function action_delivery_list()
    {
        $return = array();
        $zone_id = $this->request->post('zone_id');
        $morning = intval($this->request->post('morning'));
        $date = $this->request->post('date');

        if (Valid::date($date)) {
            try {
                $q = DB::insert('z_no_delivery')
                    ->columns(array('id', 'zone_id', 'morning'))
                    ->values(array($date, $zone_id, $morning));
                $q .= 'on duplicate key update morning = values(morning)';
                DB::query(Database::INSERT, $q)->execute();

            } catch (Database_Exception $e) {
                $this->tmpl['errors'] = array('delivery' => $e->getMessage());
            }
            $this->request->redirect(Route::url('admin_list', array('model' => 'delivery')));
        }

        $return['zones'] = ORM::factory('zone')->where('active', '=', 1)->order_by('priority')->find_all()->as_array('id');
        return $return;
    }

    /**
     * Интервалы доставки - с поиском
     */
    function action_zone_time_list()
    {
        $return = array();

        $query = $this->model;

        $zone_id = $this->request->query('zone_id');

        if ( ! empty($zone_id)) {
            $return['zone_id'] = $zone_id;
            $query->where('zone_id', '=', $zone_id);
        }

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list'] = $query
            ->order_by('active', 'desc')
            ->order_by('sort', 'asc')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all();

        return $return;
    }

    function action_zone_time_edit(Model_Zone_Time $model)
    {
        $wd = $this->request->post('week_day');
        if (is_array($wd)) {
            $model->week_day = intval(array_reduce($wd, function($a, $b) {return $a | $b;}));
            if ($model->changed('week_day')) {
                Model_History::log('zone_time', $model->id, 'week_day change');
                $model->save();
            }
        }

        $new_prices = $this->request->post('prices');
        $new_price = $this->request->post('new_price');

        $prices_changed = FALSE;
        if ($new_prices || $new_price) {
            $old_prices = $this->model->prices->order_by('min_sum')->find_all()->as_array('id');

            foreach($old_prices as $id => $price) {
                if ($new_prices[$id]['min_sum'] == '' || $new_prices[$id]['price'] == '') { // удаляем цены
                    $prices_changed = TRUE;
                    $price->delete();
                } else { // меняем цены
                    $price->min_sum = $new_prices[$id]['min_sum'];
                    $price->price = $new_prices[$id]['price'];
                    if ($price->changed()) $prices_changed = TRUE;
                    $price->save();
                }
            }

            if ($new_price) { // добавляем новые цены если возникли
                $new_min_sum = $this->request->post('new_min_sum');
                foreach($new_price as $k => $p) {
                    if (ctype_digit($p) && ctype_digit($new_min_sum[$k])) {
                        $ztp = new Model_Zone_Time_Price();
                        $ztp->time_id = $model->id;
                        $ztp->price = $p;
                        $ztp->min_sum = $new_min_sum[$k];
                        $ztp->save();
                        $prices_changed = TRUE;
                    }
                }
            }

            if ($prices_changed) Model_History::log('zone_time', $model->id, 'prices change');
        }
    }

    function action_pricelab()
    {
        $ymd = $this->request->param('ymd');
        $file = APPPATH."logs/".$ymd."/prices.csv";

        if ( ! empty($ymd) && file_exists($file)) { // download file
            header('Content-Description: File Transfer');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename='.basename(str_replace('/', '_', $ymd).'prices.csv'));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }

        $return = array();
        exec("ls -S -t ".APPPATH."logs/*/*/*/prices.csv", $output);
        foreach($output as $file) {
            if (preg_match('~20\d\d/\d\d/\d\d~', $file, $match)) {
                $return[$match[0]] = $file;
            }
        }
        $this->tmpl['history'] = $return;
        $this->layout->body = View::factory('smarty:admin/pricelab', $this->tmpl)->render();
    }
	
	function action_tag_excel(){
		
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=tags.csv');
		$content = '';
		
		$result = DB::query(Database::SELECT, "SELECT * FROM z_tag WHERE goods_count > 0")->execute();

		$content .= '"URL";' . '"Code";' . '"Title";' . '"Описание";' . '"Keywords";' . '"Имя";' . '"Анкор";' . '"text";' . "\n";
		while( $tag = $result->current() ){
			
			$content .= '"http://mladenec-shop.ru/' . $tag['code'] . '";' . '"' . $tag['code'] . '";' . '"' . $tag['title'] . '";' . '"' . $tag['description'] . '";' . '"' . htmlspecialchars(strip_tags( $tag['keywords'])) . '";' . '"' . htmlspecialchars(strip_tags( $tag['name'])) . '";' . '"' . htmlspecialchars(strip_tags( $tag['anchor'])) . '";' . '"' . htmlspecialchars(strip_tags( $tag['text'] )) . '";' . "\n";
			$result->next();
		}
		
		echo iconv("utf-8", "windows-1251//TRANSLIT", $content );
		exit;
	}

    /**
     * Список купонов - с поиском
     * @return array
     */
    function action_coupon_list()
    {
        $query = ORM::factory('coupon');

        if ($name = $this->request->query('name')) {
            $query->where('name', 'LIKE', trim($name));
        }

        $query->reset(FALSE);
        $return['pager'] = $pager = new Pager($query->count_all(), 50);
        $return['list'] = $query
            ->order_by('id', 'desc')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all()
            ->as_array();

        return $return;
    }

    /**
     * Вызов функции для получения сео статистики
     * @return array
     */
    public function action_seostatistics_list()
    {
        $stats = new Model_Seostatistics();
        return $stats->get_seostatistics();
    }

    /**
     * Список блок ссылок
     * @return array
     */
    function action_blocklinks_list()
    {
        $link = $this->request->post('link');

        if(isset($link) && !empty($link)){
            $link = trim($link, ' ');
            $link = trim($link, '/');

            $mblocklinks = new Model_Blocklinks();
            $res_blocklink = $mblocklinks->get_blocklink_url(trim($link));

            if(count($res_blocklink) == 0){
                $save_link = DB::insert('blocklinks')
                    ->columns(['link'])
                    ->values(['link' => $link ])
                    ->execute();
                $id = $save_link[0];
            } else {
                $id = $res_blocklink[0]->id;
            }
            $this->request->redirect(Route::url('admin_edit',array('model'=>'blocklinks','id'=>$id)));
        }

        $query = ORM::factory('blocklinks');

        $query->reset(FALSE);
        $domains = Kohana::$config->load('domains')->as_array();

        $return['host'] = $domains['mladenec']['host'];
        $return['pager'] = $pager = new Pager($query->count_all(), 20);
        $return['list'] = $query
            ->order_by('id', 'desc')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all()
            ->as_array();

        return $return;
    }

    public function action_blocklinks_edit(Model_Blocklinks $data)
    {
        $edit_id = $this->request->post('id');

        $blocklink = new Model_Blocklinks();
        if(isset($edit_id) && !empty($edit_id)){
            $form_vars['res'] = $blocklink->edit_blocklink($edit_id);
            $this->request->redirect(Route::url('admin_list', array('model' => 'blocklinks')));
        } else {
            $id = $data->id;
            $form_vars['res'] = $blocklink->get_blocklink($id);
        }
        return $form_vars;
    }

    public function action_blocklinks_del(){
        $id = $this->request->param('id');

        $blocklinks = ORM::factory('blocklinks', $id);
        if($blocklinks->loaded()){
            $blocklinks->delete();
        }
        $blocklinksanchor = ORM::factory('blocklinksanchor', $id);
        if($blocklinksanchor->loaded()){
            $blocklinksanchor->delete();
        }
        $this->request->redirect(Route::url('admin_list', array('model' => 'blocklinks')));
    }
}

