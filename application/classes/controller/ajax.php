<?php

class Controller_Ajax extends Controller_Frontend {

    public function before()
    {
        parent::before();
        $this->layout = View::factory('smarty:ajax');
    }

    public function after()
    {
        $this->response->body($this->layout->render());
    }

    public function action_cart()
    {
        $cart = Cart::instance();
        $goods = $cart->recount();

        // подарки по акциям - пересчитываем всегда на этой странице
        $vars = [
            'comments'  => $cart->get_comments(),
            'presents'  => [],
            'cart'      => $cart,
            'goods'     => $goods,
        ];

        if ( ! empty($goods)) {
            $presents = $cart->check_actions($goods);
            if ( ! empty($presents)) {
                $vars['present_goods'] = ORM::factory('good')->where('id', 'IN', array_keys($presents))->find_all()->as_array('id');
            }
            if ( ! empty($cart->promo)) {
                $vars['promo'] = $cart->promo;
            }
            $vars['presents'] = $presents;
        }

        $this->return_html(View::factory('smarty:product/view/cart', $vars)->render());
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
				
		$this->return_html(View::factory('smarty:cart/goods', $this->cartParams())->render());
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
    }

    /**
     * вычисление зоны доставки
     * Тут же вычисляем для неё даты и время и цену
     */
    public function action_zone()
    {
        $latlong = $this->request->query('latlong');
        if (empty($latlong)) exit('no latlong');

        $zone = Model_Zone::locate($latlong); // определяем зону доставки

        $cart = Cart::instance();
        $cart->recount();

        $return = ['zone_id' => strval($zone)];
        // расстояние и сумма для бесплатной доставки замкад
        $return['free_delivery'] = $cart->total >= 2000 ? 10 : 0;

        Log::instance()->add(Log::INFO, 'ZUZU '.$zone);

        if ($zone !== FALSE) { // доставка в наши зоны доставки

            $dates = $cart->allowed_date($zone, $latlong);
            $return['ship_date'] = View::factory('smarty:cart/ship_date', ['dates' => $dates])->render();
            $return['ship_time'] = View::factory('smarty:cart/ship_time', $cart->allowed_time($zone, key($dates), $latlong))->render();
            $return['price']    = $cart->ship_price($latlong, strval($zone)); // цена доставки

            $return['zone']     = $zone->name;
            $return['closest']  = $zone->id == Model_Zone::ZAMKAD ? Model_Zone::closest_mkad($latlong) : FALSE;
         
	    } else { // региональная доставка

            $city_id = $this->request->query('dpd_city_id');
            $city_name = $this->request->query('city');

            $door = $closest_term = $terminal = $min_param = []; // нет вариантов доставки

            try {
                $weight = $cart->weight();
                $volume = $cart->volume();

                if ( ! $cart->ship_wrong && empty($cart->big) && ! empty($city_name)) { // в заказе кгт или кривые данные о размере-весе или нет города - не надо считать доставку

                    $dpd = new DpdSoap(); // запрос цены доставки в dpd

                    $city = new Model_Dpd_City($city_id);
                    if ( ! $city->loaded()) { // не нашли город по id, поищем по названию
                        $parts = array_filter(array_map('trim', explode(',', $city_name))); // название города может быть составным, с областью или районом
                        $city = ORM::factory('dpd_city')->where('name', 'IN', $parts)->find();
                    }
                    if ($city->loaded()) { // есть город - запрос по id
                        $door = $dpd->ship_price($city->id, $cart->total, $weight, $volume);
                    } elseif ( ! empty($city_name)) {  // нет города - запрос по последней части, типа Республика Крым, Керчь
                        $door = $dpd->ship_price($parts[count($parts) - 1], $cart->total, $weight, $volume);
                    }

                    if ( ! empty($door)) {
                        foreach ($door as $v) {
                            $v->cost = ceil(floatval($v->cost));
                            if ( ! isset($min_param['cost']) || ($v->cost < $min_param['cost'] || ($v->cost == $min_param['cost'] && $v->days < $min_param['days']))) { // лучшая цена или время
                                $min_param = ['days' => $v->days, 'cost' => $v->cost, 'dt' => 'D'];
                            }
                        }
                    }

                    // ищем ближайший терминал
                    $reverse_latlong = implode(' ', array_reverse(explode(',', $latlong)));

                    $closest_term = DB::select()
                        ->from('dpd_terminal')
                        ->where('latlong', 'LIKE', '%,%')
                        ->order_by(DB::expr("geodist_pt(GeomFromText('Point(" . $reverse_latlong . ")'), point)"))
                        ->limit(1)
                        ->execute()
                        ->as_array();

                    $closest_term = $closest_term[0];

                    $terminal = $dpd->ship_price($closest_term['city_id'], $cart->total, $cart->weight(), $cart->volume(), FALSE);

                    if ( ! empty($terminal)) {
                        foreach ($terminal as &$v) {
                            $v->cost = ceil(floatval($v->cost));
                            if ( ! isset($min_param['cost'])) { // лучшая цена или время только если нет до двери
                                $min_param = ['days' => $v->days, 'cost' => $v->cost, 'dt' => 'T'];
                            }
                        }
                    }
                    if (empty($min_param['cost'])) { // не определили цену
                        $min_param = ['cost' => 0, 'dt' => ''];
                    }
                }

            } catch (DPDException $e) {

                Log::instance()->add(Log::WARNING, $e->getMessage());
            }

            $return['zone'] = Model_Zone::NAME_REGION;;
            $return['price'] = empty($min_param['cost']) ? FALSE : $min_param['cost'];

            $data = ['door' => $door, 'terminal' => $terminal, 'closest' => $closest_term, 'min_param' => $min_param];
            $return['ship_date'] = View::factory('smarty:cart/ship_date', $data)->render();
            $return['ship_time'] = View::factory('smarty:cart/ship_time', $data)->render();
        }

        $this->return_json($return);
    }

    /**
     * вычисление интервалов доставки в зависимости от даты и зоны
     */
    public function action_time()
    {
        $zone = $this->request->query('zone');
        if ( ! $zone) $this->return_error('Zone required');
        $zone = new Model_Zone($zone);
        if ( ! $zone->loaded() || ! $zone->active) $this->return_error('Zone required');

        $date = $this->request->query('date');
        if ( ! $date || ! strtotime($date)) $this->return_error('Date required');

        $cart = Cart::instance();
        $cart->recount();

        exit(View::factory('smarty:cart/ship_time', $cart->allowed_time($zone, $date, $this->request->query('latlong')))->render());
    }
    
    /**
     * вічисление стоимости доставки Озон
     */
    public function action_ozon_price()
    {
        $deliveryId = $this->request->post('delivery_id');
        $cart = Cart::instance();
        $ozon = new OzonDelivery();
        $return['settings'] = $ozon->calculate_price($deliveryId, $cart->weight());
        $data = [];
        if(isset($return['settings']['price'])) {
            $data = ['ozon_delivery_price' => $return['settings']['price']];
            $return['html'] = View::factory('smarty:cart/ship_date', $data)->render();
        } else {
            $return['html'] = $return['settings']['warning'];
        }
        
        exit(json_encode($return));        
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
            'images'  => Model_Good::many_images([255], $ids),
            'short' => 1,
			'ga_ajax' => TRUE,
			'ga_list' => 'homepage'
        ))->render());
    }
    
    /**
     * Показ слайда от слайдера товарами, принимает тип слайдера, номер страницы, число товаров на странице
     */
    public function action_slide()
    {
        $page = intval($this->request->query('page'));
        $type = $this->request->param('type');
        $param = $this->request->param('param');

        $q = ORM::factory('good')               // готовим запрос - только товары возможные к покупке
            ->where('good.show', '=', 1)
            ->where('good.active', '=', 1)
            ->where('good.price', '>', 1)
            ->where('good.qty', '!=', 0)
            ->join('z_section')
                ->on('good.section_id', '=', 'z_section.id')
                ->where('z_section.active', '=', 1)
                ->where('z_section.vitrina', '=', Kohana::$server_name)
            ->join('z_group')
                ->on('good.group_id', '=', 'z_group.id')
                ->where('z_group.active', '=', '1')
            ->join('z_brand')
                ->on('good.brand_id', '=', 'z_brand.id')
                ->where('z_brand.active', '=', '1')
            ->order_by('good.popularity', 'DESC')
            ->limit(100);
        
        switch ($type) {

            case 'new':
                $q->where('good.new', '=', '1');
                break;

            case 'superprice':
                $q->join('z_good_prop')
                    ->on('good.id', '=', 'z_good_prop.id')
                    ->where('z_good_prop.superprice', '=', '1');
                break;

            case 'cart_set':
                $slider_id = intval($this->request->param('param'));
                $q->join('z_set_good')
                    ->on('z_set_good.good_id', '=', 'good.id')
                    ->where('z_set_good.set_id', '=', $slider_id);
                break;

            case 'cart2_set':
                $promos = [];
                $goods = Cart::instance()->recount();

                foreach ($goods as $g) $promos = array_merge($promos, $g->get_promos());
                if (empty($promos)) exit();

                $q->join('z_promo_good')
                    ->on('z_promo_good.good_id', '=', 'good.id')
                    ->where('z_promo_good.promo_id', 'IN', $promos);
                break;

            case 'promo':
                $promo_id = intval($this->request->param('set_id'));
                $q->join('z_promo_good')
                    ->on('z_promo_good.good_id', '=', 'good.id')
                    ->where('z_promo_good.promo_id', '=', $promo_id);
                break;

            case 'sale':
                $q->where('good.old_price', '>', 0);
                break;

            case 'pampers':
                $q  ->where('good.brand_id', '=', Model_Good::PAMPERS_BRAND)
                    ->where('good.section_id', '=', Model_Good::PAMPERS_SECTION);
                break;

            default: // rr- слайдеры тут

                if (strpos($type, 'rr-') !== 0) throw new HTTP_Exception_404;
                $rr = substr($type, 3);
                if ( ! method_exists('rrapi', $rr)) throw new HTTP_Exception_404;

                if (in_array($rr, ['CrossSellItemToItems', 'RelatedItems'])) {
                    $param = explode('_', $param);
                }
                $goods = rrapi::$rr($param);
                if ( ! $goods) exit();

                $q->where('good.id', 'IN', $goods);

                break;
        }
        $incart = Cart::instance()->goods;
        if ( ! empty($incart)) {
            $q->where('good.id', 'NOT IN', array_keys($incart));
        }
        if ( ! empty($rr)) {
            $order = $goods; // массив ид товаров в исходном порядке
        }

        $goods = $q->find_all()->as_array('id');

        if (empty($goods)) exit();

        if ( ! empty($rr)) { // собираем результат в том же порядке, в каком был результат запроса
            $ordered = [];
            foreach($order as $id) {
                if ( ! empty($goods[$id])) {
                    $ordered[$id] = $goods[$id];
                }
            }
            $goods = $ordered;
        }

        $page_ids = Txt::cycle_page($page, 5, array_keys($goods));

        $title = $this->request->query('t');

        $data = [
            'short' => 1,
            'style' => $this->request->param('type'),
            'goods' => Arr::extract($goods, $page_ids),
            'price' => Model_Good::get_status_price(1, $page_ids),
            'images'    => Model_Good::many_images([255], $page_ids),
            'ga_ajax'   => TRUE,
            'total'     => count($goods),
            'rel'       => '/slide/'.$type.($param ? '/'.implode('_', is_array($param) ? $param : [$param]) : ''),
            'ga_list'   => 'homepage'
        ];

        if ($title) { // rr slider - first query
            $view = 'retail_rocket/slider';
            $data['name'] = $title;
        } else {
            $view = 'common/goods';
        }
        Cookie::get('rrpusid');

        exit(View::factory('smarty:'.$view, $data)->render());
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

        exit(View::factory('smarty:product/view/tiles', [
            'goods' => ORM::factory('good')
                ->where('id', 'IN', $visible_good_ids)
                ->limit(10)
                ->offset(0)
                ->find_all()
                ->as_array(),
            'images'  => Model_Good::many_images([255], $visible_good_ids),
            'price' => Model_Good::get_status_price(1, $visible_good_ids),
        ])->render());
        
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
                    $txt .= '<p><strong>'.$k.'</strong><br />'.( is_array($v)? var_export($v,true) : $v ).'</p>';
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
     * Получить 5 хитов продаж по категории
     * Если хита нет в наличии, он заменяется на подменный или на другой реальный хит из этой категории
     */
    public function action_hitz()
    {
        if ( ! ($section_id = $this->request->param('section_id'))) throw new HTTP_Exception_404;

        $hitz = Model_Good::get_hitz($section_id); // получаем все проставленные хиты

        $goodidz = [];
        foreach($hitz as $k => $good) $goodidz[] = $good->id;

        $view = View::factory('smarty:common/goods', array(
			'goods' => $hitz, 
			'short' => 1,
			'ga_ajax' => TRUE,
			'ga_list' => 'homepage'
		));

        if ($goodidz) {
            $view->price = Model_Good::get_status_price(1, $goodidz);
            $view->images = Model_Good::many_images([255], $goodidz);
        }

        $this->return_html($view->render());
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
                
        $is_quickview = $this->request->post('is_quickview');

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
                $goods = $group->goods->find_all()->as_array('id');
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

        $view->set([
            'page'      => $page,
            'params'    => $params,
            'comments'  => $reviews,
            'is_quickview' => $is_quickview,
            'votes'     =>  Session::instance()->get('votes')
        ]);

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

					$sphinx = new Sphinx('section', $query, FALSE);
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

								$_sphinx = new Sphinx('section', $sectionId, FALSE);
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

					$sphinx = new Sphinx('section', $query, FALSE);
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

								$_sphinx = new Sphinx('section', $sectionId, FALSE);
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

    /**
     * Cлияние корзин
     * @throws Kohana_Exception
     */
    public function action_cart_merge()
    {
		$old_ids = $this->request->post('old_session');

		$Session = Session::instance();
		$sessId = $Session->id();
		
		if ( ! empty($old_ids) && is_array($old_ids)) {
			
			foreach($old_ids as $old_id ){
				
				if (empty($old_id)) continue;

				$old_session = ORM::factory('session', $old_id);

				if (empty($old_session->id)) continue;

				$odata = unserialize($old_session->data);
				$Cart = Cart::instance();
				$Cart->add($odata['cart']->goods);
				$Cart->save();

				$old_session->delete();
				
				// $OldCart->clean(); // Чистим старую корзину
			}

            exit('ok');
		}
	}

    /**
     * выдать список вариантов товаров на вводимое слово
     */
    public function action_search_suggestion()
    {
	$q = Txt::words($this->request->post('q')); // все слова
	if (empty($q)) exit('Нет вариантов');

        // последнее слово ищем лайком
        $last_word = $q[count($q) - 1];
        $ru_en = Txt::en_ru($last_word);

        $like = DB::select('keyword')
            ->from('z_suggest')
            ->where_open()
                ->where('keyword', 'LIKE', $ru_en[0].'%');

        if ( ! empty($ru_en[1])) {
            $like
                ->or_where('keyword', 'LIKE', $ru_en[1] . '%');
        }
        $like = $like->where_close()
            ->order_by('freq', 'DESC')
            ->limit(1)
            ->execute()
            ->get('keyword', 0);

        if ( ! empty($like)) { // слово есть

            $q[count($q) - 1] = $like;

        } else { // слова нет - пробуем править в нём опечатку

            $correct = Sphinx::correct($last_word);
            if ($correct) $q[count($q) - 1] = $correct;

        }

        // теперь ищем все слова обычным поиском по товарам
        $_sphinx = new Sphinx('suggest', implode(' ', $q));
		$goods = $_sphinx->suggestion();

        exit($goods);
	}

    /* изменить город (по нему считаем и показываем доставку) */
    public function action_city()
    {
        $view = View::factory('smarty:user/city');

        if ($this->request->post('save_city')) {
            Session::instance()->set('city', $this->request->post('city'));
            $this->return_reload();
        }

        exit($view->render());
    }

    /**
     * Очистка корзины
     * @throws View_Exception
     */
    public function action_cart_clear()
    {
        Cart::instance()->clean();
        // $this->tmpl['sync'] = 'cart';

        $this->return_reload();
    }

    /**
     * Форма уведомления о поставке
     * @throws HTTP_Exception_404
     */
    public function action_warn()
    {
        $good = ORM::factory('good', $this->request->param('id'));
        if ( ! $good->loaded()) throw new HTTP_Exception_404;

        $m = new Model_Good_Warn();
        $m->good_id = $good->id;
        if ($this->user) $m->user_id = $this->user->id;

        $view = View::factory('smarty:product/warn')->bind('i', $m)->set('good', $good);

        if ($this->request->post('send')) {

            $m->values($this->request->post());

            $captcha = $this->user ? TRUE : Captcha::check($this->request->post('captcha'));

            if ($m->validation()->check() AND $captcha) {

                $m->save();
                $view->sent = TRUE;
                $this->return_html($view->render());

            } else {

                $errors = $m->validation()->errors('product/warn');
                if ( ! $captcha) $errors['captcha'] = Kohana::message('captcha', 'captcha.default');
                $this->return_error($errors);

            }
        }
        exit($view->render());
    }

    /**
     * сохранение состояния меню в категориях в сессию
     */
    public function action_toggle()
    {
        $mode = $this->request->post('mode');
        $query = $this->request->post('query');
        $rel = $this->request->post('rel');
        $section = $this->request->post('section');

        $state_json = Session::instance()->get('toggle_state');
        $state = json_decode($state_json, TRUE);
        if (empty($state)) $state = [];

        if ( ! empty($section) && ! empty($rel)) { // состояние для раздела категории - общее (для нескольких mode)
            if ( ! empty($state[$section][$rel])) {
                unset($state[$section][$rel]);
            } else {
                $state[$section][$rel] = TRUE;
            }
        } elseif ( ! empty($mode) && ! empty($query) && ! empty($rel)) {
            if ( ! empty($state[$mode][$query][$rel])) {
                unset($state[$mode][$query][$rel]);
            } else {
                $state[$mode][$query][$rel] = TRUE;
            }
        }
        Session::instance()->set('toggle_state', json_encode($state));
        exit('ok');
    }

    public function action_arbuz()
    {
        if ($this->request->post('name') && $this->request->post('email') && $this->request->post('telephone')) {
            $mail = new Mail();
            $mail->setHTML(sprintf('<dl><dt>Имя</dt><dd>%s</dd><dt>Email</dt><dd>%s</dd><dt>Телефон</dt><dd>%s</dd></dl>',
                $this->request->post('name'), $this->request->post('email'), $this->request->post('telephone')), FALSE);
            $mail->send('0099060msk@gmail.com, m.zukk@ya.ru, zakaz@mladenec.ru, request@mladenec.ru, a.sergeev@mladenec.ru', 'Арбузная форма!');
        }
        exit('ok');
    }

    /**
     * Автокомплит для города - свой, дает код региона и id города для точного расчета
     */
    public function action_city_suggest()
    {
        $city = $this->request->query('query');
        $suggs = DB::select('city.id', ['city.name', 'cname'], ['region.name', 'rname'])
            ->from('city')
            ->join('region')
                ->on('region.id', '=', 'city.region_id')
            ->where('city.name', 'LIKE', $city.'%')
            ->order_by('city.name')
            ->order_by('region.name')
            ->limit(30)
            ->execute()
            ->as_array();

        $return['suggestions'] = [];
        if ($suggs) {
            foreach ($suggs as $n => $sugg) {

                $val = $sugg['cname'];
                if (( ! empty($suggs[$n - 1]['cname']) && $suggs[$n - 1]['cname'] == $val)
                    || ( ! empty($suggs[$n + 1]['cname']) && $suggs[$n + 1]['cname'] == $val)
                ) {
                    $val .= ', '.$sugg['rname'];
                }

                $return['suggestions'][] = [
                    'value' => $val,
                    'data'  => $sugg['id'],
                ];
            }
        }
        $this->return_json($return);
    }

    /**
     * Автокомплит при заполнении адреса
     */
    public function action_ya_autocomplete()
    {
        $city = $this->request->query('city');
        $street = $this->request->query('street');

        $suggs = yadost::autocomplete($this->request->query('query'), ! empty($city) ? ( ! empty($street) ? 'house' : 'street') : 'locality', $city, $street);
        $return['suggestions'] = [];
        if ($suggs) {
            foreach ($suggs as $sugg) {
                $return['suggestions'][] = $sugg->value;
            }
        }
        $this->return_json($return);
    }

    /**
     * получить варианты доставки от Яндекса
     */
    public function action_delivery_door_ya()
    {
        $cart = Cart::instance();

        $addr_id = $this->request->query('address_id');

        if (empty($addr_id)) throw new HTTP_Exception_404; // нет адреса
        $addr = new Model_User_Address($addr_id);
        if ( ! $addr->loaded()) throw new HTTP_Exception_404;
        if ($addr->user_id != $this->user) throw new HTTP_Exception_404; // чужой адрес
        $city = $addr->city;

        $variants = yadost::searchDeliveryList($city, $cart->weight, $cart->height, $cart->width, $cart->length, $cart->get_total());

        $return = [];
        if ( ! empty($variants)) {
            foreach ($variants as $k => $v) {
                $return[$k]['tariffName'] = $v->tariffName;
                $return[$k]['tariffId'] = $v->tariffId;
                $return[$k]['cost'] = $v->cost;
            }
        }
        //var_dump($variants);
        $this->return_json($return);
    }

    /**
     * получить варианты доставки от DPD
     * @TODO сделать city_id обязательным
     */
    public function action_delivery_door_dpd()
    {
        $cart = Cart::instance();
        $addr_id = $this->request->query('address_id');

        if (empty($addr_id)) throw new HTTP_Exception_404; // нет адреса
        $addr = new Model_User_Address($addr_id);
        if ( ! $addr->loaded()) throw new HTTP_Exception_404;
        if ($addr->user_id != $this->user) throw new HTTP_Exception_404; // чужой адрес
        $city_id = $addr->city_id;

        $return = [];
        try {

            $dpd = new DpdSoap(); // запрос цены доставки в dpd
            $variants = $dpd->ship_price($city_id, $cart->get_total(), $cart->weight, $cart->volume());

            if ( ! empty($variants)) {
                foreach ($variants as $k => $v) {

                    $return[$k]['tariffName'] = 'DPD'; //$v->tariffName;
                    $return[$k]['tariffId'] = $v->serviceCode;
                    $return[$k]['cost'] = ceil(floatval($v->cost));
                    $return[$k]['days'] = $v->days;
                }
            }

            /*
            if ( ! $cart->ship_wrong && empty($cart->big) && ! empty($city_name)) { // в заказе кгт или кривые данные о размере-весе или нет города - не надо считать доставку



                $city = new Model_City($city_id);
                if ( ! $city->loaded()) { // не нашли город по id, поищем по названию
                    $parts = array_filter(array_map('trim', explode(',', $city_name))); // название города может быть составным, с областью или районом
                    $city = ORM::factory('city')->where('name', 'IN', $parts)->find();
                }
                if ($city->loaded()) { // есть город - запрос по id
                } elseif ( ! empty($city_name)) {  // нет города - запрос по последней части, типа Республика Крым, Керчь
                    $door = $dpd->ship_price($parts[count($parts) - 1], $cart->total, $weight, $volume);
                }

                // ищем ближайший терминал
                $reverse_latlong = implode(' ', array_reverse(explode(',', $latlong)));

                $closest_term = DB::select()
                    ->from('terminal')
                    ->where('latlong', 'LIKE', '%,%')
                    ->order_by(DB::expr("geodist_pt(GeomFromText('Point(" . $reverse_latlong . ")'), point)"))
                    ->limit(1)
                    ->execute()
                    ->as_array();

                $closest_term = $closest_term[0];

                $terminal = $dpd->ship_price($closest_term['city_id'], $cart->total, $cart->weight(), $cart->volume(), FALSE);

                if ( ! empty($terminal)) {
                    foreach ($terminal as &$v) {
                        $v->cost = ceil(floatval($v->cost));
                        if ( ! isset($min_param['cost'])) { // лучшая цена или время только если нет до двери
                            $min_param = ['days' => $v->days, 'cost' => $v->cost, 'dt' => 'T'];
                        }
                    }
                }
                if (empty($min_param['cost'])) { // не определили цену
                    $min_param = ['cost' => 0, 'dt' => ''];
                }
            }

             $data = ['door' => $door, 'terminal' => $terminal, 'closest' => $closest_term, 'min_param' => $min_param];
            */

        } catch (DPDException $e) {

            Log::instance()->add(Log::WARNING, $e->getMessage());
        }

        //$return['dpd'] = View::factory('smarty:cart/ship_date', $data)->render().View::factory('smarty:cart/ship_time', $data)->render();
        $this->return_json($return);
    }


    /**
     * Заведение нового адреса (из корзины)
     */
    public function action_new_address()
    {
        if ( ! $this->user) throw new HTTP_Exception_404;
        $address = new Model_User_Address();
        $address->values($this->request->post());
        $address->user_id = $this->user->id;

        $return = [];
        if ($address->validation()->check()) {
            $address->save();
            $return = [
                'id' => $address->id,
                'name' => Txt::addr($address),
                'correct_addr' => $address->correct_addr,
                'latlong'   => $address->latlong,
                'zone_id'   => $address->zone_id,
            ];

        } else {
            $return['error'] = $address->validation()->errors('user/address');
        }

        $this->return_json($return);
    }
}