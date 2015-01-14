<?php
class Controller_User extends Controller_Frontend
{
	protected function _subscribe_api( $action, $params = array(), $session = '', $request_id = 0 ){

		$params['one_time_auth'] = array(
			'login' => 'mladenec',
			'sublogin' => 'mladenec',
			'passwd' => 'voo3Gal'
		);

		if( empty( $request_id ) ){

			$request_id = rand(100,1000000);
		}

		$curl = new Curl();
		
		$returner = json_decode($curl->get_url('https://pro.subscribe.ru/api' . $session, array(
			'apiversion' => 100,
			'json' => 1,
			'request.id' => $request_id,
			'request' => json_encode(array_merge(array(
				'action' => $action
			), $params ))
		)), true );
		
		if( !empty( $returner['REDIRECT'] ) ){

			$returner = $this->_subscribe_api($action, $params, $returner['REDIRECT']);
		}

		return $returner;
	}
	
    /**
     * Clean external session data.
     */
    public function action_external_clean()
    {
        $account = Session::instance()->get('external');
        Session::instance()->set('external', NULL)->write();
        die(json_encode(array('clean' => ( ! empty($account)))));
    }

    // регистрация
    public function action_register(){
		
        if ( !$this->request->post('ajax')) {
            $this->request->redirect('/?reg'); // на главную с открытой формой регистрации
        }

		$mode = $this->request->post('mode');
		
		if( !in_array( $mode, ['cart', 'general'] ) ){
			$mode = 'general';
		}
		
        $user = new Model_User();

        if ($this->request->post()){
            $post = $this->request->post();
            if (isset($post['email'])) $post['email'] = trim($post['email']); // у мыла режем пробелы

            $v = $user->validation()->copy($post)
                ->rule('password', 'not_empty')
                ->rule('password', 'min_length', array(':value', 6))
                ->rule('password2', 'not_empty')
                ->rule('password', 'matches', array(':validation', 'password', 'password2'))
                ->rule('email', array(ORM::factory('user'), 'unique'), array('email', ':value'));

            if ($v->check()){
				
                $salt = Text::random();

                $user->values(array(
                    'name'      => $v['name'],
                    'login'     => $v['email'],
                    'email'     => $v['email'],
                    'password'  => $salt.md5($salt.$v['password']),
                    'phone'     => $v['phone'],
                    'created'   => time(),
                    'sub'       => 1, // подписка на рассылку - по умолчанию
                ))->create();

				$this->_subscribe_api('member.set', array(
					'email' => $v['email'],
					'addr_type' => 'email',
					'newbie.confirm' => '0',
					'if_exists' => 'error',
					'source' => Request::$client_ip
				));
				
                Mail::htmlsend('register', array('user' => $user, 'passwd' => $v['password']), $v['email'], 'Добро пожаловать!');
				
                Model_User::login($user);

                $refer = $this->request->referrer();
                if ($has_q = strpos($refer, '?')) {
                    $refer = substr_replace($refer, '?reg_done='.$user->id.'&', $has_q, 1);
                } else {
                    $refer .= '?reg_done='.$user->id;
                }

                if ( ! empty($post['poll_id'])) {
                    $poll = ORM::factory('poll',$post['poll_id']);
                    if ($poll->loaded()) {
                        $ok = $poll->vote_handler($user->id);
                    }
                }
				
				if( $mode == 'cart' ){
					$this->return_json( [ 
						'delivery' => Controller_Product::cart_delivery(),
						'userpad' => $this->userpad()
					] );
				} else{
	                $this->return_redirect($refer);
				}
            }
            else
            {
                $this->return_error($v->errors('user'));
            }
        };
    }

    /**
     * Проброс отсылки из pro.subscribe
     */
    public function action_unsubscribe_pro()
    {
		
		$email = $this->request->param('email');
		
		$this->tmpl['result'] = 'Отписан ' . $email;
		
		Model_User::unsubscribe($email);
	}
	
    /**
     * Отписка мыла от рассылки
     */
    public function action_unsubscribe()
    {
        $mail = $this->request->query('mail');
        $check = $this->request->query('check');

        if (md5(Cookie::$salt.$mail) != $check) throw new HTTP_Exception_404;

        if ($this->request->post('do')) { // был пост, отпишем адрес

            $reason = trim($this->request->post('reason'));
            if ( ! empty($reason)) Model_Spam::why($reason);
            Model_User::unsubscribe($mail);

			$ee = $this->_subscribe_api('member.delete', array('email' => $mail));
				
            $this->tmpl['done'] = TRUE;

        } else { // первый приход - проверить подписан дли вообще адрес?

            $this->tmpl['subscribed'] = ORM::factory('user')->where('email', '=', $mail)->where('sub', '=', 1)->count_all();

        }

        $this->layout->menu = Model_Menu::html();
    }
    /**
     * Залогинивание юзера
     */
    public function action_login()
    {
		$json = false;
		
		$mode = $this->request->post('mode');
		
		if( !in_array( $mode, ['cart', 'general'] ) ){
			$mode = 'general';
		}
		
        if (($l = $this->request->post('login')) AND ($p = $this->request->post('password'))
        ) {
			$u = Model_User::login(array('login' => $l, 'password' => $p, 'remember' => $this->request->post('remember')));

			if( $u ){
				
				$Session = Session::instance();
				$cart = $Session->get('cart');
				
				$session_id = Session::instance()->id();
				
				$old_sessions = ORM::factory('session')->where('user_id', '=', $u->id)->where('id', '!=', $session_id)->find_all()->as_array('id');
				
				if( !empty( $old_sessions ) ){

					$old_goods = array();
					
					foreach( $old_sessions as $key => $sess ){
						
						$old_data = unserialize($sess->data);
						$old_goods = $old_goods + $old_data['cart']->goods;
					}
					
					if( !empty( $old_goods ) ){

						if( empty( $cart->goods ) ){
							
							$Cart = Cart::instance();
                            
							$Cart->add($old_goods);
                            
							$Session->set('cart', $Cart)->write();
							
							$json = ['redirect' => $this->request->referrer()];
						}
						else{

							$ogoods = ORM::factory('good')->where('id', 'IN', array_keys( $old_goods ))->find_all()->as_array('id');
							$json = ['fancybox' => View::factory('smarty:admin/user/merge_carts', array( 'old_sessions' => array_keys( $old_sessions ), 'old_goods' => $ogoods, 'old_goods_counts' => $old_goods, 'user' => Model_User::current()))->render(), 'redirect' => $this->request->referrer()];
						}
					}
					else{
						$json = ['redirect' => $this->request->referrer()];
					}
				}
				else
					$json = ['redirect' => $this->request->referrer()];
			}
        }
		
		if( !empty( $json ) ){
			
			if( $mode == 'cart' ){
				$json = [ 'delivery' => Controller_Product::cart_delivery() ];
			}
			
			if( empty( $json['redirect'] ) )
				$json['userpad'] = $this->userpad();
			
			$this->return_json( $json );
		}

        $this->return_error(array('login' => 'Неправильный логин или пароль', 'password' => 'Неправильный пароль или логин'));
    }
	
	/**
	 * html плашки в шапке
	 * @return type
	 */
	public static function userpad(){
		return View::factory('smarty:averburg/user/userpad', ['user' => Model_User::current()] )->render();
	}
	
    /**
     * Разлогинить юзера
     */
    public function action_logout()
    {
        $this->tmpl['user'] = $this->user = Model_User::logout();
        $this->request->redirect();
    }

    /**
     * Личный кабинет юзера
     * @throws HTTP_Exception_403
     */
    public function action_view()
    {
        if ( ! $this->user) throw new HTTP_Exception_403;

        if ($this->request->post()) { // пришла форма с данными юзера
            try
            {
                $this->user->values($this->request->post());
                $this->user->sub = ($this->request->post('sub') ? 1 : 0);
                $this->user->save();

                if ($this->user->changed()) {
                    Model_History::log('user', $this->user->id, 'edit', $this->user->as_array()); // запоминаем в истории
                }

            } catch (ORM_Validation_Exception $e) {
                $this->return_error($e->errors('user'));
            }
            $this->return_html('Ваши данные успешно изменены <a href="/account" class="ok">Сменить снова</a>');
        }

        $this->tmpl['user'] = $this->user;
    }

    /**
     * Список заказов пользователя
     * @throws HTTP_Exception_403
     */
    public function action_orders()
    {
        if ( ! $this->user) throw new HTTP_Exception_403;

        $orders = ORM::factory('order')->where('user_id', '=', $this->user->id)->reset(FALSE);

        $this->tmpl['pager'] = $pager = new Pager($orders->count_all());
        $this->tmpl['orders'] = $orders
            ->order_by('id', 'DESC')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all();
    }

    /**
     * Список адресов пользователя
     * @throws HTTP_Exception_403
     */
    public function action_address()
    {
        if ( ! $this->user) {
            throw new HTTP_Exception_403;
        }

        if ($this->request->post('ajax') && ($del_addr = $this->request->post('address_id'))) { // запрос на удаление адреса
            $address = ORM::factory('user_address', $del_addr);
            if ( ! $address->loaded()) $this->return_reload(); // нет адреса
            if ($address->user_id != $this->user->id) $this->return_reload(); // чужой адрес
            $address->active = 0;
            $address->save();
            $this->return_reload();
        }

        $address = ORM::factory('user_address')
                        ->where('user_id', '=', $this->user->id)
                        ->where('active', '=', 1)
                        ->reset(FALSE); // показ адресов юзера

        $this->tmpl['pager'] = $pager = new Pager($address->count_all());
        $this->tmpl['address'] = $address
            ->order_by('id', 'DESC')
            ->offset($pager->offset)
            ->limit($pager->per_page)
            ->find_all();
    }

    /**
     * Ребенок пользователя
     * @throws HTTP_Exception_403
     */

    public function action_children()
    {
        if ( ! $this->user ) throw new HTTP_Exception_403;

		$user_kids = $this->user->kids->find_all()->as_array('id');

        if ($post = $this->request->post()) // Пришла форма с данными ребенка
		{
            $_errors = array();
			foreach ($user_kids as $id => $kid) { //существующие дети
                $kid->values(array(
                    'sex' => isset($post['sex'][$id]) ? $post['sex'][$id] : 0,
                    'birth' => isset($post['birth'][$id]) ? $post['birth'][$id] : 0,
                    'name' => isset($post['name'][$id]) ? $post['name'][$id] : '',
                ));
                $v = $kid->validation();
                if ( ! $v->check()) {
                    $_errors[''][$id] = $v->errors('user/child');
                } else {
                    $kid->save();
                }
            }
            foreach ($post['new_name'] as $id => $new_name) { // новые дети
                if ( ! empty($new_name) || ! empty($post['new_birth'][$id])) {
                    $kid = new Model_User_Child();
                    $kid->user_id = $this->user->id;
                    $kid->values(array(
                        'sex' => isset($post['new_sex'][$id]) ? $post['new_sex'][$id] : null,
                        'birth' =>  isset($post['new_birth'][$id]) ? $post['new_birth'][$id] : null,
                        'name' => $new_name,
                    ));
                    $v = $kid->validation();
                    if ( ! $v->check()) {
                        $_errors['new_'][$id] = $v->errors('user/child');
                    } else {
                        $kid->save();
                    }
                }
            }

            if (count($_errors)) {
                $return_errors = array();
                foreach($_errors as $type => $child_errors) {
                    foreach($child_errors as $id => $form_errors) {
                        foreach($form_errors as $field => $error) {
                            $return_errors[$type.$field.'['.$id.']'] = $error;
                        }
                    }
                }
                $this->return_error($return_errors);
            } else {
                $this->return_reload();
            }
        }

		$this->tmpl['sexes'] = array_combine(Model_User_Child::$SEX, Model_User_Child::$SEX_CAPTION); // варианты пола
        $this->tmpl['children']	 = $user_kids; // дети
    }

    /**
     * Личный кабинет юзера
     * @throws HTTP_Exception_403
     */
    public function action_action()
    {
        if ( ! $this->user) throw new HTTP_Exception_403;

        $actions = ORM::factory('action')
                ->where('count_from', 'IS NOT', NULL)
                ->where('active', '=', 1)
                ->order_by('name','ASC')
                ->find_all()->as_array();
        if ( ! empty($actions)) {
            $credits = DB::select('action_id','sum','qty')
                    ->from('z_action_user')
                    ->where('user_id',   '=',  $this->user->pk())
                    ->where('action_id', 'IN', $actions)
                    ->execute()->as_array('action_id');
            $this->tmpl['credits'] = $credits;
            $this->tmpl['actions'] = $actions;
        } else {
            $this->tmpl['credits'] = array();
            $this->tmpl['actions'] = array();
        }
        
    }
    
    /**
     * Личный кабинет юзера
     * @throws HTTP_Exception_403
     */
    public function action_reviews()
    {
        if ( ! $this->user) throw new HTTP_Exception_403;

		$good = new Model_Good();

		$reviews = ORM::factory('good_review')
			->where('user_id', '=', $this->user->id)
			->with('author')
			->order_by('time', 'DESC')
			->find_all()
			->as_array('id');
		
		$goodsIds = array();
		foreach( $reviews as &$review ){
		
			$goodsIds[] = $review->good_id;
		}
		unset( $review );
		
		$goods = [];
		if( !empty( $goodsIds ) ){
			
			$goods = ORM::factory('good')
				->where('id', 'in', $goodsIds)
				->find_all()
				->as_array('id');
		}

		$this->tmpl['comments'] = $reviews;
		$this->tmpl['goods'] = $goods;
    }
    
    /**
     * Удаление ребенка 
     * @throws HTTP_Exception_403
     */

    public function action_child_delete()
    {
        if ( ! $this->user ) throw new HTTP_Exception_403;

        $child = ORM::factory('user_child')
            ->where('id', '=', $this->request->param('id'))
            ->where('user_id', '=', $this->user->id )
            ->find();

        if ( $child->loaded()) $child->delete();

        $this->return_redirect(Route::url('user_child'));
    }
	
    /**
     * Старый заказ юзера
     *
     * @throws HTTP_Exception_403
     * @throws HTTP_Exception_404
     */
    /* public function action_cart()
    {
        $order_id = $this->request->param('id');
        if (empty($order_id)) throw new HTTP_Exception_404;
        if (empty($this->user)) throw new HTTP_Exception_403;
        $this->tmpl['user'] = $this->user;

        $order = new Model_Order($order_id);
        if ( ! $order->loaded()) throw new HTTP_Exception_404;
        if ($order->user_id != $this->user->id)  throw new HTTP_Exception_403;

        $this->tmpl['order_goods'] = $order->get_goods();

        if ($this->request->param('thanx')) { // это спасибо-страница?

            if (Session::instance()->get('thanx') != $order_id) $this->request->redirect(Route::url('order_detail', array('id' => $order_id))); // если нет в сесии - на просто страницу заказа
            Session::instance()->delete('thanx'); // ключ одноразовый зачистить

            $thanx = TRUE;
            View::bind_global('thanx', $thanx);

            $this->layout->o = $order;

            // show coolstat params here - to tell them about order
            $coolstat_params = array(
                'client_name' => $this->user->id,
                'code' => $this->request->cookie('coolstat_code'),
                'order_id' => $order->id,
                'sum' => round($order->get_total()),
                'site_id' => 20,
                'secret_key' => 'e558c7b98c42eea6fa82f2c16830a2e71e999f8ed0acdec99ecae9a961da57ba'
            );
            $coolstat_params['sign'] = hash('sha256', strtoupper(http_build_query($coolstat_params)));
            unset($coolstat_params['secret_key']);

            $this->tmpl['coolstathref'] = 'http://web-economica.ru/index.php?route=api/orders&task=add&'.http_build_query($coolstat_params);

            // check for PG goods to show for Channel Intelligence
            $pg_goods = array();
            foreach($order->get_goods() as $g) if ($g->upc) $pg_goods[] = $g; // they got upc

            $this->tmpl['pg_goods'] = $pg_goods;

            // get active polls to promote
            $polls = ORM::factory('poll')
                    ->where('active', '=', 1)
                    ->where('type', '=', Model_Poll::TYPE_ORDER_COMPLETE)
                    ->where('closed', '=', 0)
                    ->order_by('id', 'DESC')
                    ->find_all()->as_array();
            if ( ! empty($polls)) { // есть опросы
                $votes = Model_Poll::votes($this->user->id); // за что уже голосовал
                $new_user = ORM::factory('order')->where('user_id', '=', $this->user->id)->count_all() <= 1; // новый ли пользователь?
                $can_poll = FALSE;

                foreach($polls as $p) {
                    if ( ! empty($votes[$p->id])) continue; // уже голосовал тут
                    if ( ! $p->new_user || ($p->new_user && $new_user)) { // может быть ещё условие на новизну юзера
                        $can_poll = TRUE;
                        break;
                    }
                }
                $this->tmpl['can_poll'] = $can_poll;
            }
        }

        $this->tmpl['o'] = $this->tmpl['cart'] = $order;
        $this->tmpl['od'] = $order->data;
        $this->tmpl['coupon'] = $order->coupon_id ? $order->coupon : FALSE;
    } */

    public function action_order2(){
		
		$returner = [];
		
		$this->user = Model_User::current();
		
		$this->action_order();

		$returner['result'] = 'ok';
		
		exit(json_encode($returner));
	}

    /**
     * Оформление заказа - ввод адреса и т.п.
     *
     * @throws HTTP_Exception_403
     * @throws Kohana_Exception
     */
    public function action_order()
    {
        if (empty($this->user) && ! empty($this->external_account)) {
            $this->tmpl['user'] = (object) $this->external_account['info'];
        } elseif ( ! empty($this->user)) {
            $this->tmpl['user'] = $this->user;
        }
        
		$url_append = '';

        $this->tmpl['cart'] = $cart = Cart::instance();
        $this->tmpl['big_to_wait'] = $big_to_wait = $cart->big_to_wait(TRUE);
        $this->tmpl['dt'] =  Model_Order::SHIP_COURIER;// способ доставки по-умолчанию

        if (empty($cart->goods)) $this->return_redirect(Route::url('cart'));

        if ($this->request->post())
        {
            // Если пользователь до этого не был авторизован, но есть внешний акк
            if (empty($this->user))
            {
                $user = new Model_User();
                $arPost = $this->request->post();

                // Validate model by internal rules.
                $validation = $user->validation()->copy($arPost)
                    ->rule('email', array(ORM::factory('user'), 'unique'), array('email', ':value'));

                // Check validation rules.
                if ( ! $validation->check()) $this->return_error($validation->errors('order/order_data'));

                $salt = Text::random();     // Generate salt.
                $password = Text::random(); // Generate password.

                // Определение указания галки сохранения.
                $bSaveUser = $this->request->post('save_user');

                // Try create new user.
                $user->values(array(
                    'name'      => $validation['name'],
                    'last_name' => $validation['last_name'],
                    'login'     => $validation['email'],
                    'email'     => $validation['email'],
                    'password'  => $salt . md5($salt.$password),
                    'phone'     => $validation['phone'],
                    'created'   => time(),
                    'sub'       => 1, // подписка на рассылку - по умолчанию
                ))->create();

                Mail::htmlsend('register', array('user' => $user, 'passwd' => $password), $validation['email'], 'Добро пожаловать!');

                // Replace current user.
                $this->user = Model_User::login($user);

				$url_append = '?reg_done=' . $this->user->id;
				
                // Linkage user and account.
                if ( ! empty($this->external_account['info'])) {
                    Model_User_External::linkageUserAccount(
                        $this->external_account['source'],
                        $this->external_account,
                        $this->user->id
                    );
                }
            }

            $order_data = new Model_Order_Data();
            $order_data->values($this->request->post());
            $dt = $this->request->post('delivery_type');

            switch($dt) {
                case Model_Order::SHIP_COURIER: // доставка курьерской службой
                case Model_Order::SHIP_SERVICE: // доставка через транспортную компанию

                    if ($this->request->post('address_id')) { // старый адрес!
                        $addr = new Model_User_Address($this->request->post('address_id'));
                        if ( ! $addr->loaded()) throw new HTTP_Exception_404; // no address found
                        if ($addr->user_id != $this->user->id) throw new HTTP_Exception_403; // not current user address

                        $order_data->values($addr->as_array());
                        $mkad = intval($this->request->post('mkad'));

                        $addr->mkad = $mkad;
                        $addr->save();

                        $order_data->mkad = $addr->mkad;

                    } else {
                        $addr = new Model_User_Address();
                    }

                    $v = $order_data->validation();
                    foreach($addr->rules() as $f => $rules) $v->rules($f, $rules);
                    if ($dt == Model_Order::SHIP_SERVICE) {
                        Session::instance()->set('tarif_id', $this->request->post('tarif_id')); // remember chosen tarif for transport
                    }
                    break;

                case Model_Order::SHIP_SELF: // самовывоз
                    $v = $order_data->validation();
                    $v->rule('address_id', array('Model_Order', 'is_shop'));
                    break;

                default:
                    throw new ErrorException('Not valid delivery_type '.var_export($dt, TRUE));
            }

            if ($v->check())
            {
                // запомним данные пользователя, если стоит галка
                if ($this->request->post('save_user')) {

                    try {
                        $this->user->values(array(
                            'name' => $order_data->name,
                            'last_name' => $order_data->last_name,
                            'second_name' => $order_data->second_name,
                            'phone'  => $order_data->phone,
                            'phone2' => $order_data->phone2,
                            //'email' => $order_data->email  // we do not save email here to prevent changing it from here
                        ))->save();
                    } catch (ORM_Validation_Exception $e) {
                        Log::instance()->add(Log::ERROR, $e->getMessage().var_export($this->user->as_array(), true));
                    }
                }
                $od = $order_data->as_array() +
                    array(
                        'delivery_type' => $dt,
                        'address_id' => $this->request->post('address_id')
                    );

                Session::instance()->set('od', json_encode($od))->write();

                $this->return_redirect(Route::url('order_valid') . $url_append);
            } else {
                $this->return_error($v->errors('order/order_data'));
            }
        }
		else{
			Session::instance()->set('od_time', time())->write();
		}
    }

    /**
     * Страница подтверждения заказа пользователем
     */
    public function action_order_valid()
    {
        $od = Session::instance()->get('od');

        $cart = Cart::instance();
        $cart->recount();
        $this->tmpl['cart'] = $cart;
        
        if (empty($cart->goods)) $this->return_redirect(Route::url('cart'));

        $this->tmpl['order_goods'] = $goods = $cart->recount();
        $cart->check_actions($goods, TRUE);
        $this->tmpl['big_to_wait'] = $big_to_wait = $cart->big_to_wait(TRUE);

        if ( ! empty($od) AND ($odata = json_decode($od, 1)))
        {
            $o = new Model_Order();
            $od = new Model_Order_Data();
            $od->values($odata, array_keys($od->as_array()));

            $o->delivery_type = $odata['delivery_type'];
            $o->price = $cart->get_total();
            $o->discount = $cart->discount;

            if (in_array($o->delivery_type, array(Model_Order::SHIP_COURIER, Model_Order::SHIP_SERVICE))) { // доставка по адресу

                if (empty($odata['address_id'])) { // создаём новый адрес
                    $a = new Model_User_Address();
                    $a->values($odata);
                    $a->user_id = $this->user->id;
                }

                // вычисление стоимости доставки
                if ($o->delivery_type == Model_Order::SHIP_COURIER) {
                    $o->price_ship = $o->price_ship($od);
                }
                if ($o->delivery_type == Model_Order::SHIP_SERVICE) { // доставка через edost
                    $od->comment = '';
                    $o->price_ship = 0;

                    $tarif_id = Session::instance()->get('tarif_id');
                    $edost = Session::instance()->get('edost');

                    if ( ! empty($edost) AND ! empty($tarif_id)) {
                        if ($tarif = $edost[$tarif_id]) {
                            $od->comment = $tarif['company'].', '.$tarif['name'];
                            $o->price_ship = $tarif['price'];
                        }
                    }
                }

            }
            if ($o->delivery_type == Model_Order::SHIP_SELF) { // самовывоз
                $o->price_ship = 0;
                $od->address = Model_Order::is_shop($odata['address_id']);
            }

            if ($this->request->post('agree')) { // только если согласился
                $pt = $this->request->post('pay_type');

                if ($o->delivery_type == Model_Order::SHIP_SERVICE) {
                    $pt = Model_Order::PAY_CARD; // только картой

                } elseif ($o->delivery_type == Model_Order::SHIP_COURIER) { // картой или налом
                    if ( ! in_array($pt, array(Model_Order::PAY_DEFAULT, Model_Order::PAY_CARD))) $pt = Model_Order::PAY_DEFAULT; // по умолчанию - наличка

                } elseif($o->delivery_type == Model_Order::SHIP_SELF) {
                    $pt = Model_Order::PAY_DEFAULT; // только наличкой
                }

                $o->values(array(
                    'user_id' => $this->user->id,
                    'user_status' => $cart->status_id,
                    'description' => $this->request->post('description'),
                    'status' => $pt == Model_Order::PAY_CARD ? 'C' : 'N', // при оплате по карте - свой статус
                    'status_time' => date("Y-m-d H:i:s"),
                    'pay_type' => $pt,
                ));

				/**
				 * Проверка дат
				 */
				
				$setNextDate = false;
				$today_time = mktime(0, 0, 0);

				if( $od->ship_zone ){
					
					$Zone = ORM::factory('zone', $od->ship_zone);
					$dates = $Zone->allowed_date( $cart );
					list($first_date) = each( $dates );
				}
				else
					$first_date = date('Y-m-d',strtotime('tomorrow'));
				
				if( empty($od->ship_date) )
					$setNextDate = true;
				
				else{
					
					// Если это заказ из прошлого, то ставим первую дату из открытых
					$ship_time = strtotime( $od->ship_date );
					
					if( $ship_time < $today_time || ( $od->ship_date == date('Y-m-d') && time() >= $today_time + 43200 ) ){
						$setNextDate = true;
					}
				}
				
                if ($setNextDate){
					
					$od->ship_date = $first_date;
				}
				
				/**
				 * /Проверка дат
				 */

				$od_time = date('Y-m-d H:i:s', Session::instance()->get('od_time') );
				
				if( !empty( $od_time ) ){
					
					$o->created = $od_time;
				}
				
                $o->save();
                $o->save_goods($cart);

                $od->id = $o->id;

                if ( ! empty($a)) {
                    $a->save();
                    $od->address_id = $a->id;
                }
                $od->save();

                $cart->clean(); // чистка корзины!
                
                if ($pt == Model_Order::PAY_CARD) { // Переходим к оплате по карте
                    Session::instance()->set('order_id', $o->id); // у нас в сессии теперь висит заказ с незавершённой оплатой
                    $this->return_redirect(Route::url('payment'));
                } else {
                    /* Отправляем СМС о принятом заказе */
                    $o->send_sms_accepted();
                }
                $mail_params = array(
                    'o' => $o,
                    'od' => $od,
					'coupon' => $o->coupon_id ? $o->coupon : FALSE
                );
                if ( ! empty($big_to_wait)) $mail_params['big_to_wait'] = $big_to_wait;
                Mail::htmlsend('order', $mail_params, $od->email, 'Ваш заказ '.$o->id.' принят');
                
                Session::instance()->set('thanx', $o->id);
                $this->return_redirect(Route::url('order_detail', array('id' => $o->id, 'thanx' => 'thanx'))); // идём на страницу спасибо
            } else {
                $this->return_error(array('agree' => 'Для отправки заказа Вы&nbsp;должны согласиться с&nbsp;пользовательским соглашением.'));
            }

            $this->tmpl['od'] = $od;
            $this->tmpl['o'] = $o;

        } else {
            $this->return_redirect(Route::url('order')); // if no order data - back to data
        }

        if ($cart->presents) { // если есть подарки - получим
            $this->tmpl['presents']      = $cart->get_presents(FALSE, FALSE);
            $this->tmpl['present_goods'] = $cart->get_present_goods(FALSE, FALSE);
        }
        if ($cart->big) { // если есть крупногабаритка - получим
            $this->tmpl['big'] = ORM::factory('good')->where('id', 'IN', array_keys($cart->big))->find_all()->as_array('id');
        }
        $this->tmpl['blago'] = $cart->blago;
        $this->tmpl['coupon'] = ! empty($cart->coupon) ? new Model_Coupon($cart->coupon) : FALSE;

        $this->layout->title = 'Подтверждение заказа';
    }

    /**
     * Выдаёт название зоны заказа по Широте-Долготе (for ajax)
     */
    public function action_zone()
    {
        echo Model_Zone::locate($this->request->query('latlong'))->name;
    }

    /**
     * Обработка забытого пароля
     * @throws HTTP_Exception_404
     */
    public function action_forgot()
    {
        if ( ! empty($this->user))  $this->request->redirect(Route::url('user'));

        if ($this->request->is_ajax()) { // запрос кода восстановления

            $v = Validation::factory($this->request->post())
                ->rules('email', array(array('not_empty'), array('email')))
                ->rules('captcha', array(array('not_empty'), array('Captcha::check')))
            ;
            if ($v->check()) {
                $user = ORM::factory('user')->where('email', '=', $v['email'])->find();
                if ( ! $user->loaded()) $this->return_error(array('email' => 'Пользователь с таким e-mail не найден'));

                // Выслать письмо с кодом восстановления пароля
                $t = strtotime('+1 hour');
                $code = date('ymdH', $t);
                $time = date('d.m.y H:59:59', $t);

                $user->checkword = $code.md5($user->id.$user->email.$code.Cookie::$salt);
                $user->save();

                Mail::htmlsend('forgot', array('u' => $user, 'time' => $time), $user->email, 'Восстановление пароля');

                $this->return_html('<p>Ссылка для&nbsp;восстановления пароля выслана на&nbsp;адрес '.$user->email.
                    '</p><p>Внимание, данная ссылка действительна до&nbsp;'.$time.' по&nbsp;московскому времени</p>'
                );

            } else {

                $this->return_error($v->errors('user/forgot'));
            }
        }
    }

    /**
     * Смена пароля
     * @throws HTTP_Exception_404
     */
    public function action_password() {

        if ($code = $this->request->query('code')) { // юзер пришёл с кодом, проверить и залогинить

            if ( ! empty($this->user)) $this->request->redirect(Route::url('user')); // залогиненный так не может

            $user = ORM::factory('user')->where('checkword', '=', $code)->find();
            if ( ! $user->loaded()) throw new HTTP_Exception_404; // нет никого с таким кодом
            $valid_till = substr($code, 0, 8);
            if (md5($user->id.$user->email.$valid_till.Cookie::$salt) != substr($code, 8)) throw new HTTP_Exception_404; // код невалидный

            $this->user = $user; // как бы логиним его
            $this->tmpl['code'] = $code;
        }

        if (empty($this->user)) throw new HTTP_Exception_403; // тут уже запросим юзера если нету

        if ($this->request->post('reset_password')) { // постили пароль

            $v = Validation::factory($this->request->post())
                ->rule('password', 'not_empty')
                ->rule('password', 'min_length', array(':value', 6))
                ->rule('password2', 'not_empty')
                ->rule('password', 'matches', array(':validation', 'password', 'password2'));


            if ($code) { // старый пароль проверять не надо
                $oldpass = TRUE;
            } else {
                $user = ORM::factory('user')->where('id', '=', $this->user->id)->password($this->request->post('old_password'))->find();
                $oldpass = $user->loaded();
            }

            if ($v->check() AND $oldpass) {
                $salt = Text::random();
                $this->user->password = $salt.md5($salt.$v['password']);
                $this->user->save();
                $this->user->checkword = ''; // чистим код восстановления
                Model_User::login($this->user);

                $this->return_html('Ваш пароль успешно изменён. <a href="/account">Перейти в&nbsp;личный кабинет</a>');

            } else {

                $this->return_error($v->errors('user') +
                    ( ! $oldpass ? array('old_password' => Kohana::message('user', "old_password.default")) : array())
                );
            }

        }
    }

    /**
     * Откат заказа с незавершённой оплатой
     * Предлагаем юзеру перейти к оплате или закрыть заказ
     */
    public function action_back()
    {
        if (empty($this->user))  throw new HTTP_Exception_403;

        $order_id = Session::instance()->get('order_id');
        if (empty($order_id)) $this->request->redirect(Route::url('user')); // нет заказа в сессии

        $order = new Model_Order($order_id);
        if ( ! $order->loaded()) $this->request->redirect(Route::url('user')); // нет заказа
        if ($order->user_id != $this->user->id)  $this->request->redirect(Route::url('user')); // не его заказы
        if ($order->status != 'C') $this->request->redirect(Route::url('order_detail', array('id' => $order_id)));

        $this->tmpl['order'] = $order;
    }

    /**
     * Оплата по карте
     *
     * @throws HTTP_Exception_404
     * @throws HTTP_Exception_403
     */
    public function action_payment()
    {
        if (empty($this->user))  throw new HTTP_Exception_403;

        $order_id = Session::instance()->get('order_id');
        if (empty($order_id)) {
            Session::instance()->delete('order_id');
            $this->request->redirect($this->request->referrer());
        }

        $o = new Model_Order($order_id);
        if ( ! $o->loaded()) {
            Session::instance()->delete('order_id');
            $this->request->redirect($this->request->referrer());
        }

        if ($o->user_id != $this->user->id)  {
            Session::instance()->delete('order_id');
            $this->request->redirect($this->request->referrer());
        }

        // ищём, может уже платили по заказу
        $payment = new Model_Payment($o->id);

        switch($this->request->param('todo')) {

            case 'payment': // первый этам оплаты

                if ($payment->loaded()) {
                    if ($payment->status == Model_Payment::STATUS_New) {
                        $payment->pay(); // повторно пришёл на оплату - продолжим
                        return;
                    } else {
                        $res = $payment->status(); // письма юзеру и прочее - см. внутри status()
                        $this->tmpl['state'] = $res['State'];
                    }
                } else {
                    $payment->init($o);  // нет оплаты - начнём сеанс
                }

                break;

            case 'pay_success': // возврат от банка на страницу оплаты

                if ( ! $payment->loaded()) {
                    $e = 'Payment not found for order '.$o->id;
                    Log::instance()->add(Log::ERROR, $e);
                    exit($e);
                };

                $res = $payment->status(); // письма юзеру и прочее - см. внутри status()
                $this->tmpl['state'] = $res['State'];

                break;
        }

        $this->tmpl['payment'] = $payment;
        $this->tmpl['o'] = $o;
    }

    /**
     * Возврат с формы оплаты по карте
     * Нужно убрать заказ и вернуть человека на страницу с выбором типа оплаты
     */
    public function action_payment_back()
    {
        if (empty($this->user))  throw new HTTP_Exception_403;

        $oid = intval($_SERVER['QUERY_STRING']); // номер заказа должен придти из запроса
        if (empty($oid)) throw new HTTP_Exception_404;

        $o = new Model_Order($oid);
        if ( ! $o->loaded()) throw new HTTP_Exception_404; // нет такого заказа

        if ($o->user_id != $this->user->id)  throw new HTTP_Exception_404; // это заказ другого пользователя

        if ($o->status != 'C') throw new HTTP_Exception_404; // заказ д.б. в статусе ожидания оплаты

        Session::instance()->delete('order_id'); // вычистим order_id из сессии

        $odata = $o->data->as_array();
        Session::instance()->set('od', json_encode($odata + array('delivery_type' => $o->delivery_type))); // восстанавливаем данные заказа
        $goods = $o->get_goods();

        if ( ! empty($goods)) { // восстанавливаем данные о товарах в заказе TODO - добавиит благотворительность, проверить подарки
            $gg = array();
            foreach($goods as $g) {
                $gg[$g->id] = $g->quantity;
            }
            Cart::instance()->add($gg);
        }

        if ($o->delivery_type == Model_Order::SHIP_SERVICE) {

            $tarif_id = Session::instance()->get('tarif_id');
            $edost = Session::instance()->get('edost');
            if (empty($tarif_id) OR empty($edost)) { // восстановим данные о тарифе
                Session::instance()->set('tarif_id', 1);
                @list($c, $name) = explode(', ', $o->data->comment);
                if ($c and $name) {
                    Session::instance()->set($edost, array(1 => array('company' => $c, 'name' => $name, 'price' => $o->price_ship)));
                }
            }
        }

        $this->request->redirect(Route::url('cart'));
    }
}
