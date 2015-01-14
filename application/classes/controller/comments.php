<?php

class Controller_Comments extends Controller_Frontend {

    public function after() {
        $this->layout->menu = Model_Menu::html();
        parent::after();
    }

    /**
     * Добавление отзыва
     */
    public function action_add()
    {
        $m = new Model_Comment();

		$themeId = $this->request->post('theme_id');
		
		if( empty( $themeId ) )
			$themeId = $this->request->query('theme');
		
		$theme = ORM::factory('comment_theme', $themeId );
		
        $view = View::factory('smarty:comments/add')->bind('i', $m)->bind('theme', $theme);

        if ($this->request->post('send')) {

			if( empty( $themeId ) ){
			
				$theme->values($this->request->post())->save();
				$themeId = $theme->id;
				$active = true;
			}
			else
				$active = false;
			
            $m->values($this->request->post());
			$m->values(array('theme_id' => $themeId, 'active' => $active));
			
            $captcha = $this->user ? TRUE : Captcha::check($this->request->post('captcha'));

            if ($m->validation()->check() AND $captcha) {

                $m->save();
                Model_History::log('comment', $m->id, 'create', $m->as_array()); // запоминаем в истории ип отзыва
                $to = Conf::instance()->mail_comment;
                if ( ! empty($to) ) {
                    Mail::htmlsend('comment', array('i' => $m, 'theme' => $theme), $to, 'Поступил новый отзыв о сайте');
                }

                $view->sent = TRUE;
                $this->return_html($view->render());

            } else {

                $errors = $m->validation()->errors('comments/add');
                if ( ! $captcha) $errors['captcha'] = Kohana::message('captcha', 'captcha.default');
                $this->return_error($errors);

            }
        }
        exit($view->render());
    }

    /**
     * старая страница отзыва
     */
    public function action_old_view()
	{
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /about/review#!id" . $this->request->param('id'));
		exit;
	}
	
    /**
     * Просмотр отзыва
     * @throws HTTP_Exception_404
     */
    public function action_view()
	{
        $item = ORM::factory('comment_theme', $this->request->param('id'));
		
        if ( ! $item->active) throw new HTTP_Exception_404;

        $this->tmpl['theme'] = $item;
		
		$hash = $this->request->query('hash');
		
		$currentUser = Model_User::current();
		
		$allowAnswer = false;
		if( ( !empty( $hash ) && $item->getHash() == $hash ) || ( ! empty( $currentUser ) && $currentUser->id == $item->user_id ) ){
			
			$allowAnswer = true;
		}
		
		$this->tmpl['data'] = $item->getData(true);
		$this->tmpl['allowAnswer'] = $allowAnswer;
        $this->layout->title = 'Отзыв: '.$item->name;
		
		$v = View::factory('smarty:comments/view',$this->tmpl);
		echo ($v->render());
		exit;
	}
	
	public function action_index()
    {
		$this->tmpl['content'] = View::factory('smarty:comments/_index',$this->tmpl)->render();
	}

    /**
     * Список отзывов
     */
    public function action_list()
   	{
        $c = ORM::factory('comment_theme')->where('active', '=', 1)->reset(FALSE);

        $page = $this->request->query('page');
		
		if( !empty( $page ) ){
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: /about/review");
		}
		
        $_offset = $offset = $this->request->query('offset');
		
		$iPerPageQty = @Kohana::$hostnames[Kohana::$server_name]['per_page_elements'] ?: 10;
		
		if( empty( $offset ) ){

			$this->tmpl['pager'] = $pager = Pager::factory($c->count_all(), $iPerPageQty);
			$offset = $pager->offset;
			$per_page = $pager->per_page;
		}
		else{
			
			$per_page = $iPerPageQty;
		}
		
		$this->tmpl['perPage'] = $per_page;
		$this->tmpl['comments'] = $c->getLast($per_page, $offset);
		
		$this->tmpl['count'] = $c->reset(false)->count_all();
		
		if( !empty( $_offset ) ){
			$v = View::factory('smarty:comments/list_item',$this->tmpl);
			echo ($v->render());
			exit;
		}
		else{
			$v = View::factory('smarty:comments/list',$this->tmpl);
			echo ($v->render());
			exit;
		}
    }
}
