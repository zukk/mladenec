<?php
class Controller_User extends Controller_Frontend
{
    /**
     * Регистрация нового юзера
     */
    public function action_register()
    {
        if ( ! $this->request->post('ajax')) {
            $this->request->redirect('/?reg'); // на главную с открытой формой регистрации
        }

        $user = new Model_User();

        if ($this->request->post()) {

            $post = $this->request->post();
            if (isset($post['email'])) $post['email'] = trim($post['email']); // у мыла режем пробелы
            $post['login'] = $post['email'];

            $v = $user->validation()->copy($post)
                ->rule('password', 'not_empty')
                ->rule('password', 'min_length', [':value', 6])
                ->rule('password2', 'not_empty')
                ->rule('password', 'matches', [':validation', 'password', 'password2'])
                ->rule('email', [ORM::factory('user'), 'unique'], ['email', ':value']);

            if ( ! $v->check()) $this->return_json(['error' => $v->errors('user')]); // ошибки при создании юзера

            $user->create_new($v);
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

            if (parse_url($this->request->referrer(), PHP_URL_PATH) == Route::url('cart')) { // с корзины
                $this->return_json( [
                    'delivery' => Controller_Product::cart_delivery(),
                    'userpad' => $this->userpad(),
                    'userId' => $user->id
                ]);
            } else {
                $this->return_json([
                    'userId' => $user->id,
                    'redirect' => $refer
                ]);
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
		$json = [];
		
        if (($l = $this->request->post('login')) AND ($p = $this->request->post('password'))
        ) {
			$u = Model_User::login(array('login' => $l, 'password' => $p, 'remember' => $this->request->post('remember')));

            if ($u) {

				$Session = Session::instance();
				$cart = $Session->get('cart');

				$session_id = Session::instance()->id();

				$old_sessions = ORM::factory('session')->where('user_id', '=', $u->id)->where('id', '!=', $session_id)->find_all()->as_array('id');

				if (empty($old_sessions)) $this->return_redirect($this->request->referrer());

                $old_goods = [];
                foreach ($old_sessions as $key => $sess) {
                    $old_data = unserialize($sess->data);
                    $old_goods = $old_goods + $old_data['cart']->goods;
                }
                if (empty($old_goods)) $this->return_redirect($this->request->referrer());
                if (empty($cart->goods)) {

                    $Cart = Cart::instance($old_goods);
                    $Session->set('cart', $Cart)->write();
                    $json = ['redirect' => $this->request->referrer()];

                } else {

                    $ogoods = ORM::factory('good')
                        ->where('id', 'IN', array_keys($old_goods))
                        ->find_all()
                        ->as_array('id');

                    $json = [
                        'redirect' => $this->request->referrer(),
                        'fancybox' => View::factory('smarty:admin/user/merge_carts', [
                            'old_sessions' => array_keys($old_sessions),
                            'old_goods' => $ogoods,
                            'old_goods_counts' => $old_goods,
                            'user' => Model_User::current()
                        ])->render(),
                    ];
                }

                $json['userId'] = $u->id;
            }
        }

		if ( ! empty($json)) {
			
			if (parse_url($this->request->referrer(), PHP_URL_PATH) == Route::url('cart')) { // c корзины
                $json['cart'] = Cart::instance()->checkout(); // нужна новая корзина ведь при логине может сумма измениться
                $json['fancybox'] = FALSE;
                $json['redirect'] = FALSE;
                $json['delivery'] = Controller_Product::cart_delivery(); // подгрузим доставку  
                $address_list = [];
                foreach( $u->address() as $address) {
                    $address_list[$address->id] = $address->as_array();
                }
                $json['addresses'] = $address_list; // адреса для подстановки
            }

            if (empty($json['redirect'])) $json['userpad'] = $this->userpad(); // общий блок логина-пароля

			$this->return_json( $json );
		}

        $this->return_error(['login' => 'Неправильный логин или пароль', 'password' => 'Неправильный пароль или логин']);
    }
	
	/**
	 * html плашки в шапке
	 * @return type
	 */
	public static function userpad()
    {
		return View::factory('smarty:user/userpad', ['user' => Model_User::current()] )->render();
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
            $this->return_html('Ваши данные успешно изменены <a href="'.Route::url('user').'" class="ok">Сменить снова</a>');
        }

		$this->tmpl['user_phones'] = $this->user->get_phones();
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

    public function action_deferred()
    {
        if (!$this->user) {
            throw new HTTP_Exception_403;
        }

        $deferreds = ORM::factory('deferred')
            ->where('user_id', '=', $this->user->id);
        $this->tmpl['pager'] = $pager = new Pager($deferreds->count_all());
        $this->tmpl['deferreds'] = $deferreds
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
                    'birth' => isset($post['birth'][$id]) ? Txt::date_reverse($post['birth'][$id]) : 0,
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
                        'birth' =>  isset($post['new_birth'][$id]) ? Txt::date_reverse($post['new_birth'][$id]) : null,
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
            
            // сохранение флага беременности и срока
            if(isset($post['pregnant']) && $post['pregnant'] == 1
               && intval($post['pregnant_terms'])>0
               && intval($post['pregnant_terms'])<=41) {
                $this->user->pregnant = 1;
                $this->user->pregnant_terms = (time()- intval($post['pregnant_terms'])*7*24*60*60);
                $this->user->save();

            } else {
                $this->user->pregnant = 0;
                $this->user->save();
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

            GetResponse::renew($this->user->id);
        }

        // скидка за даные о детях - включаем если надо
        $this->tmpl['coupon'] = $this->user->child_discount( ! empty($user_kids) || ! empty($this->user->pregnant));

        //срок беременности
        $this->tmpl['pregnant_weeks'] = ($this->user->pregnant_terms) ? $this->user->get_pregnant_weeks() : NULL;
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
        $this->tmpl['actions'] = Model_Action::get_active(TRUE); // накопительные
    }
	
	protected function _get_user_goods($limit)
    {
		$result = ORM::factory('user_good')
            ->where('user_id', '=', $this->user->id)
            ->group_by('good_id')
            ->order_by('created', 'DESC')
            ->limit($limit)
            ->find_all()
            ->as_array('good_id');

        $ids = array_keys($result);

        $return = [];
		if ( ! empty($ids)) {
			$return = ORM::factory('good')
                ->where('id', 'IN', $ids)
                ->find_all()
                ->as_array('id');
		}

		return $return;
	}
    
    /**
     * Личный кабинет юзера
     * @throws HTTP_Exception_403
     */
    public function action_goods_ajax()
    {
        if ( ! $this->user) throw new HTTP_Exception_403;

		// TODO !!!
		exit;
		echo View::factory('smarty:product/view/tiles', [
            'goods' => $this->_get_user_goods(5),
			'is_topbar' => TRUE
        ])->render();
		exit;
	}
	
    /**
     * Личный кабинет юзера - история просмотров
     * @throws HTTP_Exception_403
     */
    public function action_goods()
    {
        if ( ! $this->user) throw new HTTP_Exception_403;
        
        $goods = $this->_get_user_goods(20);        
        $keys = array_keys($goods);
        $images = Model_Good::many_images([255], $keys);        
        $prices  = Model_Good::get_status_price(1, $keys);
        
        $this->tmpl['goods'] = View::factory('smarty:product/view/tiles', [
            'goods' => $goods,
            'images' => $images,
            'price' => $prices,
            'row' => 5
        ])->render();
    }
	
    /**
     * Личный кабинет юзера
     * @throws HTTP_Exception_403
     */
    public function action_reviews()
    {
        if ( ! $this->user) throw new HTTP_Exception_403;

		$reviews = ORM::factory('good_review')
			->where('user_id', '=', $this->user->id)
			->with('author')
			->order_by('time', 'DESC')
			->find_all()
			->as_array('id');
		
		$goodsIds = [];
		foreach($reviews as $review) $goodsIds[] = $review->good_id;

		$goods = [];
		if ( ! empty($goodsIds)) {
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
    public function action_password()
    {
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
                $user = ORM::factory('user')
                    ->where('id', '=', $this->user->id)
                    ->password($this->request->post('old_password'))
                    ->find();
                $oldpass = $user->loaded();
            }

            if ($v->check() AND $oldpass) {
                $salt = Text::random();
                $this->user->password = $salt.md5($salt.$v['password']);
                $this->user->save();
                $this->user->checkword = ''; // чистим код восстановления
                Model_User::login($this->user);

                $this->return_html('Ваш пароль успешно изменён. '.HTML::anchor(Route::url('user'), 'Перейти в личный кабинет'));

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
     * Оплата по карте - возврат с платёжного шлюза
     *
     * @throws HTTP_Exception_404
     * @throws HTTP_Exception_403
     */
    public function action_pay_success()
    {
        $session_id = $this->request->query('orderId');

        if (empty($session_id)) throw new HTTP_Exception_404;

        $payment = new Model_Payment(['session_id' => $session_id]);
        if ( ! $payment->loaded()) throw new HTTP_Exception_404;
        $payment->status(); // письма юзеру и прочее - см. внутри status()

        $this->request->redirect(Route::url('pay', ['id' => $payment->order_id])); // переходим на страницу со статусом оплаты
    }

    /**
     * Возврат с формы оплаты по карте
     */
    public function action_payment_back()
    {
        $this->request->redirect(Route::url('pay', ['id' => intval($_SERVER['QUERY_STRING'])]));
    }
	
	public function action_phone(){
		
        if ( ! $this->user) throw new HTTP_Exception_403;
		
		$phone = $this->request->post('phone');
		
		$Phone = new Model_User_Phone();
		
		$post = $this->request->post();
		$post['user_id'] = $this->user->id;
		
		$v = $Phone->validation()->copy($post);
		
		if( $v->check() ){
			
			$newPhone = $Phone->values(array(
				'phone'      => $v['phone'],
				'user_id' => $v['user_id']
			))->create();
			$this->user->phone_active = $newPhone->id;
			$this->user->save();
			
			$this->return_json(['ok' => $newPhone->id, 'html' => View::factory('smarty:user/phone', [
				'user_phones' => $this->user->get_phones(),
				'user' => $this->user
			])->render()]);
		}
		else{
			$this->return_error($v->errors());
		}
	}

    /**
     * Просмотр заказа юзером
     * @param int $id
     * @return array
     * @throws HTTP_Exception_403
     * @throws HTTP_Exception_404
     * @throws Kohana_Exception
     */
    public function action_order($id = 0)
    {
        $order_id = (empty($id)) ? $this->request->param('id') : $id;

        if (empty($order_id)) throw new HTTP_Exception_404;
        $order = new Model_Order($order_id);
        if ( ! $order->loaded()) throw new HTTP_Exception_404;

        $this->tmpl['phone'] = $order->data->phone;
        $this->tmpl['order_goods'] = $order_goods = $order->get_goods();

        if ($this->request->param('thanx')) { // это спасибо-страница - тут юзера может не быть (для заказов в один клик)

            if (Session::instance()->get('show_ecommerce') == $order->id) { // чтобы не слать статистику в ГА и Я второй раз
                $this->tmpl['is_new'] = TRUE;
                Session::instance()->delete('show_ecommerce');
                $this->layout->dataLayer = View::factory('smarty:user/order/datalayer', ['o' => $order, 'order_goods' => $order_goods]); // хотели dataLayer выше gtm
            }
            $this->tmpl['thanx'] = TRUE;
            $this->layout->o = $order;

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

            if ($this->user && ! empty($polls)) { // есть опросы
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
        } else {
            if ( ! $this->user) throw new HTTP_Exception_403;
        }

        $this->tmpl['o'] = $this->tmpl['cart'] = $order;
        $this->tmpl['od'] = $order->data;
        $this->tmpl['coupon'] = $order->coupon_id ? $order->coupon : FALSE;

        return $this->tmpl;
    }

    /**
     * Оплата заказа картой - пускаем всех!
     */
    public function action_pay()
    {
        $order_id = $this->request->param('id');
        $o = new Model_Order($order_id);
        if ( ! $o->loaded()) throw new HTTP_Exception_404; // нет заказа
        if ($o->pay_type != Model_Order::PAY_CARD || ! $o->can_pay) throw new HTTP_Exception_404; // не карта

        if ($this->request->post('ajax')) { // хотят оплатить

            $payment = $o->payments
                ->where('status', '=', Model_Payment::STATUS_New)
                ->find();

            if (empty($payment->id)) { // нет оплат - создадим новую
                $payment = new Model_Payment();
            }
            $this->return_redirect($payment->init($o));
        }
        $this->tmpl['order'] = $o;

    }
}
