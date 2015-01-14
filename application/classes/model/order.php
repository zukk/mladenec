<?php
class Model_Order extends ORM {

    const TYPE_ONECLICK = 1; // тип заказа = заказ в один клик

    const PAY_DEFAULT = 1; // оплата наличными при получении
    const PAY_CARD = 8; // оплата картой
    const PAY_CARD_LATER = 9; // оплата картой после сборки заказа
    const PAY_BANK = 5; // оплата банковским переводом

    const SHIP_COURIER = 2; // доставка курьером
    const SHIP_SERVICE = 3; // доставка транспортной компанией (через dpd)
    const SHIP_SELF = 4; // самовывоз - (сейчас не используется)

    const EDOST_ID = 1499;
    const EDOST_PSWD = 'OMY9Fmx6y9gqrX66tSSVWVMq3cPR0fCy';
    const EDOST_URL = 'http://www.edost.ru/edost_calc_kln.php';

    const PRICE_KM = 20;

    protected $_table_name = 'z_order';

    protected $_table_columns = array(
        'id' => '', 'type' => '', 'user_id' => '', 'user_status' => '', 'created' => '', 'changed' => '',  'description' => '',
        'manager' => '', 'price' => '',	'discount' => '', 'price_ship' => '', 'status' => '', 'status_time' => '',
        'pay_type' => '', 'payment' => '', 'delivery_type' => '', 'vitrina' => '', 'coupon_id' => ''
    );

    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
        'coupon' => array('model' => 'coupon', 'foreign_key' => 'coupon_id'),
    );

    protected $_has_many = array(
        'goods' => array(
            'model'         => 'good',
            'through'       => 'z_order_good',
            'foreign_key'   => 'order_id',
            'far_key'       => 'good_id',
        ),
    );

    protected $_has_one = array(
        'data' => array(
            'model' => 'order_data',
            'foreign_key' => 'id',
        ),
        'logistic' => array(
            'model' => 'order_logistic',
            'foreign_key' => 'order_id',
        ),
        'card' => array(
            'model' => 'payment',
            'foreign_key' => 'order_id',
        )
    );

    public $qty = 0;
    public $total = 0;

    public function rules()
    {
        return array(
            'user_id' => array(
                array('not_empty'),
            ),
            'price' => array(
                array('not_empty'),
            ),
        );
    }

    public function get_link($html = true)
    {
        $href = sprintf('/account/order/%d', $this->id);
        return $html ? HTML::anchor($href, $this->id) : $href;
    }

    public static function get_status_list() {
         static $stats = array(
            'N' => 'Принят',
            'C' => 'Ожидает оплаты картой',
            'S' => 'В обработке',
            'D' => 'Сформирован',
            'X' => 'Отменён',
            'F' => 'Выполнен',
        );
         
        return $stats;
    }
    
    /**
     * Возвращает строку статуса
     * @return mixed
     * @throws ErrorException
     */
    public function status()
    {
        $stats = self::get_status_list();
        if (empty($stats[$this->status])) throw new ErrorException('bad status');
        return $stats[$this->status];
    }
    
    public function on_status_change()
    {
        $got_status = FALSE;
            
        if(in_array($this->status, array('D', 'X', 'F'))) {
            
            
            if ('F' == $this->status) { // заказ доставлен 

                // пересчитаем сумму для юзера
                $this->user->sum += $this->get_total();

                if (($this->user->status_id == 0) AND ($this->user->sum >= Model_User::STATUS_CHANGE)) { // меняем статус

                    $this->user->status_id = 1;
                    $this->user->save();

                    $got_status = TRUE;
                }
                $this->user->save();

                
            }
            
            $this->on_status_change_email($got_status);
        }
        
        // Пересчитаем накопления по акцииям
        // Накопления пересчитываются при всех сменах статусов, чтобы корректно отображались накопленные баллы
        
        $user_action_credits = Model_Action::get_user_credits($this->user->pk());

        foreach ($user_action_credits as $action_id=>$credits) {
            $user_action = DB::select(DB::expr('MAX(`order_id`) as `from_order`'))
                    ->from('z_order_good')
                    ->where('action_id', '=', $action_id)
                    ->execute()->as_array();

            $from_order = 0;
            if ( ! empty($user_action['from_order'])) $from_order = $user_action['from_order'];

            DB::delete('z_action_user')
                    ->where('user_id', '=', $this->user->pk())
                    ->where('action_id', '=', $action_id)
                    ->execute();

            DB::insert('z_action_user')
                    ->columns(array('action_id','user_id','from_order','sum','qty'))
                    ->values(array(
                        'action_id'  => $action_id,
                        'user_id'    => $this->user->pk(),
                        'from_order' => $from_order,
                        'sum'        => empty($credits['sum'])?0:$credits['sum'],
                        'qty'        => empty($credits['qty'])?0:$credits['qty']
                    ))->execute();
        }
    }
    
    private function on_status_change_email($got_status)
    {
        
        $mail_values = array('o' => $this, 'od' => $this->data); // что передадим в почту
		$mail_subj = 'Ваш заказ '.$this->id.' '.$this->status();

        if ($got_status) $mail_values['got_status'] = TRUE;
        
        if ('F' == $this->status) { // заказ доставлен 

            // для этого статуса заголовок зависит от типа доставки
            if (self::SHIP_SELF == $this->delivery_type) {

                $shop_user = ORM::factory('user', $mail_values['od']->address_id); // данные магазина
                $mail_values['shop_phone'] = $shop_user->phone;
                $mail_subj = 'Ваш заказ '.$this->id.' отправлен на пункт самовывоза';

            } elseif (self::SHIP_SERVICE == $this->delivery_type) {

                $mail_subj = 'Ваш заказ '.$this->id.' передан в транспортную компанию';
            }
        }
        
        Mail::htmlsend('order', $mail_values, $this->data->email, $mail_subj);
    }
    
    /**
     * Пытается отправить СМС о том что заказ принят
     */
    public function send_sms_accepted() {
        if ($this->delivery_type == Model_Order::SHIP_SERVICE) return FALSE;

        $user = ORM::factory('user', $this->user_id);
        if ( ! $user->loaded()) return FALSE;
        
        $order_data = ORM::factory('order_data', $this->id);
        if ( ! $order_data->loaded()) return FALSE;
        
        /* Отправляем СМС о принятом заказе */
        if ($user->order_notify < 2) {
            if (Txt::phone_is_mobile($order_data->phone)) {
                $mobile_phone = $order_data->phone;
            } elseif (Txt::phone_is_mobile($order_data->phone2)) {
                $mobile_phone = $order_data->phone2;
            }
            if ( ! empty($mobile_phone)) {
                $sms_text = View::factory('smarty:sms/accepted', array('o' => $this, 'od' => $order_data))->render();
                Model_Sms::to_queue($mobile_phone, $sms_text, $this->user_id, $this->id);
            }
        }
    }
    
    /**
     * Получить список товаров заказа
     * @return $this
     */
    public function get_goods()
    {
        $res = DB::query(Database::SELECT, 'SELECT `good_id`, `price`, `quantity`, `comment` FROM z_order_good WHERE order_id = '.$this->id)->execute();
        $good_q = $res->as_array('good_id');
        if (empty($good_q)) return array();

        $goods = ORM::factory('good')->where('id', 'IN', array_keys($good_q))->find_all();
        $return = array();
        foreach($goods as $g) {
            $g->quantity = $good_q[$g->id]['quantity'];
            $g->price = $good_q[$g->id]['price'];
            $g->total = $g->quantity * $g->price;
            $g->order_comment = $good_q[$g->id]['comment'];
            $this->qty += $g->quantity;
            $this->total += $g->total;
            $return[] = $g;
        }

        return $return;
    }

    /**
     * Функция сохранения данных о товарах в заказе в БД
     * @param Cart $cart
     * @return array
     */
    public function save_goods(Cart $cart) 
    {
        $ins = DB::insert('z_order_good',array('order_id','good_id','price','quantity','comment','action_id'));
        $not_empty = FALSE;

        $goods = $cart->recount();
        $presents = $cart->check_actions($goods);
        
        foreach ($goods as $g) {
            $ins->values(array(
                'order_id' => $this->id,
                'good_id' => $g->id,
                'price' => $g->price,
                'quantity' => $g->quantity,
                'comment' => $cart->get_comment($g->id),
                'action_id' => 0
            ));
            $not_empty = TRUE;
        }

        if ( ! empty($presents)) {
            foreach($presents as $action_id => $present_id) { // проверяем выбранные призы или отказ от приза
                $action = $cart->actions[$action_id];
                $add = TRUE;
                if ($action->count_from) { // накопительная
                    if ( ! empty($cart->no_presents[$action_id]))  {
                        $add = FALSE; // отказ от приза!
                    } else {
                        $action->set_count_from(Model_User::current()->id, $this->id); // взяли приз, запоминаем номер с которого считать сумму
                    }
                }

                if ($add) {
                    $ins->values(array(
                        'order_id' => $this->id,
                        'good_id' => $present_id,
                        'price' => 0,
                        'quantity' => $action->pq,
                        'comment' => '',
                        'action_id' => $action->pk()
                    ));
                }
            }
        }

        if ( ! empty($cart->blago)) {
            $ins->values(array(
                'order_id' => $this->id,
                'good_id' => Cart::BLAG_ID,
                'price' => 1,
                'quantity' => $cart->blago,
                'comment' => '',
                'action_id' => 0
            ));
        }
        if ( ! empty($cart->coupon)) { // есть купон на скидку
            $coupon = new Model_Coupon($cart->coupon['id']);
            if ($coupon->loaded()) {
                $this->coupon_id = $coupon->id;
                $this->save();
                $coupon->used(); // используем купон
            }
        }
        if ($not_empty) DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();
    }

    /**
     * Меняет список товаров в заказе на новый, пришедший из 1с
     * @param $goods array(id => array('qty' => $qty, 'price' => price))
     */
    public function change_goods($goods)
    {
        $good_action = DB::select('good_id','action_id')
                ->from('z_order_good')
                ->where('order_id',  '=',  $this->id)
                ->where('action_id', '!=', 0)
                ->execute()->as_array('good_id','action_id');
        
        DB::delete('z_order_good')->where('order_id', '=', $this->id)->execute(); // удалим всё что было
        
        $ins = DB::insert('z_order_good', array('order_id', 'good_id', 'price', 'quantity', 'action_id'));
        
        foreach ($goods as $id => $g) {
            $ins->values(array(
                'order_id' => $this->id,
                'good_id' => $id,
                'price' => current($g),
                'quantity' => key($g),
                'action_id' => (empty($good_action[$id]) ? 0 : $good_action[$id])
            ));
        }

        $ins->execute();
        
    }

    /**
     * Массив типов доставки для проверок и скриптов
     */
    public static function delivery_types()
    {
        return array(self::SHIP_COURIER, self::SHIP_SERVICE, self::SHIP_SELF);
    }

    /**
     * Звонок перед доставкой
     * @static
     * @param null $call
     * @return array
     */
    public static function delivery_call($call = null)
    {
        $return = array(
            '0' => 'не нужно',
            '5' => 'за 5-10 минут',
            '20' => 'за 20-30 минут',
        );

        if ( ! is_null($call)) {
            return ! empty($return[$call]) ? $return[$call] : FALSE;
        }

        return $return;
    }

    /**
     * Расчёт суммарной стоимости заказа
     * @param bool $no_ship - без учёта доставки
     * @return mixed
     */
    public function get_total($no_ship = FALSE)
    {
        return $this->price + (TRUE === $no_ship ? 0 : $this->price_ship);
    }

    /**
     * Вычисление цены доставки по данным заказа
     * Цена зависит от зоны доставки, расстояния от Мкад, времени доставки, суммы заказа
     * @param \Model_Order_Data $od
     * @throws ErrorException
     * @return int|mixed
     */
    public function price_ship($od)
    {
        if (in_array($this->delivery_type, array(Model_Order::SHIP_SERVICE, Model_Order::SHIP_SELF))) return 0;

        $zone = new Model_Zone($od->ship_zone);
        if ( ! $zone->loaded()) throw new ErrorException('Zone not found '.$od->ship_zone);

        $time = new Model_Zone_Time($od->ship_time);
        if ( ! $time->loaded()) throw new ErrorException('Time not found '.$od->ship_time);

        if ($time->zone_id != $zone->id) {
            throw new ErrorException('Zone time '.$time->id.' not allowed for zone '.$zone->id);
        }


        $return = $time->get_price($this->price);
        if ($od->ship_zone == Model_Zone::ZAMKAD) $return += $od->mkad * self::PRICE_KM;
        if ($od->ship_date == '2014-12-31') $return += 500; // + 500 рублей 31 декабря

        return $return;
    }

    /**
     * Получение от edost расчёта доставки
     * @param $to_city
     * @return array
     */
    public static function edost($to_city)
    {
        $cart = Cart::instance();
        if ( 0 == $cart->get_qty() ) return array('qty_company' => 0, 'stat' => 0); // нет корзины
        if ( ! Model_User::logged()) return array('qty_company' => 0, 'stat' => -1); // нет пользователя

        $weight = 0;
        foreach($cart->recount() as $g) {
            $weight += (floatval($g->prop->weight) OR .5) * $g->quantity;
        }

        // запрашиваем с кешированием
        $cache_key = md5($cart->get_total().'|'.$to_city.'|'.$weight);
        $cached = Cache::instance()->get($cache_key);
        if ( ! $cached) {
            $edost = Request::factory(self::EDOST_URL)
                ->method('POST')
                ->post(array(
                'to_city' => $to_city,
                'id' => self::EDOST_ID,
                'p' => self::EDOST_PSWD,
                'weight' => $weight,
                'strah' => $cart->get_total(),
            ));

            $xml = $edost->execute();

            Cache::instance()->set($cache_key, serialize($xml));
        } else {
            $xml = unserialize($cached);
        }


/*
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rsp><stat>1</stat><tarif><id>2</id><price>261</price><day></day><strah>0</strah><company>Почта России</company><name>наземная посылка</name></tarif><tarif><id>2</id><price>496</price><day></day><strah>1</strah><company>Почта России</company><name>наземная посылка со страховкой</name></tarif><tarif><id>3</id><price>1030</price><day>3-4 дня</day><strah>0</strah><company>EMS Почта России</company><name></name></tarif><tarif><id>3</id><price>1089</price><day>3-4 дня</day><strah>1</strah><company>EMS Почта России</company><name> со страховкой</name></tarif></rsp>';
*/
        Log::instance()->add(Log::INFO, $xml);

        $x = simplexml_load_string($xml);

        if ( ! empty($x->stat[0]) && $x->stat[0] == 1) {
            $k = 1;
            $store = array();
            foreach($x->tarif as $t) {
                $item = array();
                foreach($t as $name => $val) {
                    $item[$name] = (string)$val;
                }
                $store[$k++] = $item;
            }
            Session::instance()->set('edost', $store)->write(); // запомним в сессии тарифы
            return View::factory('smarty:user/order/edost', array('opts' => $x->tarif))->render();

        } elseif (isset($x->stat[0])) {

            $return = Model_Order::edost_status($x->stat[0]);

        } elseif ($xml == 'Err14') {

            $return = Model_Order::edost_status(14);

        } else {

            $return = Model_Order::edost_status(8);
        }

        return sprintf('<p>%s</p>', $return);
    }

    /**
     * проверка выбора магазина для самовывоза
     * @param $shop_id
     * @return bool|array|string
     */
    public static function is_shop($shop_id = NULL)
    {
        static $shops = array(
            36878 => 'М.О. г.&nbsp;Мытищи, ул.&nbsp;Шараповская, д.1 корп.2; Тел.: <nobr>+7 (910) 019-79-58</nobr>',
            // 36428 => 'М.О. г.&nbsp;Королев ул.&nbsp;Декабристов, д.20; Тел.: <nobr>+7 (495) 519-53-24</nobr>',
            36955 => 'М.О. г.&nbsp;Юбилейный, ул.&nbsp;Лесная, д.14; Тел.: <nobr>+7 (495) 515-93-80</nobr>'
        );

        if ( ! is_null($shop_id)) {
            return ! empty($shops[$shop_id]) ? $shops[$shop_id] : false;
        }
        return  $shops;
    }


    /**
     * Возвращает расшифровку кода статуса edost
     * @static
     * @param $status
     * @return string
     */
    public static function edost_status($status)
    {
        switch($status) {
            // коды ошибок из главного запроса на сервер edost
            case -1:	return "Операция разрешена только авторизованным пользователям"; break;
            case 0:		return "Пустая корзина"; break;
            case 1:		return "Расчёт произведён успешно"; break;
            case 2:		return "Доступ к расчету заблокирован"; break;
            case 3:		return "Не верные данные магазина (пароль или идентификатор)"; break;
            case 4:		return "Не верные входные параметры"; break;
            case 5:		return "Не верный город или страна"; break;
            case 6:		return "Внутренняя ошибка сервера расчетов"; break;
            case 7:		return "Не заданы компании доставки в настройках магазина"; break;
            case 8:		return "Сервер расчета не отвечает"; break;
            case 9:		return "Превышен лимит расчетов за день"; break;
            case 11:	return "Не указан вес"; break;
            case 12:	return "Не заданы данные магазина (пароль или идентификатор)"; break;
            case 14:	return "Настройки сервера не позволяют отправить запрос на расчет"; break;
            // коды ошибок из класса edost_class
            case 10:	return "Не верный формат XML"; break;
            default:	return "В данный город автоматический расчет доставки не осуществляется";
        }
    }

    /**
     * Сохранение статуса заказа
     * @param null|\Validation $validation
     * @return ORM
     */
    public function save(Validation $validation = NULL)
    {
        if ($this->changed('status')) {
            $this->status_time = date("Y-m-d H:i:s");
        }
        if ($this->changed()) {
            $this->changed = date("Y-m-d H:i:s");
        }
        if (empty($this->vitrina)) {
            $this->vitrina = Kohana::$server_name;
        }
		
		if( $this->status == 'X' ){
			
			if( ! empty( $this->coupon_id ) ){

				$this->coupon->unused();
				$this->coupon_id = 0;
				
				$this->save();
			}
		}
		
        return parent::save($validation);
    }

}
