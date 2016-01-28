<?php
class Model_Order extends ORM {

    const TYPE_ONECLICK = 1; // тип заказа = заказ в один клик

    const PAY_DEFAULT = 1; // оплата наличными при получении
    const PAY_CARD = 8; // оплата картой
    const PAY_BANK = 5; // оплата банковским переводом

    const SHIP_COURIER = 2; // доставка курьером
    const SHIP_SERVICE = 3; // доставка транспортной компанией (через dpd)
    const SHIP_SELF = 4; // самовывоз - (сейчас не используется)
    const SHIP_OZON = 5;
    const SHIP_UNKNOWN = 9;

    const SERVICE_OZON = 'ozon';
    const SERVICE_DPD = 'dpd';
    const SERVICE_YA = 'ya';

    const SHIP_DOOR = 0; // доставка до двери
    const SHIP_TERMINAL = 1; // доставка до терминала

    const EDOST_ID = 1499;
    const EDOST_PSWD = 'OMY9Fmx6y9gqrX66tSSVWVMq3cPR0fCy';
    const EDOST_URL = 'http://www.edost.ru/edost_calc_kln.php';

    const PRICE_KM = 20;

    protected $_table_name = 'z_order';

    protected $_table_columns = [
        'id' => '', 'type' => '', 'user_id' => '', 'user_status' => '', 'sent' => '', 'created' => '', 'changed' => '', 'description' => '',
        'manager' => '', 'price' => '',	'discount' => '', 'price_ship' => '', 'status' => '', 'status_time' => '',
        'pay_type' => '', 'payed' => 0, 'pay8'=> '', 'pay1' => '', 'delivery_type' => '', 'vitrina' => '', 'coupon_id' => '', 'can_pay' => '',
        'in1c' => 0, 'call_card' => 0,
        'check' => '', 'check_time' => '',
    ];

    protected $_belongs_to = [
        'user' => ['model' => 'user', 'foreign_key' => 'user_id'],
        'coupon' => ['model' => 'coupon', 'foreign_key' => 'coupon_id'],
    ];

    protected $_has_many = [
        'goods' => [
            'model'         => 'good',
            'through'       => 'z_order_good',
            'foreign_key'   => 'order_id',
            'far_key'       => 'good_id',
        ],
        'payments' => [
            'model' => 'payment',
            'foreign_key' => 'order_id',
        ]
    ];

    protected $_has_one = [
        'data' => [
            'model' => 'order_data',
            'foreign_key' => 'id',
        ],
        'logistic' => [
            'model' => 'order_logistic',
            'foreign_key' => 'order_id',
        ],
    ];

    public $qty = 0;
    public $total = 0;

    public function rules()
    {
        return [
            'user_id' => [
                ['not_empty'],
            ],
            'price' => [
                ['not_empty'],
            ],
        ];
    }

    public function get_link($html = true)
    {
        $href = sprintf('/account/order/%d', $this->id);
        return $html ? HTML::anchor($href, $this->id) : $href;
    }

    public static function get_status_list()
    {
        static $stats = [
            'N' => 'Принят',
            'C' => 'Ожидает оплаты картой',
            'S' => 'В обработке',
            'D' => 'Сформирован',
            'T' => 'Заказ передан в службу доставки',
            'R' => 'Заказ на маршруте',
            'F' => 'Выполнен',
            'X' => 'Отменён',
        ];
         
        return $stats;
    }

    public static function pay_types()
    {
        static $types = [
            self::PAY_CARD => 'Карта',
            self::PAY_DEFAULT => 'Наличные',
        ];

        return $types;
    }

    /**
     * Получить крайний сеанс оплаты для заказа
     * Если заказ ещё не оплачивали будет пустой объект
     * @return Model_Payment
     */
    public function payment()
    {
        $payment = $this->payments->order_by('id', 'DESC')->find();
        return $payment;
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

    /**
     * Изменилось состояние оплаты заказа
     * @throws Kohana_Exception
     */
    public function on_payment_change()
    {
        if ($this->pay_type == self::PAY_CARD && $this->can_pay == 1) { // оплата картой разрешена
            if ($this->data->mobile_phone) {
                Model_Sms::to_queue($this->data->mobile_phone, 'Заказ ' . $this->id . ' готов к оплате: http://mladenec.ru/' . $this->id);
            }
            Mail::htmlsend('order', ['o' => $this, 'od' => $this->data, 'canPay' => TRUE] // что передадим в почту
            , $this->data->email, 'Заказ '.$this->id.' готов к оплате');
        }
    }

    /**
     * Если заказ сменил статус
     * @throws Kohana_Exception
     */
    public function on_status_change()
    {
        $got_status = FALSE;

        if (in_array($this->status, ['T', 'X', 'F'])) {

            if ('F' == $this->status) { // заказ доставлен 

                // пересчитаем сумму для юзера
                $this->user->sum += $this->get_total();
                $this->user->qty ++;

                if (($this->user->status_id == 0) AND ($this->user->sum >= Model_User::STATUS_CHANGE)) { // меняем статус

                    $this->user->status_id = 1;
                    $this->user->save();

                    $got_status = TRUE;
                }
                $this->user->save();

                
            }
            
            $this->on_status_change_email($got_status);
        }

        /* обработка заказа озона TODO: переписать
        //размещение заказа в озон
        if ($status_changed &&
            $order->delivery_type == Model_Order::SHIP_OZON)
        {
            // при смене статуса на "на палете" загружаем заказ
            if( $order->status == 'T' ) {
                $ozon = new OzonDelivery();
                $terminal_id = Model_Ozon_Terminal::get_id_by_address($addr);
                if($terminal_id) {
                    if(!$ozon->upload_order($order, $order_data, $terminal_id)) {
                        //ставим статус - не получилось разместить заказ в озон
                        $order_data->ship_status = -1;
                        $order_data->save();
                        $order->in1c = 0;
                        $order->save();

                        mail('v.vinnikov@toogarin.ru', 'Не размещен заказ в озон '.$order->id, "Заказ не был загружен - подробности в логах");
                    } elseif(!$order_data->ship_barcode) {
                        //если заказ только размещен - получаем штрихкод и сохраняем в базу
                        $order_data->ship_barcode = $ozon->get_barcode($order->id);
                        $order_data->save();
                        //сообщаем лайт-протоколу чтоб отдали штрихкод и статус размещено
                        $order->in1c = 0;
                        $order->save();
                    }
                } else  {
                    //ставим статус - не найдет терминал озон
                    $order_data->ship_status = -3;
                    $order_data->save();
                    $order->in1c = 0;
                    $order->save();

                    mail('v.vinnikov@toogarin.ru', 'Не найден терминал озон '.$order->id, "По адресу $addr не найден delivery id");
                }
            } elseif($order->status == 'X' && $order_data->ship_barcode) {
                //при отмене заказа пробуем отменить заказ
                $ozon = new OzonDelivery();
                if(!$ozon->cancel_order($order->id)) {
                    //ставим статус - не получилось отменить заказ в озон
                    $order_data->ship_status = -2;
                    $order_data->save();
                    $order->in1c = 0;
                    $order->save();

                    mail('v.vinnikov@toogarin.ru', 'Не удалось отменить заказ в озон '.$order->id, "Смотрите логи");
                } else {
                    $order_data->ship_barcode = '';
                    $order_data->ship_status = 0;
                    $order_data->save();
                }
            }
        } elseif($order_data->ship_barcode && $order->delivery_type == Model_Order::SHIP_OZON) {
            //если статус не менялся, но состав заказа, способ оплаты или терминал поменялись
            $ozon = new OzonDelivery();
            $terminal_id = Model_Ozon_Terminal::get_id_by_address($addr);
            if ($terminal_id) {
                if (!$ozon->upload_order($order, $order_data, $terminal_id)) {
                    //ставим статус - не получилось разместить заказ в озон
                    $order_data->ship_status = -1;
                    $order_data->save();
                    $order->in1c = 0;
                    $order->save();

                    mail('v.vinnikov@toogarin.ru', 'Не обновлен заказ в озон ' . $order->id, "Заказ не был обновлен - подробности в логах");
                }
            } else {
                //ставим статус - не найдет терминал озон
                $order_data->ship_status = -3;
                $order_data->save();
                $order->in1c = 0;
                $order->save();

                mail('v.vinnikov@toogarin.ru', 'Не найден терминал озон '.$order->id, "По адресу $addr не найден delivery id");
            }
        } elseif($order_data->ship_barcode) {
           //сменился способ доставки, отменяем заказ озона
            $ozon = new OzonDelivery();
            if(!$ozon->cancel_order($order->id)) {
                //ставим статус - не получилось отменить заказ в озон
                $order_data->ship_status = -2;
                $order_data->save();
                $order->in1c = 0;
                $order->save();

                mail('v.vinnikov@toogarin.ru', 'Не удалось отменить заказ в озон '.$order->id, "Смотрите логи");
            } else {
                $order_data->ship_barcode = '';
                $order_data->ship_status = 0;
                $order_data->save();
            }
        }
        */

        // Пересчитаем накопления по активным накопительным акцииям
        // Накопления пересчитываются при всех сменах статусов, чтобы корректно отображались накопленные баллы
        $actions = Model_Action::get_active(TRUE);
        $user_action_credits = [];
        foreach($actions as $id => $a) {
            $user_action_credits[$id] = $this->user->get_funded($a);
        }

        foreach ($user_action_credits as $action_id => $credits) { // апдейтим записи в таблице с накоплениями

            Database::instance()->begin();

            DB::delete('z_action_user') // вместо update у нас  delete - insert - чтобы обрабатывать новые записи
                ->where('user_id', '=', $this->user->pk())
                ->where('action_id', '=', $action_id)
                ->execute();

            DB::insert('z_action_user')
                ->columns(['action_id','user_id','from_order','sum','qty'])
                ->values([
                    'action_id'  => $action_id,
                    'user_id'    => $this->user->pk(),
                    'from_order' => intval($credits['from_order']),
                    'sum'        => floatval($credits['sum']),
                    'qty'        => intval($credits['qty'])
                ])->execute();

            Database::instance()->commit();
        }
    }
    
    private function on_status_change_email($got_status)
    {
        
        $mail_values = ['o' => $this, 'od' => $this->data]; // что передадим в почту
		$mail_subj = 'Ваш заказ '.$this->id.' '.$this->status();

        if ($got_status) $mail_values['got_status'] = TRUE;
        
        if ('F' == $this->status) { // заказ доставлен 

            // для этого статуса заголовок зависит от типа доставки
            if (self::SHIP_SERVICE == $this->delivery_type) {
                $mail_subj = 'Ваш заказ '.$this->id.' передан в транспортную компанию';
            }
        }
        
        Mail::htmlsend('order', $mail_values, $this->data->email, $mail_subj);
    }

    /**
     * Пытается отправить СМС о том что заказ принят
     */
    public function send_sms_accepted()
    {
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
                $sms_text = "Мы приняли Ваш заказ ".$this->id.", сумма ".($this->price + $this->price_ship)."р.Спасибо!Ваш mladenec.ru";
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
        $good_q = DB::select('good_id', 'price', 'quantity', 'comment')
            ->from('z_order_good')
            ->where('order_id', '=', $this->id)
            ->execute()
            ->as_array('good_id');

        if (empty($good_q)) return [];

        $goods = ORM::factory('good')->where('id', 'IN', array_keys($good_q))->find_all();
        $return = [];
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
            $ins->values([
                'order_id' => $this->id,
                'good_id' => $g->id,
                'price' => $g->price,
                'quantity' => $cart->goods[$g->id],
                'comment' => $cart->get_comment($g->id),
                'action_id' => 0
            ]);
            $not_empty = TRUE;
        }

        if ( ! empty($presents)) {
            foreach($presents as $action_id => $present_id) { // проверяем выбранные призы или отказ от приза
                $action = $cart->actions[$action_id];
                $add = TRUE;
                if ($action->is_funded()) { // накопительная
                    if ( ! empty($cart->no_presents[$action_id]))  {
                        $add = FALSE; // отказ от приза!
                    } else {
                        $action->set_count_from(Model_User::current()->id, $this->id); // взяли приз, запоминаем номер с которого считать сумму
                    }
                }
        
                if ($add) {
                    $ins->values([
                        'order_id'  => $this->id,
                        'good_id'   => $present_id,
                        'price'     => 0,
                        'quantity'  => $action->pq,
                        'comment'   => '',
                        'action_id' => $action->pk()
                    ]);
                }
            }
        }

        // крепим призы по купону
        if ( ! empty($cart->coupon['id']) && $cart->coupon['type'] == Model_Coupon::TYPE_PRESENT && ! empty($cart->coupon_presents)) {

            foreach ($cart->coupon_presents as $present_id) {
                $ins->values([
                    'order_id' => $this->id,
                    'good_id' => $present_id,
                    'price' => 0,
                    'quantity' => 1,
                    'comment' => 'Приз по купону',
                    'action_id' => $cart->coupon['id'],
                ]);
            }
        }
        
        if ( ! empty($cart->blago)) {
            $ins->values([
                'order_id' => $this->id,
                'good_id' => Cart::BLAG_ID,
                'price' => 1,
                'quantity' => $cart->blago,
                'comment' => '',
                'action_id' => 0
            ]);
        }
        if (isset($cart->coupon['type']) && ($cart->coupon['type'] != Model_Coupon::TYPE_LK)) { // есть купон на скидку - тут надо засчитать использование

            $coupon = new Model_Coupon($cart->coupon['id']);
            if ($coupon->loaded()) {
                $this->coupon_id = $cart->coupon['id'];
                $this->save();
                $coupon->used(); // используем купон
            }
        }
        if ($not_empty) DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();
    }

    /**
     * Меняет список товаров в заказе на новый, пришедший из 1с
     * @param $goods [id => ['qty' => $qty, 'price' => price]]
     */
    public function change_goods($goods)
    {
        $good_action = DB::select('good_id','action_id')
                ->from('z_order_good')
                ->where('order_id',  '=',  $this->id)
                ->where('action_id', '!=', 0)
                ->execute()->as_array('good_id','action_id');
        
        DB::delete('z_order_good')->where('order_id', '=', $this->id)->execute(); // удалим всё что было
        
        $ins = DB::insert('z_order_good', ['order_id', 'good_id', 'price', 'quantity', 'action_id']);
        
        foreach ($goods as $id => $g) {
            $ins->values([
                'order_id' => $this->id,
                'good_id' => $id,
                'price' => current($g),
                'quantity' => key($g),
                'action_id' => (empty($good_action[$id]) ? 0 : $good_action[$id])
            ]);
        }

        $ins->execute();
        
    }

    /**
     * Массив типов доставки для проверок и скриптов
     */
    public static function delivery_types()
    {
        return [self::SHIP_COURIER, self::SHIP_SERVICE];
    }

    /**
     * Звонок перед доставкой
     * @static
     * @param null $call
     * @return array
     */
    public static function delivery_call($call = null)
    {
        $return = [
            '20' => 'за 20-30 минут',
            '5' => 'за 5-10 минут',
            '0' => 'не нужно',
        ];

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
        if (empty($od->ship_zone)) { // регион

            $ship_data = explode(':', $od->comment); // тут ожидаем данные
            $return = floatval(array_pop($ship_data));

        } else {

            $zone = new Model_Zone($od->ship_zone);
            if ( ! $zone->loaded()) throw new ErrorException('Zone not found ' . $od->ship_zone);

            $time = new Model_Zone_Time($od->ship_time);
            if ( ! $time->loaded()) { // нет времени доставки - ставим первое из выбранной зоны
                Log::instance()->add(Log::WARNING, 'Time not found '.$od->ship_time.' for zone '.$od->ship_zone);

                $time = ORM::factory('zone_time')
                    ->where('zone_id', '=', $zone->id)
                    ->where('active', '=', 1)
                    ->order_by('sort')
                    ->limit(1)
                    ->find();

                if ( ! $time->loaded()) throw new ErrorException('No time for zone '.$od->ship_zone);
            }
            if ($time->zone_id != $zone->id) {
                Log::instance()->add(Log::WARNING, 'Zone time '.$time->id.' not allowed for zone '.$zone->id);
            }

            $return = $time->get_price($this->price);
            if ($od->ship_zone == Model_Zone::ZAMKAD) $return += $od->mkad * self::PRICE_KM;
        }

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
        if ( 0 == $cart->get_qty() ) return ['qty_company' => 0, 'stat' => 0]; // нет корзины
        if ( ! Model_User::logged()) return ['qty_company' => 0, 'stat' => -1]; // нет пользователя

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
            $store = [];
            foreach($x->tarif as $t) {
                $item = [];
                foreach($t as $name => $val) {
                    $item[$name] = (string)$val;
                }
                $store[$k++] = $item;
            }
            Session::instance()->set('edost', $store)->write(); // запомним в сессии тарифы
            return View::factory('smarty:user/order/edost', ['opts' => $x->tarif])->render();

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
        static $shops = [
            36878 => 'М.О. г.&nbsp;Мытищи, ул.&nbsp;Шараповская, д.1 корп.2; Тел.: <nobr>+7 (910) 019-79-58</nobr>',
            // 36428 => 'М.О. г.&nbsp;Королев ул.&nbsp;Декабристов, д.20; Тел.: <nobr>+7 (495) 519-53-24</nobr>',
            36955 => 'М.О. г.&nbsp;Юбилейный, ул.&nbsp;Лесная, д.14; Тел.: <nobr>+7 (495) 515-93-80</nobr>'
        ];

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
		
		if ($this->status == 'X') {
			
			if ( ! empty( $this->coupon_id)) {

				$this->coupon->unused();
				$this->coupon_id = 0;
				
				$this->save();
			}
		}
		
        return parent::save($validation);
    }

    static function terminal($city, $region)
    {
       // Model_Terminal::
    }

    /**
     * Вспомогательные функции для генерации чека
     * @param $price
     * @return string
     */
    private function __check_price_format($price)
    {
        return number_format($price, 2, ',', '');
    }
    private function __check_nds_format($price)
    {
        return number_format($price, 2, ',', '\'');
    }
    private function __check_add_good(PHPExcel_Worksheet &$sheet, $g, $n)
    {
        $n2 = $n + 2;
        $sheet->insertNewRowBefore($n, 3); // Insert 3 new rows

        $sheet->setCellValue('A' . $n, $g->group_name . ' ' . $g->name);
        $sheet->duplicateStyle($sheet->getStyle('A9'), 'A'.$n);
        $sheet->mergeCells('A' . $n.':F'.( $n + 1 ));

        $sheet->setCellValue('A' . $n2, $g->quantity . ' * ' . self::__check_price_format($g->price));
        $sheet->duplicateStyle($sheet->getStyle('A11'), 'A'.$n2);
        //$sheet->mergeCells('A' . $n.':F'.( $n + 1 ));

        $sheet->setCellValue('F' . $n2, ' = '. self::__check_price_format($g->total));
        $sheet->duplicateStyle($sheet->getStyle('F11'), 'F'.$n2);
    }

    /**
     * Генерация/получение чека
     */
    function get_check($generate = FALSE)
    {
        if ($this->status != 'F') return FALSE;
        if (empty($this->check) || empty($this->check_time)) return FALSE;

        $subdir = preg_replace('~(\d)~isu', '$1/', substr($this->id, 0, 4));
        $dir = APPPATH.'../www/upload/'.$subdir;
        if ( ! file_exists($dir)) {
            mkdir($dir, 0777, TRUE);
        }
        $fname = $dir.$this->id.".xlsx";

        if (empty($generate)) {

            if (file_exists($fname)) {
                return '/'.Upload::$default_directory.'/'.$subdir.basename($fname);
            } else {
                return FALSE;
            }
        }

        include(APPPATH.'classes/PHPExcel.php');
        $excel = PHPExcel_IOFactory::load(APPPATH.'config/check.xlsx');
        $sheet = $excel->getActiveSheet();
        $sheet->setCellValue('A7', strftime('%d.%m.%Y %H:%M', strtotime($this->check_time)));
        $sheet->setCellValue('F8', '№ '.$this->check);

        $goods = $this->get_goods();

        $n = 9;
        $nds10 = $nds18 = 0;

        foreach($goods as $g) {

            if ($n == 9) { // первая строка - меняем на нашу цену и название
                $sheet->setCellValue('A' . $n, $g->group_name . ' ' . $g->name);
                $sheet->setCellValue('A' . ($n + 2), $g->quantity . ' * ' . self::__check_price_format($g->price));
                $sheet->setCellValue('F' . ($n + 2), ' = '. self::__check_price_format($g->total));

            } else { // следующие - копируем первое и стили

                self::__check_add_good($sheet, $g, $n);
            }
            if ($g->nds == 10) {
                $nds10 += $g->total * $g->nds / 100;
            }
            if ($g->nds == 18) {
                $nds18 += $g->total * $g->nds / 100;
            }
            $n += 3;
        }

        if ($this->price_ship > 0) { // доставка - последним товаром

            $g = new Model_Good();
            $g->name = 'Доставка';
            $g->quantity = 1;
            $g->price = $g->total = $this->price_ship;

            self::__check_add_good($sheet, $g, $n);

            $n += 3;
        }

        $total = self::__check_price_format($this->get_total());
        $sheet->setCellValue('D' . ($n + 1), ' = '. $total); // ИТОГО
        $sheet->setCellValue('A' . ($n + 2), $this->pay_type == Model_Order::PAY_CARD ? 'Безналичные' : 'Наличные'); // текст про НДС

        $nds = "Общая сумма ".self::__check_nds_format($total)." руб. включая НДС ".self::__check_nds_format($nds10 + $nds18)." руб.\n"
            ."из них оплачено наличными ".self::__check_nds_format($this->pay_type == Model_Order::PAY_CARD ? $this->pay1 : $total)." руб.\n"
            ."18% НДС - ".self::__check_nds_format($nds18)." руб.\n"
            ."10% НДС - ".self::__check_nds_format($nds10)." руб";

        $sheet->setCellValue('A' . ($n + 3), $nds); // текст про НДС
        $sheet->setCellValue('B' . ($n + 5), 'ЕКЛЗ с фп 7024127040'); // счетчик фп?
        $sheet->setCellValue('B' . ($n + 6), '00037122 #'.$this->id);

        $io = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

        $io->save($fname);
        return $fname;
    }
}
