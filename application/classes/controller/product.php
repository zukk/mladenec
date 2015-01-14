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

        if ( ! $parent->loaded()) throw new HTTP_Exception_404;

        // redirect to canonical url
        if (($good->translit != $this->request->param('translit')) || ($section->vitrina != Kohana::$server_name) || $good->group_id != $this->request->param('group_id'))
            $this->request->redirect(Route::$default_protocol.Kohana::$hostnames[$section->vitrina]['host'].Route::url('product', $good->as_array()).($this->request->query('ajax') == 1 ? '?ajax=1' : ''), 301);

		$this->tmpl['tags'] = $tags = $good->get_tags(); // его теговые страницы
        $this->tmpl['notInSale'] = $last_seen = $good->not_in_sale(); // если нет в продаже - тут время когда был

        if ($last_seen && (time() - strtotime($last_seen)) > 6 * 30 * 24 * 60 * 60) { // если товара долго нет впродаже (6 месяцев)
            $redirUri = ( empty( $tags ) ) ? $section->get_link(false) : sprintf('/tag/%s.html', $tags[0]->code ); // редирект на теговую или на страницу категории
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

        $keys = array_keys($goods);

		if ($good->show) {

            $bundled_goods = $good->get_promo_goods(1); // Участвует ли товар в промоакциях?

			if ( ! empty($bundled_goods) && $good->qty != 0 ) { // да, и в наличии

				$this->tmpl['frequent'] = $bundled_goods;
				$bundled_ids = array();
				foreach($bundled_goods as $bg) $bundled_ids[] = $bg->id;
				$keys = array_merge($keys, $bundled_ids);

				$promos = $good->get_promos();

				foreach($promos as $prom) {
					$header = $prom->slider_header;
					if ( ! empty($header)) {
						$this->tmpl['slider_header'] = $header;
						break;
					}
				}

			} else {

				if ($good->qty == 0) { // нет в наличии
					$frequent = $good->analogy();
					$this->tmpl['slider_header'] = 'Аналогичные товары';
				} else {
					$frequent = $good->get_frequent();
					if ( ! empty($frequent)) $keys = array_merge($keys, array_keys($frequent));
				}

				$this->tmpl['frequent'] = $frequent;
			}

		} else { // товар отключен от показа - показываем аналоги
			
			$this->tmpl['frequent'] = $good->analogy();
			$this->tmpl['slider_header'] = 'Аналогичные товары';
		}

//        $this->tmpl['images'] = Model_Good::many_images(array(255), $keys);
        $this->tmpl['action_goods'] = Model_Action::by_goods($keys); // получим данные об акциях для всех товаров
        $this->tmpl['price'] = Model_Good::get_status_price(1, $keys); // цены
        $this->tmpl['serts'] = $group->get_serts( TRUE ); // сертификаты соответствия

        $filters = []; // {{{ показ фильтров в карточке товара
        $filter_data = $good->filters_data();
        foreach($filter_data as $vid => $data) {
            if ($good->is_cloth() && $data['filter_id'] == Model_Filter::CLOTH_BIG_TYPE) {
                $this->tmpl['big_filter'] = ['id' => $vid, 'value' => $data['value_name']];
            }
            if (empty($filters[$data['filter_name']])) $filters[$data['filter_name']] = [];
            $filters[$data['filter_name']][] = $data['value_name'];
        }
        $this->tmpl['filters'] = $filters;  // }}}

        $region = Session::instance()->get('region'); // регион пользователя
        $this->tmpl['can_one_click'] = $good->big && $good->price > 4000 && in_array($region, ['RU-MOW', 'RU-MOS']);

        if ($good->is_cloth()) { // для карточки одежды получаем все варианты цветов и размеров для товаров из группы, со связями цвет => размеры
			
			$color_size_data = $this->get_color_size($good->group_id);

			$this->tmpl['colorsize'] = $color_size_data['colorsize'];
			$this->tmpl['sizes'] = $color_size_data['sizes'];
            $this->tmpl['allsizes'] = $color_size_data['allsizes'];
            $this->tmpl['colors'] = $color_size_data['colors'];
            $this->tmpl['colorimage'] = $color_size_data['colorimage'];
            $this->tmpl['size_filter'] = $color_size_data['size_filter'];

            //print_r($color_size_data);
            if ($this->request->query('ajax') || $this->request->post('infancybox')) { // это смена цвета-размера в карточке
				$this->tmpl['price'] = Model_Good::get_status_price(1, $keys);
				$this->tmpl['infancybox'] = true;
				exit(View::factory('smarty:product/view/inner', $this->tmpl)->render());
			}
		}

        if ($this->request->query('ajax')) { // быстрый просмотр
            $this->tmpl['price'] = Model_Good::get_status_price(1, $keys);
            exit(View::factory('smarty:product/buy', $this->tmpl));
        }

        $this->layout->title = $good->seo->title ? $good->seo->title : $group->name.' '.$good->name;
        if ( ! empty($good->seo->description)) $this->layout->description = $good->seo->description;
        if ( ! empty($good->seo->keywords)) $this->layout->keywords = $good->seo->keywords;
		
		if ($this->request->post('isajax')){
            exit(View::factory('smarty:product/view/inner', $this->tmpl)->render());
		}
    }

    /**
     * Получить массив всех значений цветов => размеров для одной товарной группы, цвета есть всегда
     * @param int $group_id
     * @return array [colorsize => , colors => , sizes => , allsizes => , colorimage => , sizefilter => ]
     */
	protected function get_color_size($group_id)
    {
		$return = ['colorsize' => '', 'colors' => '', 'sizes' => '', 'allsizes' => '', 'colorimage' => '', 'size_filter' => ''];

        $colors = DB::select('good_id', 'color')
            ->from('good_color')
            ->where('group_id', '=', $group_id)
            ->execute()
            ->as_array('good_id', 'color');

        $colorimage = array();
        foreach($colors as $good_id => $color) { // собираем по цветам
            if (empty($color)) $color = $good_id;
            if (empty($colorimage[$color])) $colorimage[$color] = $good_id;
            $return['colors'][$color][] = $good_id;
        }

        if (empty($colors)) return $return;

        // получаем все размеры для этих товаров
        $sizes = DB::select(
            ['v.id', 'vid'],
            ['f.id', 'filter_id'],
            ['f.name', 'filter_name'],
            ['v.name', 'value_name'],
            'r.good_id'
        )
        ->from(['z_good_filter','r'])
        ->join(['z_filter_value', 'v'])
            ->on('v.id', '=', 'r.value_id')
        ->join(['z_filter', 'f'])
            ->on('f.id', '=', 'r.filter_id')
        ->where('good_id', 'in' , array_keys($colors))
            ->order_by('f.sort', 'DESC')
            ->order_by('v.sort', 'ASC')
            ->order_by('v.name', 'ASC')
            ->where('r.filter_id', 'in', [self::FILTER_SIZE, self::FILTER_GROWTH, self::FILTER_AGE])
        ->execute()
        ->as_array();

        if ( ! empty($sizes)) {
            // выбираем что первое попадёт - рост или размер или возраст
            $return['size_filter'] = $size_filter = $sizes[0]['filter_id'];

            foreach($sizes as $data) { // и заполняем связь товар -> размеры
                if ($data['filter_id'] == $size_filter) {
                    $return['sizes'][$data['good_id']][$data['vid']] = $data['value_name'];
                    $return['allsizes'][$data['vid']] = $data['value_name'];
                }
            }
            // теперь заполняем цветам какому размеру какой товар
            foreach($return['colors'] as $color => $goodz) {
                foreach($goodz as $gid) {
                    if ( ! empty($return['sizes'][$gid])) {
                        foreach ($return['sizes'][$gid] as $vid => $vname) {
                            $return['colorsize'][$color][$vid][] = $gid;
                        }
                    }
                }
            }
        }

        $images = Model_Good::many_images([70], $colorimage); // получим картинки для всех разных цветов
        foreach($colorimage as $color => $good_id) {
            $return['colorimage'][$color] = $images[$good_id][70]->get_img(0);
        }

        return $return;
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

		$this->tmpl['filters'] = $good->get_filters($section);

        $this->layout = View::factory('smarty:layout/1c');
        $this->layout->body = View::factory('smarty:product/view', $this->tmpl)->render();
        $this->layout->cart = null;
    }
	
	public function action_thank_you2( $id = 0 ){
		return $this->action_thank_you($id);
	}
	
	public function action_thank_you( $id = 0 ){
		if( empty( $id ) ){
	        $order_id = $this->request->param('id');
		}
		else
			$order_id = $id;
		
        if (empty($order_id)) throw new HTTP_Exception_404;
        $order = new Model_Order($order_id);
        if ( ! $order->loaded()) throw new HTTP_Exception_404;
		
        if (empty($this->user) /* && $order->type != 1 */) throw new HTTP_Exception_403;
        $this->tmpl['user'] = $this->user;
		$this->tmpl['phone'] = $this->user->phone;

        if ( /* $order->type != 1 && */ $order->user_id != $this->user->id)  throw new HTTP_Exception_403;

        $this->tmpl['order_goods'] = $order->get_goods();
		$this->tmpl['thanx'] = true;
		$this->tmpl['can_poll'] = true;
		
        if ($this->request->param('thanx')) { // это спасибо-страница?

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
		
		return $this->tmpl;
	}
	
	protected function _create_order( &$post ){
		
		$cart = Cart::instance();
		$user = Model_User::current();
		$post = $this->request->post();
		$post['latlong'] = implode(',', array_reverse( explode( ',', $post['latlong'] ) ) );
		
		if( !empty( $post['select_present'] ) )
			$cart->select_presents($post['select_present']);
		
		if( empty( $post['address_id'] ) || $post['address_id'] == -1 ){
			$a = new Model_User_Address();
			$a->values($post);
			$a->user_id = $this->user->id;
			$a->save();
			$post['address_id'] = $a->id;
		}
		
		/* if( empty( $post['ship_zone'] ) ){
			$post['ship_zone'] = Model_Zone::ZAMKAD;
		} */
		
		$order_data = new Model_Order_Data();
		$order_data->values($post);
		$dt = $this->request->post('delivery_type');

		switch($dt) {
			case Model_Order::SHIP_COURIER: // доставка курьерской службой
			case Model_Order::SHIP_SERVICE: // доставка через транспортную компанию

				if ($post['address_id'] > 0 ) { // старый адрес!
					$addr = new Model_User_Address($post['address_id']);
					if ( !$addr->loaded()) throw new HTTP_Exception_404; // no address found
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
		
		if ($v->check()){
			$o = new Model_Order();
			$o->delivery_type = (int)$post['delivery_type'];
			$o->price = $cart->get_total();
			$o->discount = $cart->discount;
			$o->description = htmlspecialchars($post['description']);
			$o->user_id = $user->id;

			$od = $order_data->as_array() +
				array(
					'delivery_type' => $dt,
					'address_id' => $post['address_id']
				);

			Session::instance()->set('od', json_encode($od))->write();

			if (in_array($o->delivery_type, array(Model_Order::SHIP_COURIER, Model_Order::SHIP_SERVICE))) { // доставка по адресу

				// вычисление стоимости доставки
				if ($o->delivery_type == Model_Order::SHIP_COURIER) {
					$o->price_ship = $o->price_ship($order_data);
				}
				if ($o->delivery_type == Model_Order::SHIP_SERVICE) { // доставка через edost
					$order_data->comment = '';
					$o->price_ship = 0;

					$tarif_id = Session::instance()->get('tarif_id');
					$edost = Session::instance()->get('edost');

					if ( ! empty($edost) AND ! empty($tarif_id)) {
						if ($tarif = $edost[$tarif_id]) {
							$order_data->comment = $tarif['company'].', '.$tarif['name'];
							$o->price_ship = $tarif['price'];
						}
					}
				}

			}
			else if ($o->delivery_type == Model_Order::SHIP_SELF) { // самовывоз
				$o->price_ship = 0;
				$order_data->address = Model_Order::is_shop($post['address_id']);
			}

			$pt =& $post['pay_type'];

			if ($o->delivery_type == Model_Order::SHIP_SERVICE) {
				$pt = Model_Order::PAY_CARD; // только картой

			} elseif ($o->delivery_type == Model_Order::SHIP_COURIER) { // картой или налом
				if ( ! in_array($pt, array(Model_Order::PAY_DEFAULT, Model_Order::PAY_CARD))) $pt = Model_Order::PAY_DEFAULT; // по умолчанию - наличка

			} elseif($o->delivery_type == Model_Order::SHIP_SELF) {
				$pt = Model_Order::PAY_DEFAULT; // только наличкой
			}

			$o->values(array(
				// 'user_id' => $this->user->id, - из post
				'user_status' => $cart->status_id,
				// 'description' => $this->request->post('description'), - из post
				'status' => $pt == Model_Order::PAY_CARD ? 'C' : 'N', // при оплате по карте - свой статус
				'status_time' => date("Y-m-d H:i:s"),
				'pay_type' => $pt,
			));

			/**
			 * Проверка дат
			 */

			$setNextDate = false;
			$today_time = time();

			if( $order_data->ship_zone ){

				$Zone = ORM::factory('zone', $order_data->ship_zone);
				$dates = $Zone->allowed_date( $cart );
				list($first_date) = each( $dates );
			}
			else
				$first_date = date('Y-m-d',strtotime('tomorrow'));

			if( empty($order_data->ship_date) ){
				
				$setNextDate = true;
			}

			else{

				// Если это заказ из прошлого, то ставим первую дату из открытых
				$ship_time = strtotime( $order_data->ship_date );

				if( $ship_time < $today_time || ( $order_data->ship_date == date('Y-m-d') && time() >= $today_time + 43200 ) ){
					$setNextDate = true;
				}
			}

			if ($setNextDate){

				$order_data->ship_date = $first_date;
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

			$order_data->id = $o->id;

			if ( !empty($a)) {
				$order_data->address_id = $a->id;
			}

			$order_data->save();

			$cart->clean(); // чистка корзины!
			Session::instance()->delete('cart_delivery');

			if ($pt == Model_Order::PAY_CARD) { // Переходим к оплате по карте
				Session::instance()->set('order_id', $o->id); // у нас в сессии теперь висит заказ с незавершённой оплатой
				$this->return_redirect(Route::url('payment'));
			} else {
				/* Отправляем СМС о принятом заказе */
				$o->send_sms_accepted();
			}

			$mail_params = array(
				'o' => $o,
				'od' => $order_data,
				'coupon' => $o->coupon_id ? $o->coupon : FALSE
			);
			if ( ! empty($big_to_wait)) $mail_params['big_to_wait'] = $big_to_wait;
			Mail::htmlsend('order', $mail_params, $order_data->email, 'Ваш заказ '.$o->id.' принят');

			Session::instance()->set('thanx', $o->id);

			$params = $this->action_thank_you($o->id);
			$params['is_new'] = true;
			
			$this->return_json([
				'thank_you' => View::factory('smarty:product/thank_you2', $params)->render(),
				'redirect' => Route::url('order_detail_thanx', array('id' => $o->id, 'thanx' => 'thanx'))
			]);
			
			// $this->return_redirect(Route::url('order_detail', array('id' => $o->id, 'thanx' => 'thanx'))); // идём на страницу спасибо

			exit;
			
		} else {
			$this->return_error($v->errors('order/order_data'));
		}
		
		exit;
	}
	
	public function action_cart2()
    {
        $this->layout->title = 'Ваша корзина';
		
		$post = $this->request->post();
		$cart = Cart::instance();

        if( ! empty($post['delivery_type']) && ! empty($post['agree'])) {
			if( ! empty($cart->goods)) {
				try {
					$this->_create_order($post);
				} catch (ORM_Validation_Exception $e) {
					$errors = $e->errors();
					$e = [];
					foreach( $errors as $field => &$error ){
					
						$e[$field] = "Заполните"; // $error[0];
					}
					unset( $error );
					$this->return_error(['address' => $e]);
					exit;
				}
			} else {
				$this->return_error(['cartempty' => 'Корзина пуста']);
				exit;
			}
		
		} else {
			$this->return_error(array('agree' => 'Для отправки заказа Вы&nbsp;должны согласиться с&nbsp;пользовательским соглашением.'));
		}
		
		Session::instance()->set('od_time', time());
		$cartParams = $this->cartParams();
		$this->tmpl = $cartParams;
		
		$this->cart_slider($cartParams['goods']);
	}
	
	public function action_cart_sync(){
		
		$Session = Session::instance();
		$r = &$this->request;
		$data = [
			'delivery_type' => (int)$r->post('delivery_type'),
			'pay_type' => (int)$r->post('pay_type'),
			'description' => $r->post('description'),
			'select_present' => $r->post('select_present')
		];
		
		$Session->set( 'cart_delivery', $data );
		exit;
	}

	public function action_cart_clear(){
		
        $cart = Cart::instance()->clean();
		$this->tmpl['sync'] = 'cart';
		
		exit(View::factory('smarty:product/cart2')->render());
	}
	
	public function action_cart_remove_good(){
		
		Session::instance()->set('od_time', time() );
        $cart = Cart::instance();
		$goods = $cart->remove($this->request->query('id'))->save();
		$this->tmpl['sync'] = 'cart';
		
		$json = [];
		
		$json['cart'] = View::factory('smarty:product/cart2', $this->cartParams())->render();
		
		exit(json_encode($json));
	}

	protected function cartParams(){
		
        $cart = Cart::instance();
		$goods = $cart->recount();
		$user = Model_User::current();
		$sessionParams = Session::instance()->get('cart_delivery');
		$open_delivery = $this->request->post('open_delivery');
		
        $region = Session::instance()->get('region'); // регион пользователя
        $can_one_click = in_array($region, ['RU-MOW', 'RU-MOS']);
		
		return [
			'goods' => $goods,
			'cart' => $cart,
			'promo' => $cart->promo,
			'blago' => $cart->blago,
			'presents' => $cart->check_actions($goods),
			'present_goods' => $cart->get_present_goods(),
			'coupon_error' => false,
			'comments' => $cart->get_comments(),
			'session_params' => $sessionParams,
			'open_delivery' => !empty( $open_delivery ),
			'delivery' => !empty( $user ) ? $this->cart_delivery(): View::factory('smarty:averburg/cart/user', ['can_one_click' => $can_one_click])
		];
	}
	
	public function action_cart_coupon(){
		
		$cart = Cart::instance();
		$params = [];
		Session::instance()->set('od_time', time() );
		
		if ( empty($cart->coupon) && $this->request->post('coupon')) { // обработка скидочных купонов

			$coupon_error = FALSE;
			$coupon = ORM::factory('coupon')
				->where('name',  '=', $this->request->post('coupon'))
				->limit(1)
				->find();

			if ( ! $coupon->loaded()) {
				$coupon_error = 'Купон с таким кодом не найден';
			} elseif ( ! $coupon->is_usable($cart->get_total())) {
				$coupon_error = 'Вы не можете использовать этот купон';
			}
			if (empty($coupon_error)) {
				$cart->add_coupon($coupon);
			} else {
				$params['coupon_error'] = $coupon_error;
			}
		}
		else if ( !empty($cart->coupon) && $this->request->post('remove')) {
			$cart->remove_coupon();
		}
		
		$cart->save();

		$params = array_merge( $this->cartParams(), $params );
		exit(View::factory('smarty:product/cart2', $params)->render());
	}
	
	public function action_cart_comments(){
		Cart::instance()->set_comments($this->request->post('comment'))->save();
		exit;
	}
	
	public function action_cart_recount()
    {
        $cart = Cart::instance();
		Session::instance()->set('od_time', time() );
		
        $hash = $this->request->post('hash');
        
		if( $inc = $this->request->post('inc') )
			$cart->inc($inc)->save();
		else if( $dec = $this->request->post('dec') )
			$cart->dec($dec)->save();
		else if( $change = $this->request->post('change') ){
			$cart->set_good_qty($change, $this->request->post('value'))->save();
		}
		
		$params = $this->cartParams();
		
        $goods = $cart->recount();
        $cart->save();
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
        
		$pricesNums = [];
			
        foreach( $goods as $id => &$g ){
			$pricesNums[$id] = $g->price;
		}
		unset( $g );

		$pricesNums['blago'] = '1';
		$goods = $cart->goods;
		$goods['blago'] = $cart->blago;
		
		$totals = [];
		foreach( $goods as $goodId => $count ){
			$totals[$goodId] = $count * $pricesNums[$goodId];
		}

		if( $this->request->post('all_cart') ){
			
			$json['cart'] = View::factory('smarty:product/cart2', $params)->render();

			exit(json_encode($json));
		}
		else{
            
			$this->return_json(array(
				'header'        => View::factory('smarty:averburg/cart/header', $params)->render(),
				'delivery'      => $this->cart_delivery(),
				'goods'         => $goods,
				'discount'      => $cart->discount,
				'no_possible'   => $cart->no_possible,
				'hash'          => $hash,
				'prices'        => $pricesNums,
				'totals'        => $totals,
				'total'         => $cart->get_total(),
                'presents_html' => View::factory('smarty:averburg/cart/gifts', $params)->render()
            ));
		}
		// exit(View::factory('smarty:product/cart2', $this->cartParams())->render());
	}
	
	protected function cart_slider( &$goods ){
		
		$promos = [];
		foreach( $goods as &$g ){
		
			$promos = array_merge( $promos, $g->get_promos() );
		}
		unset( $g );
		
		$slider_goods = [];
		$this->tmpl['cart_slide_method'] = 'cart_set';
		
		if( !empty( $promos ) ){
			
			$this->tmpl['cart_slide_method'] = 'cart2_set';
			
			$prmkey = rand(0, count( $promos ) - 1 );
			$promo = $promos[$prmkey];
			$slider_goods = $promo->get_goods();
			
			$cartIds = array_keys( $goods );
			foreach( $slider_goods as $y => &$sg ){
				if( in_array( $sg->id, $cartIds ) )
					unset( $slider_goods[$y] );
			}
			unset( $sg );
			
			$sliderCount = count( $slider_goods );
			$slider_goods = array_slice($slider_goods, 0, 5);
			
			if( !empty( $promo->slider_header ) ){
				$this->tmpl['slider_id']   = $prmkey;
				$this->tmpl['slider_name'] = $promo->slider_header;
			}
			
			$this->tmpl['cart_slider_page']  = 1;
		}
		else{
			
			// Slider {{{
			$sets = ORM::factory('good_set')
					->where('active','=',1)
					->where('cart','=',1)
					->order_by('id','DESC')
					->find_all()->as_array();

			$sets_count = count($sets);
			if ($sets_count >= 1) {
				$set = $sets[rand(0,count($sets)-1)];
				if ( ! empty($set) AND ($set instanceof Model_Good_Set) AND $set->loaded()) {
					$slider_page = rand(-10, 10);
					$slider_goods = Model_Good::get_set_slider($set->pk(), $total, $slider_page, 5, TRUE, array_keys($goods));

					if ( ! empty($slider_goods)) {
						$this->tmpl['slider_id']   = $set->id;
						$this->tmpl['slider_name'] = $set->name;
						$slider_good_ids = array();
						foreach($slider_goods as $sg) $slider_good_ids[] = $sg->id;

						// цены для любимых клиентов
						if ( ! empty($slider_good_ids)) {
							$this->tmpl['price']= Model_Good::get_status_price(1, $slider_good_ids);
							$this->tmpl['imgs']= Model_Good::many_images(array(255), $slider_good_ids);
						}

						$this->tmpl['cart_slider_page']  = $slider_page;
					}
				}
			}
		}
		
		$this->tmpl['cart_slider_goods'] = $slider_goods;
		
		// если нет, то число больше пяти
		if( !empty( $sliderCount ) )
			$this->tmpl['cart_slider_count'] = $sliderCount;
	}

    /**
     * Просмотр текущей корзины товаров
     */
    public function action_cart()
    {
        $this->layout->title = 'Ваша корзина';

        $cart = Cart::instance();
		
        if (($this->request->post('recount') || $this->request->post('order')) && $this->request->post('qty')) { // пересчёт корзины
            $qty = $this->request->post('qty'); // [$id=>$qty]
            if ($remove = $this->request->post('remove')) {
                foreach($remove as $id) if (isset($qty[$id])) unset($qty[$id]);
            }
            if ($remove = $this->request->post('remove_present')) {
                foreach($remove as $id) $cart->no_presents[$id] = $id;
            } else {
                $cart->no_presents = array(); // все подарки можно!
            }

            if ( ! empty($cart->coupon['name'])) $this->request->post('coupon', $cart->coupon['name']); // запомним, если в корзине был купон

            $goods = $cart->clean()->add($qty, FALSE); // товары подложим снова

            $cart->select_presents($this->request->post('select_present'));

            if (empty($cart->coupon) && $this->request->post('coupon')) { // обработка скидочных купонов

                $coupon_error = FALSE;
                $coupon = ORM::factory('coupon')
                    ->where('name',  '=', $this->request->post('coupon'))
                    ->limit(1)
                    ->find();

                if ( ! $coupon->loaded()) {
                    $coupon_error = 'Купон с таким кодом не найден';
                } elseif ( ! $coupon->is_usable($cart->get_total())) {
                    $coupon_error = 'Вы не можете использовать этот купон';
                }
                if (empty($coupon_error)) {
                    $cart->add_coupon($coupon);
                } else {
                    $this->tmpl['coupon_error'] = $coupon_error;
                }
            }
            if ( ! empty($cart->coupon) && $this->request->post('remove_coupon')) {
                $cart->remove_coupon();
            }
            $cart->save();

        } else {
            $goods = $cart->recount();
        }
        // Прописываем комментарии в объекте корзины
        $cart->set_comments($this->request->post('comment'));

        if ($this->request->post('order')) {
			
			$redirUrl = Route::url('order');
			
            $this->request->redirect($redirUrl); // переходим к оформлению заказа
        }

        // подарки по акциям - пересчитываем всегда на этой странице
        if ( ! empty($goods)) {
                
            $this->tmpl['presents'] = $cart->check_actions($goods);
            $this->tmpl['present_goods'] = $cart->get_present_goods();
            
            if ( ! empty($cart->promo)) {
                $this->tmpl['promo'] = $cart->promo;
            }
        }

        $this->tmpl['goods'] = $goods;
        $this->tmpl['comments'] = $cart->get_comments();
        $this->tmpl['cart'] = $cart->save();
		
		$this->cart_slider( $goods );
		
		$this->tmpl['user'] = Model_User::current();
		
        // }} slider
        $this->layout->cart = $cart->__toString();
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

        if (preg_match('~(памперс|pampers)~ius', $q)) { // кидаем в бренд-зону памперса
            $this->request->redirect(Route::url('pampers'));
        }

        $this->tmpl['search_query'] = $q;

        if ( ! empty($search_error)) {

            $this->tmpl['search_error'] = $search_error;

        } else {

            $sphinx = new Sphinx('word', $q);
            $this->layout->menu = $sphinx->menu();
            $this->tmpl['search_result'] = $sphinx->search();
        }
    }

    /**
     * Хиты продаж
     */
    public function action_hitz()
    {
        $sphinx = new Sphinx('hitz');
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();
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

                $view->sent = TRUE;
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
        $this->layout->title = 'Новинки';

        $sphinx = new Sphinx('new');
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();
    }

    /**
     * новинки
     */
    public function action_superprice()
    {
        $this->layout->title = 'Суперцена';

        $sphinx = new Sphinx('superprice');
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();
    }

    /**
     * скидки - каталог товаров со скидкой
     */
    public function action_discount()
    {
        $this->layout->title = 'Скидки';

        $sphinx = new Sphinx('discount');
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();
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
     * Продукция по тегу (посадочная страница)
     * @throws HTTP_Exception_404
     */
    public function action_tag()
    {
        $tag_code = $this->request->param('code');

        if ($to = Model_Tag::check_redirect($tag_code)) {
            $this->request->redirect($to, 301);
        };

        $tag = new Model_Tag(array('code' => $tag_code));
        if ( ! $tag->loaded()) throw new HTTP_Exception_404;
        $this->tmpl['tag'] = $tag;

        $sphinx = new Sphinx('tag', $tag->id);
        $this->tmpl['search_result'] = $sphinx->search();
        $this->layout->menu = $sphinx->menu();
        $this->tmpl['tag_section'] = $sphinx->section();

        $this->layout->title = $tag->title ? $tag->title : $tag->name;
        if ( ! empty($tag->description)) $this->layout->description = $tag->description;
    }
	
	public static function cart_delivery(){

		$returner = '';
		$user = Model_User::current();
		
		if( !empty( $user ) ){
			
			$sessionParams = Session::instance()->get('cart_delivery');
			$sessionParams = array_merge([
				'delivery_type' => 2,
				'pay_type' => Model_Order::PAY_DEFAULT,
				'description' => ''
			], empty( $sessionParams ) ? []: $sessionParams );
			if( empty( $sessionParams['pay_type'] ) )
				$sessionParams['pay_type'] = Model_Order::PAY_DEFAULT;

			if( empty( $sessionParams['delivery_type'] ) || $sessionParams['delivery_type'] == 4 )
				$sessionParams['delivery_type'] = 2;

			$returner = View::factory('smarty:averburg/cart/delivery', [
					'addresses' => ORM::factory('user_address')
						->where('user_id', '=', $user->id )
						->where('active', '=', 1)
						->where('latlong', '!=', 'TRANSPORT')
						->order_by('id', 'DESC')
						->limit(5)
						->find_all()->as_array(),
					'address' => ORM::factory('user_address')
						->where('user_id', '=', $user->id )
						->where('active', '=', 1)
						->where('latlong', '=', 'TRANSPORT')
						->order_by('id', 'DESC')
						->limit(5)
						->find_all()->as_array(),
					'session_params' => $sessionParams,
					'user' => $user,
					'cart' => Cart::instance()
				])->render();
		}
		
		return $returner;
	}
	
    /**
     * Заказ в один клик. Принимает номер телефона и учитывает юзера, если есть. Создаёт заказ.
     * Работает через ajax, Возвращает json [order_id => , new_user => , error => , sum => ...]
     */
    public function action_one_click(){
        Log::instance()->add(Log::INFO, 'oneclick!');
        
        $return = [];

        $phone = $this->request->post('phone');
        if ( ! Valid::phone($phone, 10)) {
            $return['error'] = 'Некорректный номер телефона';
            $this->return_json($return);
        };
        $return['phone'] = $phone;
        $phone = preg_replace('~\D+~', '', $phone); // оставляем только цифры
        $_phone = '+7'.$phone;

        if ( ! Txt::phone_is_ru($_phone)) {
            $return['error'] = 'Некорректный номер телефона';
            $this->return_json($return);
        }

        $cart = Cart::instance();

        $good_id = $this->request->post('good');
        if ($good_id) { // если пришли с карточки товара, то в корзину надо положить только этот товар
            $old_cart = serialize($cart);
            $cart->clean();
            $cart->add([$good_id => $this->request->post('qty')]);
        }

        if ($cart->get_qty() == 0) {
            $return['error'] = 'Не удалось оформить заказ - в заказе нет товаров';
            $this->return_json($return);
        }

        if (Model_User::logged()) { // оформляем заказ текущим пользователем

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
                'email' => $phone.'@mladenec.user',
                'login' => $phone,
                'phone' => $_phone,
                'password'  => $salt.md5($salt.$pass),
            ]);
            $user->save();
			
            // плохая идея передавать пароль по сети $return['new_user'] = ['login' => $phone, 'password' => $pass];
			$user = Model_User::login(array('login' => $phone, 'password' => $pass));
			
			// $return['u'] = $user->id;
			$return['userpad'] = Controller_User::userpad();
        }

        $cart->recount(); // пересчитываем заказ, потому что у нас мог появиться юзер

        // создаём новый заказ
        $order = new Model_Order();
        $order->type = Model_Order::TYPE_ONECLICK;
        $order->user_id = $user->id;
        $order->discount = $cart->discount();
        $order->price = $cart->get_total();
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
        $order_data->ship_date = key($central->allowed_date()); // дату доставки ставим ближайшую для центр. зоны

        if (Txt::phone_is_mobile($_phone)) $order_data->mobile_phone = $_phone;
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
        // и смс нам
        Model_Sms::to_queue('+79651824834', $txt, $user->id, $order->id);

        // и смс отправителю заказа
        $return['sms_sent'] = Model_Sms::to_queue($_phone,
            'Ожидайте звонок о заказе М'.$order->id.' на сумму '.$order->get_total().'р. для уточнения адреса и даты доставки.'
            .( ! empty($return['new_user']) ? 'Логин:'.$return['new_user']['login'].' Пароль:'.$return['new_user']['password'].' ' : '')
            .'Ваш www.mladenec.ru',
            $user->id, $order->id
        );

		$this->user = $user;
		$params = $this->action_thank_you($order->id);
		$params['is_new'] = true;
		
		$return['thank_you'] = View::factory('smarty:product/thank_you2', $params)->render();
		$return['redirect'] = Route::url('order_detail_thanx', array('id' => $order->id, 'thanx' => 'thanx'));

        $this->return_json($return);
    }
}
