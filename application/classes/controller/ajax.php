<?php

class Controller_Ajax extends Controller_Frontend {

    public function action_cart()
    {
        $cart = Cart::instance();
        $vars = array();
        
        $goods = $cart->recount();
        $vars['comments'] = $cart->get_comments();
        $vars['cart'] = $cart->save();
        $vars['presents'] = array();
        // подарки по акциям - пересчитываем всегда на этой странице
        if ( ! empty($goods)) {
            $presents = $cart->check_actions($goods);
            if ( ! empty($presents)) {
                $vars['present_goods'] = ORM::factory('good')->where('id', 'IN', array_keys($presents))->find_all()->as_array('id');
            }
            if ( ! empty($cart->promo)) {
                $vars['promo'] = $cart->promo;
            }
        }
        $vars['presents'] = $presents;
        $vars['goods'] = $goods;
        $v = View::factory('smarty:product/view/cart',$vars);
        exit($v->render());
    }
    
    public function action_cart_update()
    {
        $cart = Cart::instance();
		
		if($inc = $this->request->post('inc') )
			$cart->inc($inc);
		else if( $dec = $this->request->post('dec') )
			$cart->dec($dec);
		else if( $change = $this->request->post('change') ){
			$cart->set_good_qty($change, $this->request->post('value'));
		}
				
		exit(View::factory('smarty:cart/cart', $this->cartParams())->render());
	}
    
    /**
     * Варианты опроса
     */
    public function action_poll_variants()
    {
        $id = $this->request->param('id');
        $poll = ORM::factory('poll',$id)->with('questions');
                
        if ( ! $poll->loaded()) throw new HTTP_Exception_403;
        
        $questions = $poll->questions->order_by('sort')->find_all()->as_array();
                
        $v = View::factory('smarty:page/poll/questions',array('questions' => $questions,'p'=>$poll));
        
        exit($v->render());
        
    }
    
    /**
     * загрузка формы доставки в зависимости от способа доставки
     */
    public function action_delivery()
    {
        /*if ( empty($this->user->id)) {
            throw new HTTP_Exception_403;
        }*/

        if ( ! in_array($type = $this->request->param('type'), Model_Order::delivery_types())) {
            throw new HTTP_Exception_404;
        }

        $v = View::factory('smarty:user/order/' . $type);
        $v->set('o', new Model_Order_Data());

        if ( ! empty($this->user->id) && $type == Model_Order::SHIP_COURIER)
        {
            // вычисляем адреса доставки
            $v->set('address', ORM::factory('user_address')
                ->where('latlong', '!=', 'TRANSPORT')
                ->where('latlong', '>', '')
                ->where('user_id', '=', $this->user->id)
                ->where('active', '=', 1)
                ->limit(5)
                ->order_by('id', 'DESC')
                ->find_all()
                ->as_array()
            );

        }
        else if ( ! empty($this->user->id) && $type == Model_Order::SHIP_SERVICE)
        {
            $v->set('address', ORM::factory('user_address')
                ->where('latlong', '=', 'TRANSPORT')
                ->where('user_id', '=', $this->user->id)
                ->where('active', '=', 1)
                ->limit(5)
                ->order_by('id', 'DESC')
                ->find_all()
                ->as_array()
            );
        }
        else if (empty($this->user->id))
        {
            $v->set('address', array());
        }

        $v->set('big', Cart::instance()->big_to_wait(TRUE)); // большие товары из корзины для предупреждения
        exit($v->render());
    }

    /**
     * вычисление зоны доставки
     * Тут же вычисляем для неё даты и время
     */
    public function action_zone()
    {
        $latlong = $this->request->query('latlong');
        if (empty($latlong)) throw new HTTP_Exception_404;

        $zone = Model_Zone::locate($latlong);
		
		if( $zone !== false ){
			
			$dates = $zone->allowed_date(Cart::instance());

			$months = array( //
				'01' => 'января',
				'02' => 'февраля',
				'03' => 'марта',
				'04' => 'апреля',
				'05' => 'мая',
				'06' => 'июня',
				'07' => 'июля',
				'08' => 'августа',
				'09' => 'сентября',
				'10' => 'октября',
				'11' => 'ноября',
				'12' => 'декабря',
			);

			$weekdays = array(
			  1 => 'понедельник',
			  2 => 'вторник',
			  3 => 'среда',
			  4 => 'четверг',
			  5 => 'пятница',
			  6 => 'суббота',
			  7 => 'воскресенье',
			);

			foreach( $dates as $d => &$_ ){
				list( $year, $month, $day, $weekday ) = explode( '-', date( 'Y-m-d-N', strtotime( $d ) ) );
				$_ = '<span>' . $day . ' ' . $months[$month] . '</span>, ' . $weekdays[$weekday];
			}
			unset( $_ );

			$this->return_json(array(
				'zone' => $zone->name,
				'date' => Form::select('ship_date', $dates, NULL, array('id' => 'date')),
				'dates' => $dates,
				'closest' => $zone->id == Model_Zone::ZAMKAD ? Model_Zone::closest_mkad($latlong) : FALSE,
				'zone_id' => $zone->id,
			));
		}
		else {
			$this->return_json(array(
				'zone_id' => false
			));
		}
    }

    /**
     * вычисление интервалов доставки в зависимости от даты и зоны
     */
    public function action_time()
    {
        $zone = $this->request->post('zone');
        if ( ! $zone) $this->return_error('Zone required');
        $zone = new Model_Zone($zone);
        if ( ! $zone->loaded() || ! $zone->active) $this->return_error('Zone required');

        $date = $this->request->post('date');
        if ( ! $date || ! strtotime($date)) $this->return_error('Date required');

        $this->return_json(array(
            'time' => Form::select('ship_time', $zone->allowed_time($date, Cart::instance()->get_total()), NULL, array('id' => 'time')),
        ));
    }

    /* оставить мнение о пользе отзыва */
    public function action_review()
    {
        if ( ! $this->request->post('ajax')) throw new HTTP_Exception_404;

        $id = $this->request->param('id');
        $review = new Model_Good_Review($id);
        if ( ! $review->loaded()) throw new HTTP_Exception_404;

        $votes = Session::instance()->get('votes'); // за что уже голосовал
        $vote = FALSE;
        if (empty($votes)) $votes = array();

        if (empty($votes[$id])) {

            switch($this->request->param('vote')) {
                case 'no':
                    $review->vote_no += 1;
                    $vote = 'no';
                    break;
                case 'ok':
                    $review->vote_ok += 1;
                    $vote = 'ok';
                    break;
            }
            $review->update();
            $votes[$id] = $vote;
            Session::instance()->set('votes', $votes);
        }

        exit(View::factory('smarty:common/vote', array('c' => $review, 'votes' => $votes))->render());
    }

    /**
     * Расчёт стоимости доставки через едост
     * @throws HTTP_Exception_403
     */
    public function action_edost()
    {
        exit( Model_Order::edost($this->request->post('city')) );
    }

    /**
     * показ кусочка частозаказываемых товаров, ажаксом
     */
    public function action_frequent()
    {
        $id = $this->request->param('id');
        $good = new Model_Good($id);
        
        if ( ! $good->loaded()) throw new HTTP_Exception_404;
        
        /* Участвует ли товар в промоакциях? */
        $bundled_goods = $good->get_promo_goods(1,$this->request->query('page')); 
                
        if ( ! empty($bundled_goods)) {
            /* Участвует, показываем бандлы */
            $f  = $bundled_goods;
            
        } else {
            /* Не участвует, показываем часто заказываемые */
            $f = $good->get_frequent($this->request->query('page'));
        }

        if (empty($f)) exit('');
            
        $ids = array();
        foreach($f as $good) {
            $ids[] = $good->id;
        }
        
        exit(View::factory('smarty:common/goods', array(
            'goods' => $f,
            'price' => Model_Good::get_status_price(1, $ids),
            'imgs'  => Model_Good::many_images(array(255), $ids),
            'short' => 1
        ))->render());
    }

    /**
     * Показ слайдера с новыми или распродажными товарами
     */
    public function action_slide()
    {
        $page = $this->request->query('page');

        switch($this->request->param('type')) {
            case 'new':
                $items = Model_Good::get_new($total, $page, 4);
                break;
            
            case 'superprice':
                $items = Model_Good::get_superprice($total, $page, 4);
                break;
            
            case 'cart_set':
                $slider_id = $this->request->param('set_id');
                $items = Model_Good::get_set_slider($slider_id, $total, $page, 5, TRUE, Cart::instance()->recount());
                break;

            case 'promo':
				
				$prmkey = $this->request->param('set_id');
				$promo = ORM::factory('promo', $prmkey);
				$items = $promo->get_goods();

				$perPage = 5;
				$pageNum = $page - 1;
				$pages = ceil( count( $items ) / $perPage );
				if( $pageNum * $perPage >= ( count( $items ) - $perPage ) ){
					$pageNum = $pageNum % $pages;
				}
				
				$items = array_slice($items, $pageNum * $perPage, $perPage);
				
				
				break;
            case 'cart2_set':
				
				$prmkey = $this->request->param('set_id');

				$goods = Cart::instance()->recount();
				
				$promos = [];
				foreach( $goods as &$g ){

					$promos = array_merge( $promos, $g->get_promos() );
				}
				unset( $g );

				$promo = &$promos[$prmkey];
				
				$items = $promo->get_goods();
				
				$cartIds = array_keys( $goods );
				foreach( $items as $y => &$sg ){
					if( in_array( $sg->id, $cartIds ) )
						unset( $items[$y] );
				}
				unset( $sg );
				
				$perPage = 5;
				$pageNum = $page - 1;
				$pages = ceil( count( $items ) / $perPage );
				if( $pageNum * $perPage >= ( count( $items ) - $perPage ) ){
					$pageNum = $pageNum % $pages;
				}
				
				$items = array_slice($items, $pageNum * $perPage, $perPage);
				
                break;

            case 'sale':
                $items = Model_Good::get_sale($total, $page, 4);
                break;

            case 'pampers':
                $g = Model_Good::get_pampers(FALSE);
                $offset = ((abs(intval($page)) + 1) % 2) * 4; // только из первых 8
                $items = ORM::factory('good')
                    ->where('id', 'IN', $g)
                    ->order_by('popularity', 'DESC')
                    ->offset($offset)
                    ->limit(4)
                    ->find_all()
                    ->as_array('id');
                break;

            default:
                throw new HTTP_Exception_404;
        }
        $item_keys = array();
        foreach($items as $item) {
            $item_keys[$item->id] = $item->id;
        }
        exit(View::factory('smarty:common/goods', array(
            'short' => 1,
            'style' => $this->request->param('type'),
            'goods' => $items,
            'price' => Model_Good::get_status_price(1, $item_keys),
            'imgs'  => Model_Good::many_images(array(255), $item_keys)
        ))->render());
    }

    /**
     * Показ слайдера с новыми или распродажными товарами
     */
    public function action_action_goods()
    {
        $action = new Model_Action($this->request->param('id'));
        if ( ! $action->loaded()) throw new HTTP_Exception_404;

        $visible_good_ids = $action->good_idz(TRUE);

        if (empty($visible_good_ids)) throw new HTTP_Exception_404;

        exit(View::factory('smarty:product/view/tiles', array(

            'goods' => ORM::factory('good')
                ->where('id', 'IN', $visible_good_ids)
                ->limit(8)
                ->offset(0)
                ->find_all()
                ->as_array(),

            'price' => Model_Good::get_status_price(1, $visible_good_ids),
            'row' => 4
        ))->render());
        
    }
    
    /* оставить заявку на звонок */
    public function action_callback()
    {

        $view = View::factory('smarty:user/callback');

        if ($this->request->post('save_callback')) {

            $m = new Model_Call();
            $m->values($this->request->post());

            $captcha = $this->user ? TRUE : Captcha::check($this->request->post('captcha'));

            if ($m->validation()->check() AND $captcha) {

                if ($this->user) $m->user_id = $this->user->id;
                $m->save(); // сохранили заявку

                Mail::htmlsend('callback', $m->as_array(), 'dostavka@mladenec-shop.ru', 'Заказан звонок на '.$m->phone);
                $this->return_html('Заявка на&nbsp;звонок принята');

            } else {

                $errors = $m->validation()->errors('call/add');
                if ( ! $captcha) $errors['captcha'] = Kohana::message('captcha', 'captcha.default');
                $this->return_error($errors);
            }
        }

        exit($view->render());
    }

    /* оставить сообщение об ошибке */
    public function action_error()
    {
        $view = View::factory('smarty:user/error');

        if ($this->request->post('save_error')) {

            $captcha = $this->user ? TRUE : Captcha::check($this->request->post('captcha'));

            if ($captcha) {

                $letter = new Mail();
                $txt = '';
                $data = array('problem' => $this->request->post('problem')) + $_SERVER;
                if ($this->user) {
                    $data['user_id'] = $this->user->id;
                    $data['user_email'] = $this->user->email;
                    $data['user_name'] = $this->user->name;

                } elseif ($this->request->post('email')) {

                    $data['user_email'] = $this->request->post('email');
                }

                foreach($data as $k => $v) {
                    $txt .= '<p><strong>'.$k.'</strong><br />'.$v.'</p>';
                }
                $letter->setHTML($txt);
                if ( ! ($to = Conf::instance()->mail_error)) $to = 'm.zukk@ya.ru';
                $letter->send($to, 'Error report');

                $this->return_html('Спасибо! Информация принята.');

            } else {

                $errors['captcha'] = Kohana::message('captcha', 'captcha.default');
                $this->return_error($errors);
            }
        }

        exit($view->render());
    }

    /**
     * Получить хиты продаж по категории
     */
    public function action_hitz()
    {
        if ( ! ($section_id = $this->request->param('section_id'))) throw new HTTP_Exception_404;
        $hitz = Model_Good::get_hitz($section_id);
        if (empty($hitz)) throw new HTTP_Exception_404;

        $view = View::factory('smarty:common/goods', array('goods' => $hitz, 'short' => 1));
        $goodidz = array_keys($hitz);
        if ($goodidz) {
            $view->price = Model_Good::get_status_price(1, $goodidz);
            $view->imgs = Model_Good::many_images(array(255), $goodidz);
        }

        exit($view->render());
    }


    /**
     * Ажакс-подгрузка отзывов о товаре/группе
     * @throws HTTP_Exception_404
     */
    public function action_reviews()
    {
        if ( ! $this->request->post('ajax')) throw new HTTP_Exception_404;

        $page = intval($this->request->post('page'));
        $offset = $page * 5;

        switch ($this->request->param('type')) {

            case 'product':
                $good = new Model_Good($this->request->param('id'));
                if ( ! $good->loaded()) throw new HTTP_Exception_404;

                $reviews = $good->reviews
                    ->where('active', '=', 1)
                    ->with('author')
                    ->order_by('priority', 'DESC')
                    ->limit(6)
                    ->offset($offset)
                    ->find_all()
                    ->as_array('id');

                $params = Model_Section_Param::for_reviews(array_keys($reviews), $good->section_id);

                $view = View::factory('smarty:product/view/reviews', array(
                    'group' =>  $good->group,
                    'goods' =>  array($good->id => $good)
                ));
                break;

            case 'group':
                $group = new Model_Group($this->request->param('id'));
                if ( ! $group->loaded()) throw new HTTP_Exception_404;
                $goods = $group->goods->where('show', '=', 1)->find_all()->as_array('id');
                $reviews = ORM::factory('good_review')
                    ->where('good_id', 'IN', array_keys($goods))
                    ->where('active', '=', 1)
                    ->with('author')
                    ->order_by('priority', 'DESC')
                    ->limit(6)
                    ->offset($offset)
                    ->find_all()
                    ->as_array('id');

                $params = Model_Section_Param::for_reviews(array_keys($reviews), $group->section_id);

                $view = View::factory('smarty:product/view/reviews', array(
                    'group' =>  $group,
                    'goods' =>  $goods
                ));
                break;

            default:
                throw new HTTP_Exception_404;
                break;
        }

        $view->set('page', $page);
        $view->set('params', $params);
        $view->set('comments', $reviews);
        $view->set('votes', Session::instance()->get('votes'));

        exit($view->render());
    }
	
	public function action_taggg(){

		ini_set('max_execution_time', 0);
		
		$sections = [29798, 29781, 31074, 29778, 28856, 28628, 28783, 28719, 28836, 52580, 28682, 28704, 105926, 105927, 105928, 105929 ];
		
		ob_end_clean();
		$file = fopen('taggg.csv', 'w');
		
		$r = 0;
		foreach( $sections as $sectionId ){
			
			$section = new Model_Section( $sectionId );
			if( $section->settings['list'] > 0 && $section->settings['list_filter'] > 0 ){

				$listFilter = $section->settings['list_filter'];
				
				$bFilterValues = [];
				$result = DB::query(Database::SELECT, "SELECT * FROM z_filter_value WHERE filter_id = $listFilter")->execute();
				while( $row = $result->current() ){

					$bFilterValues[] = $row['id'];
					$result->next();
				}
				
				foreach( $bFilterValues as $bFilterValue ){

					$hash = $init_hash = $_POST['hash'] = 's=rating;pp=12;x=0;m=0;';
					$query = $_POST['query'] = $sectionId;

					$sphinx = new Sphinx('section', $query);
					$d = $sphinx->menu($query . '_' . $bFilterValue, 'section_filter');

					$brands = &$d['brands'];
					$filters = &$d['filters'];
					$fvals = &$d['vals'];
					
					if( empty( $filters ) )
						continue;
					
					foreach( $filters as $key => &$filter ){
						if( $filter == 'По количеству')
							unset( $filters[$key] );
					}
					unset( $filter );

					foreach( $brands as $brandId => $brand ){
						
						foreach( $filters as $filterId => &$fName ){

							foreach( $fvals[$filterId] as $valId => $valName ){

								if( $r > 10000 )
									exit;

								$hash = $init_hash;
								$hash = 'b=' . $brandId . ';' . $hash;
								$hash .= 'f' . $filterId . '=' . $valId . ';';

								$_sphinx = new Sphinx('section', $sectionId);
								$_sphinx->param('b', [$brandId] );
								$_sphinx->param('f', [$filterId => [$valId]]);
								$goods = $_sphinx->search();
								
								if( count( $goods ) > 2 ){
									fwrite($file, iconv('utf-8', 'windows-1251', '"http://mladenec-shop.ru/catalog/' . $section->translit . '/' . $section->id . '_' . $bFilterValue . '.html#!' . $hash . '";"' .  $brand['name'] . ' ' . $valName . '"' . "\r\n" ) );
									$r++;
								}
							}
						}
						unset( $fName );
					}
				}
			}
			else{
					$hash = $init_hash = $_POST['hash'] = 's=rating;pp=12;x=0;m=0;';
					$query = $_POST['query'] = $sectionId;

					$sphinx = new Sphinx('section', $query);
    				$d = $sphinx->menu();

					$brands = &$d['brands'];
					$filters = &$d['filters'];
					$fvals = &$d['vals'];

					if( empty( $filters ) )
						continue;
					
					foreach( $filters as $key => &$filter ){
						if( $filter == 'По количеству')
							unset( $filters[$key] );
					}
					unset( $filter );

					foreach( $brands as $brandId => $brand ){
						
						foreach( $filters as $filterId => &$fName ){

							foreach( $fvals[$filterId] as $valId => $valName ){

								if( $r > 10000 )
									exit;

								$hash = $init_hash;
								$hash = 'b=' . $brandId . ';' . $hash;
								$hash .= 'f' . $filterId . '=' . $valId . ';';

								$_sphinx = new Sphinx('section', $sectionId);
								$_sphinx->param('b', [$brandId] );
								$_sphinx->param('f', [$filterId => [$valId]]);
								$_sphinx->param('pp', 48);
								$goods = $_sphinx->search();

								if( count( $goods ) > 2 ){
									fwrite($file, iconv('utf-8', 'windows-1251', '"http://mladenec-shop.ru/catalog/' . $section->translit . '#!' . $hash . '";"' . $brand['name'] . ' ' . $valName . '"' . "\r\n" ) );
									$r++;
								}
							}
						}
						unset( $fName );
					}
			}
		}
		
		fclose( $file );
		
		exit('good bye');
	}

    /**
     * Ажакс-подгрузка рейтингов товара/группы
     * @throws HTTP_Exception_404
     */
    public function action_stats()
    {
        if ( ! $this->request->post('ajax')) throw new HTTP_Exception_404;

        switch ($this->request->param('type')) {

            case 'product':
                $good = new Model_Good($this->request->param('id'));
                if ( ! $good->loaded()) throw new HTTP_Exception_404;

                $reviews = $good->reviews
                    ->where('active', '=', 1)
                    ->order_by('vote_ok', 'DESC')
                    ->find_all()
                    ->as_array('id');
                $params = Model_Section_Param::for_reviews(array_keys($reviews), $good->section_id);

                $view = View::factory('smarty:product/view/stats');
                $view->set('params', $params);
                $view->set('rating', $good->rating);
                $view->set('review_qty', $good->review_qty);
                break;

            case 'group':
                $group = new Model_Group($this->request->param('id'));
                if ( ! $group->loaded()) throw new HTTP_Exception_404;
                $goods = $group->goods->where('active', '=', 1)->find_all()->as_array('id');
                $reviews = ORM::factory('good_review')
                    ->where('active', '=', 1)
                    ->where('good_id', 'IN', array_keys($goods))
                    ->order_by('vote_ok', 'DESC')
                    ->find_all()
                    ->as_array('id');
                $params = Model_Section_Param::for_reviews(array_keys($reviews), $group->section_id);

                $view = View::factory('smarty:product/view/stats');
                $view->set('params', $params);
                $view->set('rating', $group->rating);
                $view->set('review_qty', $group->review_qty);
                break;

            default:
                throw new HTTP_Exception_404;
                break;
        }

        exit($view->render());
    }
	
	public function action_cart_merge(){
		
		$old_ids = $this->request->post('old_session');
		
		if( !empty( $old_ids ) && is_array( $old_ids ) ){
			
			foreach( $old_ids as $old_id ){
				
				if( empty( $old_id ) ){
					continue;
				}
				
				$old_session = ORM::factory('session', $old_id);

				if( empty( $old_session->id ) ){
					continue;
				}
				
				$old_data = unserialize( $old_session->data );
				$old_goods = $old_data['cart']->goods;

				$Cart = Cart::instance();
				$Cart->add($old_goods);
				$Cart->save();

				$old_data['cart']->clean(); // Чистим старую корзину
				// $old_data['cart']->qty = 0;
				// $old_data['cart']->blago = array();
				// $old_data['cart']->total = 0;
				DB::update('z_session')->value('data', serialize( $old_data))->where('id', '=', $old_id)->execute();
			}
			
			echo 'ok';
			exit;
		}
	}
}