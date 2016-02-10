<?php

class Controller_Product extends Controller_Frontend {

	const FILTER_SEX = 1951;
	const FILTER_COLOR = 1952;
	const FILTER_SIZE = 1949;
	const FILTER_GROWTH = 1950;
	const FILTER_AGE = 1948;
	const FILTER_TYPE = 1947;

	
	public static $FILTERTYPES = [
		/* self::FILTER_SEX, */ self::FILTER_COLOR, self::FILTER_SIZE, self::FILTER_GROWTH, self::FILTER_AGE, self::FILTER_TYPE
	];
	
	public static $FILTERLABELS = [
		/* 'Выберите пол', */ 'Выберите цвет', 'Размеры', 'Выберите рост', 'Возраст', 'Тип'
	];
	
	public static $COLORS = [
		'белый' => '#ffffff',
		'серый' => '#cccccc',
		'розовый' => '#FFC0CB',
		'синий' => '#0047AB',
		'красный' => '#CC0605',
		'голубой' => '#00BFFF',
		'фиолетовый' => '#800080',
		"бежевый" => '#F5F5DC',
		"зеленый" => 'green',
		"коричневый" => '#964B00',
		"черный" => 'black',
		"желтый" => 'yellow',
		"сливовый" => '#660066',
		"оранжевый" => '#FF4F00',
		"хаки" => '#C3B091',
		"гранатовый" => '#92000A',
		"лиловый" => '#C8A2C8',
		"панна" => '#ccc',
		"полоска" => '#ccc',
		"горох" => '#green',
		"салатовый" =>'#7FFF00', 
		"фисташковый" => '#BBCF54',
		"фисташка" => '#BBCF54',
		"бордовый" => '#7FFF00',
		"сиреневый" => '#C8A2C8',
		"оливковый" => '#808000',
		"экрю" => '#E9967A',
		"кремовый" => '#FFEBCD',
		"шампань" => '#F8F8FF',
		"персиковый" => '#FFE5B4',
		"меланж" => '#DCDCDC',
		"фуксия" => '#FF00FF',
		"бирюзовый" => '#30D5C8',
		"цветной" => '#ffffff',
		"пурпурный" => '#F984E5',
		"коралловый" => '#FF7F50',
		"джинс" => '#1560BD',
		"мятный" => '#98FF98',
		"лимонный" => '#FDE910',
		"баклажан" => '#772953',
		"амарант" => '#E52B50',
		"молоко" => '#FFFFF0',
		"малиновый" => '#DC143C',
		"василёк" => '#6495ED',
		"индиго" => '#310062',
		"ментоловый" => '#FAEBD7',
		"фиалковый" => '#ea8df7',
		"золотой" => '#FFD700',
		"серебряный" => '#C0C0C0',
		"слива" => '#660066',
		"терракот" => '#CC4E5C',
		"карамель" => '#FADFAD',
		"графит" => '#494d4e',
		"горчичный" => '#FFDB58',
		"бирюза" => '#30D5C8',
		"светло коричневый" => '#CD853F',
		"набивка" => '#ccc',
		'светло серый' => '#ddd',
		"Рисунок" => 'url(/i/colors.png) center',
		"клетка" => 'url(/i/colors.png) center',
		"принт" => '#ccc',
	];
	
    /**
     * Просмотр одного товара
     * @throws HTTP_Exception_404
     */
    public function action_view()
    {
        $good = new Model_Good($this->request->param('id'));
        if ( ! $good->loaded() || ! $good->show)  throw new HTTP_Exception_404;

		$this->tmpl['group'] = $group = new Model_Group($good->group_id);
        if ( ! $group->loaded() || ! $group->active) throw new HTTP_Exception_404;

        $this->tmpl['section'] = $section = new Model_Section($group->section_id);
        if ( ! $section->loaded() || ! $section->active) throw new HTTP_Exception_404;

        $this->tmpl['parent'] = $parent = ORM::factory('section') // родительская катeгория
            ->where('id', '=', $section->parent_id)
            ->where('active', '=', 1)
            ->find();

        if ($parent->loaded() && ! $parent->active) throw new HTTP_Exception_404; // если есть - то надо активную

        // redirect to canonical url
        if (($good->translit != $this->request->param('translit')) || ($section->vitrina != Kohana::$server_name) || $good->group_id != $this->request->param('group_id'))
            $this->request->redirect(Route::$default_protocol.Kohana::$hostnames[$section->vitrina]['host'].Route::url('product', $good->as_array()).($this->request->query('ajax') == 1 ? '?ajax=1' : ''), 301);

		$this->tmpl['tags'] = $tags = $good->get_tags(); // его теговые страницы
        $this->tmpl['notInSale'] = $last_seen = $good->not_in_sale(); // если нет в продаже - тут время когда был

        if ($last_seen && (time() - strtotime($last_seen)) > 6 * 30 * 24 * 60 * 60) { // если товара долго нет впродаже (6 месяцев)
            $redirUri = empty($tags) ? $section->get_link(FALSE) : $tags[0]->code; // редирект на теговую или на страницу категории
            $this->request->redirect($redirUri);
		}

        $this->tmpl['goods'] = $goods = ORM::factory('good') // получаем все товары из группы
            ->where('group_id', '=', $group->id)
            ->where('show', '=', 1)
            ->where('active', '=', 1)
            ->order_by('order','asc')
            ->order_by('name','asc')
            ->find_all()
            ->as_array('id');

        if ( empty($goods[$good->id])) $goods[$good->id] = $good;

        $this->tmpl['cgood'] = $good;
        $this->tmpl['prop'] = new Model_Good_Prop($good->id);

        $keys = array_keys($goods); // сохраним ид товаров, чтобы потом получить скопом все картинки

        if ( ! empty($this->user)) // история просмотров товаров пользователем
            DB::insert('z_user_good')->columns([
                'user_id',
                'good_id',
                'created'
            ])->values([
                'user_id' => $this->user->id,
                'good_id' => $good->id,
                'created' => date('Y-m-d H:i:s')
            ])->execute();

        $this->tmpl['good_action'] = $aa = Model_Action::for_icons($keys); // получим данные об акциях для всех товаров (для слайдера тоже)
        $this->tmpl['serts'] = $group->get_serts( TRUE ); // сертификаты соответствия

		$this->tmpl['sectionTabs'] = empty($good->section->settings['goodTabs']) ? ['Полное описание', 'Отзывы'] : $good->section->settings['goodTabs'];
		$this->tmpl['goodTabs'] = $good->text->find_all()->as_array('name', 'content');
		
        if ($this->request->query('ajax')) { // быстрый просмотр
            $this->tmpl['is_quickview'] = TRUE;
            $this->tmpl['price'] = Model_Good::get_status_price(1, $keys);
            exit(View::factory('smarty:product/buy', $this->tmpl));
        }

        /**
         * всё что дальше нужно только для полной карточки
         */

        $slider_goods = [];
        $slider_header = '';
        
        if ($good->show) {

            $bundled_goods = $good->get_promo_goods(1); // Участвует ли товар в промоакциях?

            if ( ! empty($bundled_goods) && $good->qty != 0 ) { // да, и в наличии

                $slider_goods = $bundled_goods;

                $bundled_ids = [];
                foreach($bundled_goods as $bg) $bundled_ids[] = $bg->id;
                $keys = array_merge($keys, $bundled_ids);

                $promos = $good->get_promos();

                foreach($promos as $prom) {
                    $header = $prom->slider_header;
                    if ( ! empty($header)) {
                        $slider_header = $header;
                        break;
                    }
                }

            } else {

                if ($good->qty == 0) { // нет в наличии
                    $slider_goods = $good->analogy();
                    $slider_header = 'Аналогичные товары';
                } else {
                    $slider_goods = $good->get_frequent();
                }
            }

        } else { // товар отключен от показа - показываем аналоги

            $slider_header = 'Аналогичные товары';
        }

        if ( ! empty($slider_goods)) { // Товары для слайдера
            $this->tmpl['frequent'] = $slider_goods = $good->analogy();
            $this->tmpl['slider_header'] = $slider_header;
            if($slider_goods) {
                $keys = array_merge($keys, array_keys($slider_goods));
            }
        }

        if($keys){
            $this->tmpl['images'] = Model_Good::many_images([255], $keys);
            $this->tmpl['price'] = Model_Good::get_status_price(1, $keys); // цены
        }

        // $this->tmpl['serts'] = $group->get_serts( TRUE ); // сертификаты соответствия

        $filters = []; // {{{ показ фильтров в карточке товара
        $filter_data = $good->filters_data();
        foreach($filter_data as $vid => $data) {
            if (Model_Filter::big($data['filter_id'])) { // большие фильтры - отдельно
                $this->tmpl['big_filter'] = ['id' => $vid, 'value' => $data['value_name']];
            } else {
                if (empty($filters[$data['filter_name']])) $filters[$data['filter_name']] = [];
                $filters[$data['filter_name']][] = $data['value_name'];
            }
        }
        $this->tmpl['filters'] = $filters;  // }}}

        foreach($filters as $fnames => $values){
            if (strpos($fnames, 'Возраст') !== false){
                $res_desc_cons = 'Проконсультируйтесь со специалистом. Для детей с ';
                foreach($values as $val){
                    $desc_cons = preg_replace("/[^0-9]/", '', $val);
                    if(strpos($val, '-') === false){
                        if (strpos($val, 'год') !== false || strpos($val, 'лет') !== false) {
                            if ($desc_cons > 1) {
                                $year = 'лет';
                            } else {
                                $year = 'года';
                            }
                        } else {
                            if ($desc_cons > 1){
                                $year = 'месяцев';
                            } else {
                                $year = 'месяца';
                            }
                        }
                        $res_desc_cons .= $desc_cons.' '.$year.', ';
                    } else {
                        $del_str = explode(' ', $val);
                        $res_val = explode('-', $del_str[0]);
                        if ($res_val[1] > 1){

                            $year = 'месяцев';
                        } else {
                            $year = 'месяца';
                        }
                        $res_desc_cons .= $res_val[0] . ' до ' . $res_val[1] . ' ' . $year.', ';
                    }
                }
                $res_desc_cons = rtrim($res_desc_cons, ', ').'.';
            }
        }

        $section_id = [29065, 29150, 28985, 28935, 29253, 29051, 29138, 29413, 28962];
        if(in_array($good->section->id, $section_id)) {
            $this->tmpl['consul'] = $res_desc_cons;
        }

        if ($good->is_cloth()) { // для карточки одежды получаем все варианты цветов и размеров для товаров из группы, со связями цвет => размеры
			
			$color_size_data = Model_Good::get_color_size([$good->group_id]);

			$this->tmpl['colorsize'] = $color_size_data[$good->group_id]['colorsize'];
			$this->tmpl['sizes'] = $color_size_data[$good->group_id]['sizes'];
            $this->tmpl['allsizes'] = $color_size_data[$good->group_id]['allsizes'];
            $this->tmpl['colors'] = $color_size_data[$good->group_id]['colors'];
            $this->tmpl['colorimage'] = $color_size_data[$good->group_id]['colorimage'];
            $this->tmpl['size_filter'] = $color_size_data[$good->group_id]['size_filter'];

            //print_r($color_size_data);
            if ($this->request->query('ajax') || $this->request->post('infancybox')) { // это смена цвета-размера в карточке
				$this->tmpl['price'] = Model_Good::get_status_price(1, $keys);
				$this->tmpl['infancybox'] = TRUE;
				exit(View::factory('smarty:product/view/inner', $this->tmpl)->render());
			}
		}

		if ($this->request->post('isajax')){
            exit(View::factory('smarty:product/view/inner', $this->tmpl)->render());
		}

        $delivery = 'всей России';
        if ( ! Conf::instance()->regional_shipping_allowed([$good->section_id => ['section_id' => $good->section_id]])) $delivery = 'Москве и Московской области';
        
        $this->layout->title       = $group->name . ' ' . $good->name . ': купить, цена, фото';
        $this->layout->keywords    = 'купить ' . $group->name . ', '  . $good->name . ', цена, фото, гарантия, описание, отзывы';
        $this->layout->description = 'Купить: ' 
                . $group->name . ' ' . $good->name
                . ' в интернет-магазине детских товаров «Младенец.ру». Узнать цену и заказать ' . $group->name . ' ' . $good->name . ': фото, отзывы, описание. Низкие цены, гарантия, доставка по '.$delivery;
        
        if ( ! empty($good->seo->title)) {
            $this->layout->title       = $good->seo->title;
            $this->layout->keywords    = $good->seo->keywords;
            $this->layout->description = $good->seo->description;
        }
        if (Model_User::logged()) {
            $deferred = new Model_Deferred();
            $deferred->user_id = $this->user->id;
            $deferred->good_id = $good->id;
            $this->tmpl['is_def'] = $deferred->is_deferred();
        }

    }

    /**
     * Добавление в корзину товаров
     * @return string
     */
    public function action_add()
    {
        $cart = Cart::instance();

        $cart->add($this->request->post('qty'));
        
        if ($this->request->is_ajax() AND $this->request->post('one')) exit($cart); // put one item to cart - ajax

        $referer = $this->request->referrer();
        if (strpos($referer, '/account/order/') !== FALSE) { // пришли с кнопки - повторить заказ
            $go = Route::url('cart');
        } else {
            $go = $referer;
        }
        $this->request->redirect($go);
    }

    /**
     * Просмотр товара для 1с
     */
    public function action_view1c()
    {
        $code = $this->request->param('code');
        $good = ORM::factory('good')
            ->where('code', '=', $code)
            ->find();

        if ( ! $good->loaded()) throw new HTTP_Exception_404;
        $goods = array($good->id => $good);

        $this->tmpl['group'] = $group = $good->group ;
        $this->tmpl['section'] = $section = new Model_Section($group->section_id);

        $this->tmpl['parent'] = $parent = ORM::factory('section') // родительская катагория
            ->where('id', '=', $section->parent_id)
            ->where('active', '=', 1)
            ->find();

        $this->tmpl['goods'] = $goods;
        $this->tmpl['cgood'] = $good;
        $this->tmpl['good'] = $good;
        $this->tmpl['prop'] = $prop = new Model_Good_Prop($good->id);
        $this->tmpl['price'] = Model_Good::get_status_price(1, $good->id);
        $this->tmpl['good_action'] = array();
        $this->tmpl['comments'] = array();

        $this->tmpl['notInSale'] = $good->not_in_sale(); // если нет в продаже - тут время когда был

        $this->tmpl['filters'] = $good->get_filters($section);

        $this->tmpl['sectionTabs'] = empty($good->section->settings['goodTabs']) ? ['Полное описание', 'Отзывы'] : $good->section->settings['goodTabs'];
        $this->tmpl['goodTabs'] = $good->text->find_all()->as_array('name', 'content');

        $this->layout = View::factory('smarty:layout/1c');
        $this->layout->body = View::factory('smarty:product/view', $this->tmpl)->render();
        $this->layout->cart = null;
    }

    /**
     * Создание нового заказа из корзины
     * @param $post
     * @throws ErrorException
     * @throws HTTP_Exception_403
     * @throws HTTP_Exception_404
     */
    protected function _create_order(&$post)
    {

		$cart = Cart::instance();
		$user = Model_User::current();
		$post = $this->request->post();
        if($cart->gift_only()){ // сброс адреса если в заказе только подарочные сертификаты
            $post['city'] = ' ';
            $post['street'] = ' ';
            $post['house'] = 1;
            $post['kv'] = 1;
            $post['name'] = 'noname';
            $post['last_name'] = 'noname';
        }
        $delivery_type = (int)$post['delivery_type'];

		$order_data = new Model_Order_Data();
        
        $is_ozon_delivery = ($delivery_type == Model_Order::SHIP_OZON);
        if($is_ozon_delivery) {
            $post['address'] = $post['ozon_terminal'];
            unset($post['ozon_terminal']);
        } elseif(isset($post['ozon_terminal'])) {
            unset($post['ozon_terminal']);
            unset($post['ozon_delivery_id']);
        }
        $order_data->values($post);
        
        if( ! $is_ozon_delivery) {
            $v = $order_data->validation();

            if ( ! empty($post['address_id'])) { // старый адрес!
                $addr = new Model_User_Address($post['address_id']);
                if ( ! $addr->loaded()) throw new HTTP_Exception_404; // no address found
                if ($addr->user_id != $this->user->id) throw new HTTP_Exception_403; // not current user address

            } else {
                $addr = new Model_User_Address();
                $addr->values($post);
                $addr->user_id = $this->user->id;
                $addr->save();
                $post['address_id'] = $addr->id;
            }

            // эти параметры берём из поста
            $addr->mkad = intval($post['mkad']); // расстояние до мкад
            $addr->latlong = $post['latlong']; // координаты
            $addr->zone_id = $post['ship_zone']; // зона
            $addr->comment = $post['comment']; // комментарий к адресу (как проехать или тип доставки)
            $addr->last_used = date('Y-m-d H:i');
            $addr->save();

            $order_data->values([
                'city' => $addr->city,
                'street' => $addr->street,
                'house' => $addr->house,
                'kv' => $addr->kv,

                'mkad' => $addr->mkad,
                'enter' => $addr->enter,
                'domofon' => $addr->domofon,
                'floor' => $addr->floor,
                'lift' => $addr->lift,
                'latlong' => $addr->latlong,
                'ship_zone' => $addr->zone_id,
                'comment' => $addr->comment,
                'address_id' => $addr->id,
                'correct_addr' => $addr->correct_addr,
            ]);

            if ($delivery_type != Model_Order::SHIP_COURIER) { // если не наш курьер - надо фио
                $v->rule('last_name', 'not_empty');
                $v->rule('name', 'not_empty');
            }
            foreach($addr->rules() as $f => $rules) $v->rules($f, $rules); // добавим правила на адрес к данным заказа        
        } else {
            $v = new Validation($post);
            $v->rule('last_name', 'not_empty');
            $v->rule('name', 'not_empty');
        }

        //правила валидации полей адреса кроме доставки от Озон
        if ($v->check()) {

            $o = new Model_Order();
            $total = $cart->get_total(); // тут пересчитает корзину!
            $o->values([
                'user_id'       => $user->id,
                'user_status'   => $cart->status_id,
                'status'        => 'N',
                'status_time'   => date("Y-m-d H:i:s"),
                'pay_type'      => $post['pay_type'],
                'delivery_type' => (int)$post['delivery_type'],
                'price'         => $total,
                'discount'      => $cart->discount,
			    'description'   => htmlspecialchars($post['description'])
			]);

            if ($cart->coupon_error) { // купон не подошёл - скинем
                $cart->coupon = [];
            }

			if (in_array($o->delivery_type, array(Model_Order::SHIP_COURIER, Model_Order::SHIP_SERVICE))) { // доставка по адресу
                $o->price_ship = $o->price_ship($order_data); // проставим цену доставки
			} elseif( $o->delivery_type == Model_Order::SHIP_OZON && isset($post['ozon_delivery_id']) ) {
                $ozon = new OzonDelivery();
                $price_ship = $ozon->calculate_price($post['ozon_delivery_id'], $cart->weight());
                $o->price_ship = isset($price_ship['price']) ? $price_ship['price'] : 0;
            }

            if ($o->pay_type == Model_Order::PAY_CARD && empty($cart->to_wait) && $o->delivery_type != 0) { // заказу можно сразу разрешить оплату
                //$o->can_pay = 1;
            }

            if(isset($addr)) {
                // { дата доставки не должна быть меньше ближайшей            
                $zone = new Model_Zone($order_data->ship_zone ? $order_data->ship_zone : Model_Zone::DEFAULT_ZONE);
                $first_date = key($cart->allowed_date($zone, $addr->latlong));

                $min_time = strtotime($first_date);
                $ship_time = strtotime($order_data->ship_date);

                if ($ship_time === FALSE || $ship_time < $min_time) {
                    $order_data->ship_date = $first_date;
                }
                // }
            }

			// дата создания заказа - из сессии
            $od_time = date('Y-m-d H:i:s', Session::instance()->get('od_time') );
			if ( ! empty($od_time)) $o->created = $od_time;

			$o->save();
			$o->save_goods($cart); // тут будет засчитано использование купона

			$order_data->id = $o->id;

			if ( ! empty($a)) $order_data->address_id = $a->id;
            $order_data->client_data = print_r($_SERVER, TRUE);
			$order_data->save();

			$cart->clean(); // чистка корзины!

			Session::instance()->delete('cart_delivery');

    		$o->send_sms_accepted(); // смс о приёме заказа

			$mail_params = array(
				'o' => $o,
				'od' => $order_data,
				'coupon' => $o->coupon_id ? $o->coupon : FALSE
			);

			if ( ! empty($to_wait)) $mail_params['to_wait'] = $to_wait;
			Mail::htmlsend('order', $mail_params, $order_data->email, 'Ваш заказ '.$o->id.' принят');

			Session::instance()->set('show_ecommerce', $o->id); // отправить статистику по заказу в ГА и Я
			$this->return_redirect(Route::url('order_detail', ['id' => $o->id, 'thanx' => 'thanx'])); // идём на страницу спасибо

		} else {
            $this->return_error($v->errors('order/order_data'));
		}
		
		exit;
	}

    /**
     * Корзина - отображение и оформление заказа
     * @throws HTTP_Exception_403
     * @throws HTTP_Exception_404
     */
    public function action_cart()
    {
        $cart = Cart::instance();

        $this->layout->title = 'Ваша корзина';
		
		$post = $this->request->post();

        if (empty($cart->goods)) {
            $this->return_error(['cartempty' => 'Корзина пуста']);
        }

        if (empty($post['agree'])) {

            $this->return_error(['agree' => 'Для отправки заказа Вы&nbsp;должны согласиться с&nbsp;пользовательским соглашением.']);

        } elseif( $post['delivery_type'] == 5 && $post['ozon_delivery_id'] == '') {
            $this->return_error(['ozonfail' => 'Не соответствие данному критерию, попробуйте изменить содержимое корзины или воспользоваться курьерской доставкой']);
        } else {

            try {

                $this->_create_order($post);

            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors();
                $e = [];

                foreach ($errors as $field => $error) $e[$field] = 'Обязательное поле';
                $this->return_error($e);
                exit;
            }
        }

		Session::instance()->set('od_time', time());
        $this->layout->body = $cart->checkout(TRUE);
	}

    /**
     * Выбор подарков в корзине (или отказ от подарка)
     */
    public function action_cart_presents()
    {
        $cart = Cart::instance();
        $present = $this->request->post('select_present');
        $cart->select_presents($present); // выбранные подарки или отказы - запомним в корзине
        exit();
    }

    /**
     * Удаление товара из корзины
     * @throws View_Exception
     */
    public function action_cart_remove_good()
    {
	    Session::instance()->set('od_time', time());

        $cart = Cart::instance();
        $cart->remove($this->request->post('id'));
        $this->return_json(['cart' => $cart->checkout(), 'price_changed' => 1]);
    }

    /**
     * Применение или отмена купона в корзине
     * @throws Kohana_Exception
     * @throws View_Exception
     */
    public function action_cart_coupon()
    {
		$cart = Cart::instance();
		Session::instance()->set('od_time', time());
		
		if ($this->request->post('coupon')) { // использовать купон

            $cart->load_coupon($this->request->post('coupon'));
			$cart->recount();

        } else if ( ! empty($cart->coupon) && $this->request->post('remove')) { // удаление купона

			$cart->remove_coupon();

		}

        $this->return_json([
            'cart' => $cart->checkout(), // View::factory('smarty:product/cart', $this->cartParams())->render()
        ]);
	}
	
	/**
	 * Комментарии к товарам в корзине
     * Тут же обрабатывается услуга сборки КГТ
	 */
    public function action_cart_comments()
    {
		$cart = Cart::instance();

        $com['comments'] = $this->request->post('comment');
        $com['comment_email'] = $this->request->post('comment_email');

        if ($this->request->post('sborka')) { // это сборка КГТ, должен быть id и коммент - строка
            $id = $this->request->post('id');
            if ($com == '') {
                $cart->remove($id);
                echo 'remove '.$id;
            } else {
                $cart->remove($id);
                $cart->add([$id => 1]);
            }
            $cart->set_comments([$id => $com])->save();

        } else {
            $cart->set_comments($com)->save();
            //$cart->set_comments($com_email)->save();

        }

		exit('ok');
	}

    /**
     * Открыть меню доставки
     */
    public function action_delivery_open()
    {
        $cart = Cart::instance();
        $cart->delivery_open = TRUE;
        $cart->save();
        exit(Controller_Product::cart_delivery());
    }

    /**
     * Пересчёт корзины - вызыввается ajax
     * @throws Kohana_Exception
     * @throws View_Exception
     */
    public function action_cart_recount()
    {
        $cart = Cart::instance();
        Session::instance()->set('od_time', time());
        
        $previous_cart_total = $cart->total;

        if ($qty = $this->request->post('qty')) { // пересчёт корзины с нуля
            $cart->clean();

            if ($coupon = $this->request->post('coupon')) { // может придти и купон
                $cart->load_coupon($coupon);
            }
            $cart->add($qty, FALSE); // recount inside!
        }

        if ($comments = $this->request->post('comment')) { // если были комментарии - прикрепим обратно,
        // мы их стёрли в cart::clean
            //$cart->set_comments($comments); // original
            $comments['comments'] = $comments;
            if ($comment_email = $this->request->post('comment_email')) { // если были комментарии - прикрепим обратно,
                // мы их стёрли в cart::clean
                $comments['comment_email'] = $comment_email;
            }
            $cart->set_comments($comments);
        }

        $result = [
            'cart' => $cart->checkout(),
        ];

        if ($previous_cart_total != $cart->total) {
            $result['price_changed'] = 1;
        }

        $this->return_json($result);
    }

    /**
     * Поиск товаров по строке
     */
    public function action_search()
    {
        if ( ! $q = $this->request->query('q')) {
            $search_error = 'Задан пустой поисковый запрос';
        }
        if (mb_strlen($q) < 3) {
            $search_error = 'Слишком короткий поисковый запрос, минимальная длина запроса 3 символа';
        }

        if (mb_convert_encoding($q, 'UTF-8', 'UTF-8') != $q) { // invalid multibyte
            throw new HTTP_Exception_404;
        }

        /*
        if (preg_match('~(памперс|pampers)~ius', $q)) { // кидаем в бренд-зону памперса
            $this->request->redirect(Route::url('pampers'));
        }
        */

        $this->tmpl['search_query'] = $q;

        if ( ! empty($search_error)) {

            $this->tmpl['search_error'] = $search_error;

        } else {

            $sphinx = new Sphinx('word', $q);
            $this->layout->menu = $sphinx->menu();
            $this->tmpl['search_result'] = $sphinx->search();
        }

        $this->layout->title = "Результаты поиска - Младенец. РУ";

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => $this->layout->title,
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:product/search', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }

        $this->layout->after_body = View::factory('smarty:retail_rocket/search');
    }

    /**
     * Хиты продаж
     */
    public function action_hitz()
    {
        $sphinx = new Sphinx('hitz');
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();

        $this->layout->title = "Хиты продаж - Младенец. РУ";

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => $this->layout->title,
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:product/hitz', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }

    /**
     * Форма отзыва о товаре
     * @throws HTTP_Exception_404
     */
    public function action_review()
    {
        $good_id = $this->request->param('id');
        $good = new Model_Good($good_id);
        if ( ! $good->loaded()) throw new HTTP_Exception_404;

        $m = new Model_Good_Review();
        $m->good_id = $good_id;
        $m->time = time();

        $view = View::factory('smarty:product/review')->bind('i', $m);
        $view->params = $params = Model_Section_Param::for_section($good->section_id); // параметры отзывов
        $view->good_id = $good->id;

        if ($this->request->post('send')) {

            $m->values($this->request->post());

            $captcha = $this->user ? TRUE : Captcha::check($this->request->post('captcha'));

            if ($m->validation()->check() AND $captcha) {

                if ($this->user) $m->user_id = $this->user->id;
                $m->save(); // сохранили текст отзыва

                $m->save_params($m->id, $good->section_id, $this->request->post()); // сохраним параметры отзыва

                $view->sent = $m->id;
                $this->return_html($view->render());

            } else {

                $errors = $m->validation()->errors('review/add');
                if ( ! $captcha) $errors['captcha'] = Kohana::message('captcha', 'captcha.default');
                $this->return_error($errors);

            }
        }
        exit($view->render());
    }

    /**
     * новинки
     */
    public function action_new()
    {
        $sphinx = new Sphinx('new');
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();

        $this->layout->title = "Новинки - Младенец. РУ";

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => $this->layout->title,
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:product/new', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }

    /**
     * новинки
     */
    public function action_superprice()
    {
        $sphinx = new Sphinx('superprice');
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();

        $this->layout->title = "Суперцена на Младенец. РУ";

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => $this->layout->title,
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:product/superprice', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }

    /**
     * скидки - каталог товаров со скидкой
     */
    public function action_discount()
    {
        $sphinx = new Sphinx('discount');
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();

        $this->layout->title = "Скидки на Младенец. РУ";

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => $this->layout->title,
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:product/discount', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }

    /**
     * Продукция по тегу (посадочная страница)
     * @throws HTTP_Exception_404
     */
	public function action_tag()
	{
        $tag_code = $this->request->param('code');

        if ($to = Model_Tag::check_redirect($tag_code)) {
            $this->request->redirect($to, 301);
        }

        $tag = new Model_Tag(array('code' => $tag_code));
        if ( ! $tag->loaded()) {
            Log::instance()->add(Log::INFO, 'tag not loaded '.$tag_code);
            throw new HTTP_Exception_404;
        }
        $this->tmpl['tag'] = $tag;

        $sphinx = new Sphinx('tag', $tag->id);
        $this->tmpl['search_result'] = $sphinx->search();
        $this->tmpl['found'] = $sphinx->found;

        $this->layout->menu = $sphinx->menu();

        $this->tmpl['tag_section'] = $sphinx->section();

        $this->layout->title = $tag->name;

        if ($sphinx->qs) { // есть параметры из командной строки - меняем титулы и прочее
            $seo = $sphinx_seo = $sphinx->seo();
            $this->layout->set($seo);

        } else {

            $seo = $tag->seo;
            $title = $seo->title;

            if ( ! empty($title)) {
                $this->layout->title = $title;
                $this->layout->description = $seo->description;
                $this->layout->keywords    = $seo->keywords;
            }
        }

		if (($p = $this->request->query('page')) && $p > 1) { // если мы не на первой странице - добавим текст в title
			/*
            if (empty($sphinx_seo)) {
                $seo = $sphinx->seo();
            } else {
                $seo = $sphinx_seo;
            }
			*/
			$this->layout->title .= ' (страница каталога №'.$p.') - Младенец.ру';
		}

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => ! empty($this->layout->title) ? $this->layout->title : 'Младенец. РУ',
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:product/tag', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }
	
	public function action_google_goods()
    {
		$ids = $this->request->post('id');

		$goods = [];
		if ( ! empty($ids) && is_array($ids)) {
			$goods = ORM::factory('good')->where('id', 'IN', $ids )->find_all()->as_array('id');
		}
		
		$return = [];
		
		foreach ($goods as $good) {
            $return[$good->id] = [
				'id' => $good->id,
				'name' => $good->group_name . ' ' . $good->name,
				"price" => $good->get_price(),
				"brand" => $good->brand->name,
				"category" => $good->section->name
			];
		}

		$this->return_json($return);
	}
	
	public static function cart_delivery()
    {
        $cart = Cart::instance();
        $cart->recount();// иначе не получим big

        $user = Model_User::current();
        $sessionParams = Session::instance()->get('cart_delivery');

        if ( ! empty($user)) {
            $sessionParams = array_merge([
                'delivery_type' => 0,
                'pay_type'      => Model_Order::PAY_DEFAULT,
                'description'   => ''
            ], empty($sessionParams) ? [] : $sessionParams );

            if (empty($sessionParams['pay_type'])) $sessionParams['pay_type'] = Model_Order::PAY_DEFAULT;
        }

        if(Conf::instance()->use_ozon_delivery != 0) {
            $cart->use_ozon_delivery = true;
            if(Session::instance()->get('city') == 'Москва') {
                $ozon_delivery = new OzonDelivery();
                $cart->ozon_terminals = $ozon_delivery->get_terminals();
                if(!$cart->ozon_terminals) $cart->use_ozon_delivery = false;
            }
        }

        $return = View::factory('smarty:cart/delivery', [
            'session_params' => $sessionParams,
            'user' => $user,
            'cart' => $cart,
        ])->render();

        return $return;
    }
	
    /**
     * Заказ в один клик. Принимает номер телефона и учитывает юзера, если есть. Создаёт заказ.
     * Работает через ajax, Возвращает json [order_id => , new_user => , error => , sum => ...]
     */
    public function action_one_click()
    {
        $return = [];

        $_phone = Txt::phone_clear($this->request->post('phone')); // телефон с +7...
        if ( ! $_phone) {
            $return['error'] = 'Некорректный номер телефона';
            $this->return_json($return);
        };
        $return['phone'] = $phone = substr($_phone, 2); // телефон без +7

        $cart = Cart::instance();

        $good_id = $this->request->post('good');
        $qty = $this->request->post('qty');
        if ($good_id) { // если пришли с карточки товара, то в корзину надо положить только этот товар
            $old_cart = serialize($cart);
            $cart->clean();
            $cart->add([$good_id => $qty ? $qty : 1]);
            $cart->get_qty();
        }

        if ($cart->get_qty() == 0) {
            $return['error'] = 'Не удалось оформить заказ - в заказе нет товаров';
            $this->return_json($return);
        }

        if (Model_User::logged()) { // оформляем заказ текущим пользователем
            $was_logged = TRUE;
            $user = Model_User::current();

        } else {                    // ищем пользователя с таким телефоном
            $user = ORM::factory('user')
                ->where('phone', '=', $_phone)
                ->or_where('phone2', '=', $_phone)
                ->limit(1)
                ->find();
        }

        if ( ! $user->loaded()) { // если пользователь есть - то берём пользователя, иначе создаём нового

            $salt = Text::random();
            $pass = Text::random('distinct', 8);

            $user = new Model_User();
            $user->values([
                'name'  => $phone,
                'login' => $phone,
                'phone' => $_phone,
                'password'  => $salt.md5($salt.$pass),
            ]);
            $user->save();
            Model_User::login($user); // тут сразу логинимся под ним - при логине пересчитается корзина и используются купоны лк!
			
            $return['new_user'] = ['login' => $phone, 'password' => $pass]; // если нет мыла - покажем логин-пароль
		} else {
            $found_by_phone = empty($was_logged);
        }
        if ( ! empty($found_by_phone) && ! empty($cart->coupon['type']) && $cart->coupon['type'] == Model_Coupon::TYPE_LK) {
            // в корзине есть купон на лк и юзер найден по телефону - тут надо этому юзеру дать лк и засчитать купон
            $c = new Model_Coupon($cart->coupon['id']);

            if ($user->status_id == 0 && $c->loaded() && $c->active) {
                $user->status_id = 1;
                $user->save();
                $c->used($user->id);
            }
        }
        $cart->recount(); // пересчитываем заказ, потому что у нас мог появиться юзер

        // создаём новый заказ
        $order = new Model_Order();
        $order->type = Model_Order::TYPE_ONECLICK;
        $order->user_id = $user->id;
        $order->discount = $cart->discount();
        $order->price = $cart->get_total();
        $order->user_status = $cart->status_id;
        $order->created = date('Y-m-d H:i:s');
        $order->save();

        $return['order_id'] = $order->id;
        $return['sum'] = $order->get_total();

        $order->save_goods($cart);

        $order_data = new Model_Order_Data();
        $order_data->id = $order->id;
        $order_data->phone = $_phone;
        $order_data->name = $user->name;
        $order_data->email = $user->email;
        $order_data->phone = $_phone;

        $central = new Model_Zone(Model_Zone::DEFAULT_ZONE);
        $order_data->ship_date = key($cart->allowed_date($central)); // дату доставки ставим ближайшую для центр. зоны

        if (Txt::phone_is_mobile($_phone)) $order_data->mobile_phone = $_phone;
        $order_data->client_data = print_r($_SERVER, TRUE);
        $order_data->save();

        // очищаем корзину или восстанавливаем корзину
        if (empty($old_cart)) {
            $cart->clean();
            $return['cart_clean'] = 1;
        } else {
            $cart = unserialize($old_cart);
            $cart->save();
        }

        // пишем оповещение нам
        $txt = 'Заказ '.$order->id. ' на номер '.$return['phone'];
        $mail = new Mail();
        $mail->send('1click@mladenec.ru', $txt);
        // и смс нам Model_Sms::to_queue('+79651824834', $txt, $user->id, $order->id);

        // и смс отправителю заказа
        $return['sms_sent'] = Model_Sms::to_queue($_phone,
            'Ожидайте звонок о заказе '.$order->id.' на сумму '.$order->get_total().'р.'
            .( ! empty($return['new_user']) ? 'Логин:'.$return['new_user']['login'].' Пароль:'.$return['new_user']['password'] : '')
            ."\nmladenec.ru",
            $user->id, $order->id
        );

	    $this->user = $user;
        if ( ! empty($return['new_user'])) Model_User::login($user);

		Session::instance()->set('show_ecommerce', $order->id); // чтобы сообщить все статистики по заказу
        $this->return_redirect(Route::url('order_detail', ['id' => $order->id, 'thanx' => 'thanx']));
    }

    public function action_add_deferred()
    {
        $id = $this->request->post('id');
        $doing = $this->request->post('doing');

        if (Model_User::logged()) {
            $user = Model_User::current();
            $deferred = new Model_Deferred();
            $deferred->good_id = $id;
            $deferred->user_id = $user->id;

            if ($doing === 'add') {
                $this->return_json($deferred->create());
            } elseif ($doing === 'delete'){
                $this->return_json($deferred->delete());
            }
        } else return FALSE;
    } 
}
