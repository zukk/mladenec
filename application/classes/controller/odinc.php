<?php

class Controller_Odinc extends Controller {

    protected $errors = array();
    protected $view = FALSE;
    protected $body = '';
    protected $action = '';
    
    protected $start_microtime = 0;

    protected $log_dir_suffix = '';
    
    private $good_qty_changed = array(); // [id=>[new_qty=>x,old_qty=>y]]
    
    private function timer()
    {
        $current_time = microtime(TRUE);
        
        if ( 0 == $this->start_microtime) $this->start_microtime = $current_time;
        
        return $current_time - $this->start_microtime;
    }
    
    private function good_qty_changed($good_id, $new_qty, $old_qty)
    {
        $this->good_qty_changed[$good_id] = array('new_qty' => $new_qty, 'old_qty' => $old_qty);
        $return = FALSE;
        if ($old_qty == 0 && $new_qty != 0) {
            Model_History::log('good', $good_id, 'appear'); // товара стало > 0 или -1
            $return = TRUE;
        }
        if ($old_qty != 0 && $new_qty == 0) {
            Model_History::log('good', $good_id, 'disappear'); // товара стало 0
            $return = TRUE;
        }
        return $return;
    }

    /**
     * Парсит строку в массив
     * 
     * @param string $delimiter
     * @param string $string
     * @param int    $valid_count
     * @param array  $required_fields - начиная с 0!!!
     * @return array
     */
    protected function parse($delimiter, $string, $valid_count = null, $required_fields = array())
    {
        try
        {
            $array = Txt::parse_explode($delimiter, $string, $valid_count, $required_fields);
        } 
        catch (Txt_Exception $ex)
        {
            $this->error($ex->getMessage());
            $array = NULL;
        }
        
        return $array;
    }

    /**
     * получить вьюшку для текущего протокола
     * @throws HTTP_Exception_404
     * @return View
     */
    protected function get_view()
    {
        try {
            $return = View::factory('smarty:odinc/'.$this->request->action());
        } catch(Kohana_View_Exception $e) {
            throw new HTTP_Exception_404;
        }
        return $return;
    }

    /**
     * Дела перед обработкой
     * @throws HTTP_Exception_404
     */
    public function before()
    {

        $this->action = $this->request->query('action');
        
        Log::instance()->add(Log::INFO, $this->request->action() . ' started' . ($this->action ? ', action '.$this->action : '').', timer: ' . $this->timer());
        
        $this->view = $this->get_view();

        ini_set('mbstring.substitute_character', 32); // substitute invalid characters to space

        if ( 'utf8' == $this->request->query('encoding')) {
            $this->body = $this->request->body();
        } else {
            $this->body = trim(mb_convert_encoding($this->request->body(), 'utf8', 'cp1251'));
        }

        if ( ! empty($this->body)) {
            $now = new DateTime();
            $log_dir = APPPATH.'logs/' . date_format($now, 'Y/m/d') . $this->log_dir_suffix;
            if ( ! file_exists($log_dir)) mkdir($log_dir, 0777, TRUE);

            $file_prefix = $log_dir.'/'.date_format($now, 'H_i_s_').$this->request->action().($this->action ? '_'.$this->action : '');
            $file_name = $file_prefix;
            $i = 0;
            while(file_exists($file_name)) {
                $file_name = $file_prefix.(++$i);
            }
            file_put_contents($file_name, $this->body);
        }
        
        Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', logs saved, timer: ' . $this->timer());
    }

    /**
     * Дела после обработки
     */
    public function after()
    {
        Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', processing completed, timer: ' . $this->timer());
        
        $body = $this->view->render();
        if ( ! empty($this->errors)) {
            foreach ($this->errors as $key => $errors) {
                if ($key > 0) {
                    if ( ! empty($this->view->saved[$key])) { // ид заказа есть в сохраненных - уберем
                        unset($this->view->saved[$key]);
                    }
                    $errors[] = 'SAVED';

                    $body = $key.':ERRORS:'.implode('|', $errors)."\n".$body;
                } else {
                    $body = implode("\n", $errors)."\n".$body;
                }
            }
        }

        if ('utf8' == $this->request->query('encoding')) {
            header('Content-Type: text/plain; charset=utf-8');
        } else {
            $body = mb_convert_encoding($body, 'cp1251', 'utf8');
            header('Content-Type: text/plain; charset=windows-1251');
        }

        Log::instance()->add(Log::INFO,
            $this->request->action().' complete'.($this->action ? ', action '.$this->action : '').', '.
            $this->request->content_length().' bytes, '.(substr_count($this->body, "\n") + 1).' strings'
        );
        header('Content-Length:'.strlen($body)); // bug gone ?  /*if (Kohana::$environment !== Kohana::DEVELOPMENT) */ do not send on test server due to bug!
        echo $body;
        
        Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', finished, timer: ' . $this->timer());
        
        exit();
    }

    /**
     * Функция генерации ошибки
     */
    protected function error($string, $key = 0)
    {
        $this->errors[$key][] = $string;
        Log::instance()->add(Log::ERROR, $string);
    }

    public function action_testreply()
    {
        $strings = explode("\n", $this->body);
        
        $this->view->headers = Request::current()->headers();
       
        $this->view->strings = $strings;
    }
    
    /**
     * протокол передачи купонов в 1с
     */
    public function action_coupons()
    {
        $this->view->coupon = ORM::factory('coupon')
            ->where('in1c', '=', '0')
            ->find_all();
    }

    /**
     * Проставляем статус купона - отдали в 1с
     */
    public function action_coupons_valid()
    {
        if ($id = $this->body) {
            $c = new Model_Coupon(array('name' => $id));
            $this->view->ok = $c->loaded() && $c->in1c();
        }
    }

	public function action_price()
    {
        $strings = explode("\n", $this->body);
        
        $doc_ids = array();
        
        $this->view->errors = array();
        
        $this->view->aq = array();
        
        foreach ($strings as $s)
        {
            if (FALSE !== strpos($s,'ЦЕНЫ:'))
            {
                $doc_ids[] = $doc_id = trim(mb_substr($s, 5));
                
                $this->view->errors[] = 'Документ: ' . $doc_id;
                
                $prices = array(); // [code => ['price'=>$price,'lk'=>$lk]]
                
                if ( ! (intval($doc_id) > 0)) 
                {
                    $errors[] = 'Неверный номер документа.';
                    break;
                }
            }
            elseif(FALSE !== strpos($s,'КОНЕЦЦЕН'))
            {
                $this->view->errors[] = 'Принято: ' . count($prices);
                
                $up_counter = 0;
                
                $one_s_ids = DB::select('id1c','id')
                        ->from('z_good')
                        ->where('id1c','IN',array_keys($prices))
                        ->where('price_doc_id','<>', $doc_id)
                        ->execute()->as_array('id1c','id');
                
                foreach($one_s_ids as $id1c => $id)
                {
                    if ( ! empty($prices[$id1c]))
                    {
                        $updated = DB::update('z_good')->set(array(
                                'price'         => $prices[$id1c]['price'],
                                'price_lk'      => $prices[$id1c]['lk'],
                                'price_ts'      => time(),
                                'price_doc_id'  => $doc_id
                                ))
                                ->where('id1c', '=', $id1c)
                                ->execute();

                        $up_counter += $updated;
                    }
                }
                $this->view->errors[] = 'Обновлено: ' . $up_counter;
            }
            else
            {
                try
                {
                    list($id1c,$price,$lk) = Txt::parse_explode('©', $s, 3);
                    
                    $prices[$id1c] = array('price'=>$price,'lk'=>$lk);
                }
                catch (Txt_Exception $ex)
                {
                    $this->error($ex->getMessage());
                    $this->view->errors[] = $ex->getMessage();
                }
               
            }
        }
        
        $this->view->answer = array();
        
        foreach($doc_ids as $di)
        {
            $this->view->answer[$di] = DB::select('id1c', 'price_ts')
                    ->from('z_good')
                    ->where('price_doc_id','IN',$doc_ids)
                    ->execute()->as_array('id1c','price_ts');
        }
    }

    /**
     * Отправка смс по запросу от 1с
     */
    public function action_sms()
    {
        $strings = explode("\n", $this->body);
        $saved = array();
        foreach($strings as $s)
        {
            $s = trim($s);
            if (empty($s)) continue;
            
            try
            {
                list($phone, $text) = Txt::parse_explode('|', $s, 2);
                Model_Sms::to_queue($phone, $text);
                $saved[] = $phone;
            }
            catch (Txt_Exception $ex)
            {
                $this->error($ex->getMessage());
            }
        }
        $this->view->sms = $saved;
    }


    /**
     *  Экспорт заказов для 1с
     */
    public function action_orders_export()
    {
        $this->view->orders = ORM::factory('order')
            ->where('status', '=', 'N')
            ->order_by('id', 'ASC')
            ->find_all();
    }

    /**
     * Выкладка заказов в 1с - короткий протокол, отказы и оплаты
     */
    public function action_orders_light()
    {
        $this->view->orders = ORM::factory('order')
            ->with('card')
            ->with('data')
            ->where('in1c', '=', 0)
            ->order_by('id', 'ASC')
            ->find_all();
    }

    /**
     * Подтверждение лайта со стороны 1с
     */
    public function action_orders_light_ok()
    {
        $strings = explode("\n", $this->body);
        $saved = array();
        foreach($strings as $s) {
            $s = trim($s);
            if (empty($s)) continue;

            $order = new Model_Order($s);
            if ( ! $order->loaded()) {
                $saved[$s] = 'NOT_FOUND';
            } else {
                $order->in1c = 1;
                $order->save();
                $saved[$s] = 'OK';
            }
        }
        $this->view->saved = $saved;
    }

    /**
     * Список новых пользователей для 1с
     */
    public function action_users()
    {
        $this->view->users = ORM::factory('user')->where('in1c', '=', 0)->find_all();
    }

    /**
     * Валидация заказа - проставляем статус - в обработке
     */
    public function action_orders_valid()
    {
        $strings = explode("\n", $this->body);
        $saved = array();
        foreach($strings as $s) {
            $s = trim($s);
            if (empty($s)) continue;

            $order = new Model_Order($s);
            if ( ! $order->loaded()) {
                $saved[$s] = 'NOT_FOUND';
            } else {
                $order->status = 'S';
                $order->save();
                $saved[$s] = 'OK';
            }
        }
        $this->view->saved = $saved;
    }

    /**
     * Проставляем статус юзера - отдали в 1с
     * @throws HTTP_Exception_404
     */
    public function action_users_valid()
    {
        if ($id = $this->body) {
            $user = new Model_User($id);
            $this->view->ok = $user->loaded() && $user->in1c();
        }
    }

    /**
     * Приём заказов от 1с
     */
    public function action_orders_import()
    {
        $strings = explode("\n", $this->body);
        $saved = array();

        $data = FALSE;
        foreach($strings as $s) {

            $s = trim($s);
            if (empty($s)) continue;

            try
            {
                if ($s == 'ЗАКАЗ')  // new order start
                {
                    $data = TRUE;
                    $pay1 = $pay8 = $ship_price = $price = $total = $discount = $coupon = 0;
                    $changes = $goods = [];

                } 
                elseif ($data === TRUE) // строка данных заказа
                {
                    list(
                        $ship_date, // 1
                        $id,        // 2
                        $user_id,   // 3
                        $status,    // 4
                        $tmp,       // 5 - cумма скидки
                        $total,     // 6
                        $manager,   // 7
                        $courier,   // 8
                        $from,      // 9
                        $to,        // 10
                        $ship_type  // 11 - тип доставки
                        ) = Txt::parse_explode('©', $s, 11);
                        $data = FALSE;
                }
                elseif (mb_strpos($s, 'СКИДКА:') === 0)  // строка скидки
                {
                    $discount = intval(trim(mb_substr($s, 7)));
                }
                elseif (mb_strpos($s, 'АДРЕС') === 0)  // строка адреса
                {
                    $addr_pos = mb_strpos($s, ':');
                    $address_id = mb_substr($s, 5, $addr_pos - 5);
                    
                    if ($address_id == 0) continue;

                    list(
                        $city,    // 1
                        $street,  // 2
                        $addr,    // 3
                        $lift,    // 4
                        $floor,   // 5
                        $domofon, // 6
                        $kv,      // 7
                        $mkad,    // 8
                        $comment,  // 9
                        $approved  // 10
                        ) = Txt::parse_explode('|', mb_substr($s, $addr_pos + 1), 10);

                    list(
                        $house,
                        $correct_addr,
                        $latlong,
                        $enter
                        ) = Txt::parse_explode('©', $addr, 4);

                    if ($address_id < 0) {
                        $address = new Model_User_Address();
                    } else {
                        $address = new Model_User_Address($address_id);
                    }
                    $address->user_id      = $user_id;
                    $address->city         = $city;
                    $address->street       = $street;
                    $address->house        = $house;
                    $address->correct_addr = $correct_addr == 'Y' ? 1 : 0;
                    $address->latlong      = $latlong;
                    $address->enter        = $enter; // Подъезд
                    $address->lift         = $lift;  // Наличие лифта
                    $address->floor        = $floor;
                    $address->domofon      = $domofon;
                    $address->kv           = $kv;    // Квартира/офис
                    $address->mkad         = $mkad;
                    $address->comment      = $comment;
                    $address->approved     = $approved == 'Y' ? 1 : 0;

                    try {
                        $address->save();
                        $new_address = $address->id;
                    } catch (ORM_Validation_Exception $e) {
                        $this->error('Cannot save address for '.$s.' '.$e->getMessage(), $id);
                    }

                } 
                elseif (mb_strpos($s, 'ОПЛАТА:') === 0) // часть оплаты за заказ
                {
                    list($pay_type, $pay_amount, $canpay) = Txt::parse_explode('©', mb_substr($s, 7), 3);
                    if ($pay_type == Model_Order::PAY_CARD) {
                        $pay8 = $pay_amount;
                        $can_pay = $canpay == 'Y' ? 1 : 0;
                    }
                    if ($pay_type == Model_Order::PAY_DEFAULT) {
                        $pay1 = $pay_amount;
                    }
                }
                elseif (mb_strpos($s, 'ИЗМЕНЕНИЕ:') === 0) { // изменения
                    list($date, $time, $message) = Txt::parse_explode('|', mb_substr($s, 10), 3);
                    $changes[] = [
                        'date' => $date,
                        'time' => $time,
                        'message' => $message,
                    ];
                    Model_History::log('order', $id, $message . ' '. $date .' '.$time);
                }
                elseif (mb_strpos($s, 'НОМЕРРЕАЛ:') === 0) { // НОМЕРРЕАЛ:4156
                    $check = mb_substr($s, 10);
                }
                elseif (mb_strpos($s, 'ДАТАВРЕМЯЧЕКА:') === 0) { // ДАТАВРЕМЯЧЕКА:21.01.16| 12:23
                    $check_time = preg_replace('~(\d\d)\.(\d\d)\.(\d\d)\| (\d\d\:\d\d)~', '20$3-$2-$1 $4', mb_substr($s, 14));
                }
                elseif ($s != 'КОНЕЦЗАКАЗА') // товар
                {
                    list($code, $qty, $price) = Txt::parse_explode('©', $s, 3);
                    
                    switch ($code)
                    {
                        case 'systDOST':
                        case 'systDOSTTR': $ship_price += $price;
                            break;

                        case 'systMKAD': $ship_price += $price * $qty;
                            break;

                        case 'КУПОН:':
                            $coupon = new Model_Coupon(array('name' => $qty));
                            break;

                        default:
                            $good = ORM::factory('good')->where('code', '=', $code)->find();
                            if ( ! $good->loaded()) {
                                $this->error('Unknown good code - '.$code, $id);
                                continue;
                            }
                            $goods[$good->id] = array($qty => $price);
                            break;
                    }

                }
                elseif ($s == 'КОНЕЦЗАКАЗА') // проапдейтить заказ и отослать письмо
                {

                    $order = new Model_Order($id);
                    $order->load_with('data');

                    if ( ! $order->loaded())
                    {
                        $this->error('Unknown order '.$id, $id);
                        continue;
                    }

                    if ( ! $order->data->loaded())
                    {
                        $this->error('Order data not loaded '.$id, $id);
                        continue;
                    }
                    $order->user_id = $user_id;
                    $order->status = $status;
                    $order->price = $total - $ship_price;
                    $order->price_ship = $ship_price;
                    $order->manager = $manager;
                    $order->pay8 = $pay8;
                    $order->pay1 = $pay1;
                    //$order->payment = $pay_amount;
                    $order->pay_type = ! empty($pay8) ? Model_Order::PAY_CARD : Model_Order::PAY_DEFAULT;

                    if ( ! empty($check)) {
                        $order->check = $check;
                    }
                    if ( ! empty($check_time)) {
                        $order->check_time = $check_time;
                    }
                    if ($order->can_pay == 0 && ! empty($can_pay)) {
                        $order->can_pay = 1;
                        $payment_changed = TRUE;
                    }

                    $order->delivery_type = $ship_type;

                    if ( ! empty($coupon) && $coupon->loaded())
                    {
                        $order->coupon_id = $coupon->id;
                    }

                    $status_changed = $order->changed('status');

                    if ($status_changed && $order->status == 'F') { // только при первом подтверждении заказа
                        // если в заказе есть подарочный сертификат - сгенерим купон и вышлем почту про него
                        $order->activate_gift();
                    }

                    // смена статуса у заказа с карточной оплатой - может быть снятие или возврат
                    if ($status_changed && $order->pay_type == Model_Order::PAY_CARD) {
                        if (in_array($order->status, ['F', 'X'])) {

                            $authz = $order->payments->where('status', '=', Model_Payment::STATUS_Authorized)->find_all()->as_array('id');

                            if ($order->status == 'F') { // order delivered - charge money

                                $charged = 0;
                                $to_charge = intval($order->pay8 * 100); // в копейках!
                                foreach ($authz as $card) {
                                    if ($charged < $to_charge) { // ещё надо снимать деньги
                                        $sum = min($card->sum, $to_charge);
                                        if ($card->charge($sum)) {
                                            $charged += $sum;
                                        }
                                    }
                                }

                                if ($charged != $to_charge) {
                                    mail('m.zukk@ya.ru, a.sergeev@mladenec.ru', 'Снятая сумма не совпадает с запрошенной ' . $order->id, "$charged != $to_charge");
                                }

                            } elseif ($order->status == 'X') { // order cancelled - unblock money

                                $voided = 0;
                                $to_void = intval($order->pay8 * 100); // в копейках!
                                foreach ($authz as $card) {
                                    if ($voided < $to_void) { // пытаемся разблокировать пока не наберем нужную сумму
                                        $sum = min($card->sum, $to_void);
                                        if ($card->unblock($sum)) {
                                            $voided += $sum;
                                        }
                                    }
                                }
                                if ($voided != $to_void) {
                                    //mail('m.zukk@ya.ru', 'Разблокированная сумма не совпадает с запрошенной '.$order->id, "$voided != $to_void");
                                }
                            }
                        }
                    }

                    try {
                        $order->save();

                        if ($order->status == 'F' && ! empty($order->check)) { // если пришел статус - доставлено, то всегда генерим чек
                            try {
                                $order->get_check(TRUE);
                            } catch (Kohana_Exception $e) {
                                Log::instance()->add(Log::WARNING, 'проблемы при создании чека для заказа '.$order->id.': '.$e->getMessage());
                            }
                        }
                        if ( ! empty($goods)) $order->change_goods($goods);

                        $order_data = new Model_Order_Data($id);

                        $user = new Model_User($user_id);
                        $order_data->name           = $user->name;
                        $order_data->second_name    = $user->second_name;
                        $order_data->last_name      = $user->last_name;
                        $order_data->email          = $user->email;

                        $order_data->city           = $city;
                        $order_data->courier        = $courier;
                        $order_data->ship_date      = preg_replace('~(\d\d)\.(\d\d)\.(\d\d)~', '20$3-$2-$1', $ship_date);
                        $order_data->ship_time_text = $from.'-'.$to;

                        $order_data->street         = $street;
                        $order_data->house          = $house;
                        $order_data->correct_addr   = $correct_addr == 'Y' ? 1 : 0;
                        $order_data->latlong        = $latlong;
                        $order_data->enter          = $enter;
                        $order_data->lift           = $lift;
                        $order_data->floor          = $floor;
                        $order_data->domofon        = $domofon;
                        $order_data->mkad           = $mkad;
                        $order_data->comment        = $comment;

                        if ( ! empty($new_address)) {
                            $order_data->address_id = $new_address;
                        }

                        $order_data->save();
                        
                        // обработка смены статуса (письма, размещение в транспортной компании, пересчет накоплений по акциям и т.п.)
                        if ($status_changed) $order->on_status_change();

                        // письмо о смене типа оплаты
                        if ( ! empty($payment_changed)) $order->on_payment_change();

                        $saved[$order->id] = $order->id;

                        if ( ! empty($changes)) {
                            foreach($changes as $event) {
                                Model_History::log('order', $order->id, $event['date'].' '.$event['time'].' '.$event['message']);
                            }
                        }

                    } catch (ORM_Validation_Exception $e) {
                        $this->error('Cannot save order for '.$s.' '.$e->getMessage(), $id);
                    }
                    
                    $goods = []; // чистим список товаров
                }
            } 
            catch (Txt_Exception $ex)
            {
                $this->error($ex->getMessage(), $id);
            }
        }

        $this->view->saved = $saved;
    }

    /**
     * Приём юзеров от 1с
     */
    public function action_users_upload()
    {
        $strings = explode("\n", $this->body);
        $return = array();

        foreach ($strings as $s) {
            $s = trim($s);
            if (empty($s)) continue;

            try 
            {
                list(
                    $id,    // 1
                    $fio,   // 2
                    $phone, // 3
                    $email, // 4
                    $status // 5
                ) = Txt::parse_explode('©', $s, 5);
                
                list($phone, $phone2) = Txt::parse_explode('|', $phone, 2);

                if (empty($phone) AND ! empty($phone2) AND Valid::phone($phone2, 11)) { // если валиден второй телефон, то поменяем их местами
                    $phone = $phone2;
                    $phone2 = '';
                }

                $user = new Model_User($id);
                if ( ! $user->loaded()) {
                    $return[$id] = 'Not found';
                    continue;
                }

                if (FALSE === strpos($fio, ' ')) {
                    $user->name = $fio;
                } else {
                    @list($f, $i, $o)      = explode(' ', $fio); // No spaces may be there

                    $user->name        = $i;
                    $user->last_name   = $f;
                    $user->second_name = $o;
                    $user->email = $email;
                }
                if ($status == 'gold') $user->status_id = 1;

                $user->phone  = $phone;
                $user->phone2 = $phone2;

                try {
                    $user->save();

                    $return[$id] = 'ok';

                } catch (Kohana_Validation_Exception $e) {

                    $return[$id] = 'NO';
                    $this->error('Not valid user '.$s.' '.$e->getMessage());
                }
            }
            catch (Txt_Exception $ex)
            {
                $this->error($ex->getMessage());
            }
        }
        $this->view->users = $return;
    }
    
    /**
     * Приём каталога товаров от 1c
     */
    public function action_catalog_upload()
    {
        $strings = explode("\n", $this->body);

        switch($this->action) {
            case 'catalog':
                $section = ORM::factory('section');
                $group = ORM::factory('group');
                $parent_id = $section_id = 0;
                foreach($strings as $s) {
                    $s = trim($s);
                    if (empty($s)) continue;
                    
                    try {
                    
                        list(
                            $code,    // 1
                            $level,   // 2
                            $name,    // 3
                            $vitrina, // 4
                            $sort,    // 5
                            $active,  // 6
                            $filters, // 7
                            $brands   // 8
                            ) = Txt::parse_explode('©', $s, 8);

                        switch($level) {
                            case 1:
                            case 2:
                                // общая логика для обоих уровней каталога
                                $s = $section->clear()->where('code', '=', $code)->find();
                                $s->code = $code;
                                $s->name = $name;
                                $s->sort = $sort;
                                $s->vitrina = $vitrina;
                                $s->active = ($active == 'Y') ? '1' : '0';
                                $s->parent_id = 0;

                                // создаём транслит только для новых категорий, для старых сохраняем. менять можно только через админку. потому что за транслит цепляются урлы!
                                if (empty($s->translit)) {
                                    $clear_translit = $translit = Txt::translit($s->name);
                                    // транслит должен быть уникальным
                                    $i = 1;
                                    do {
                                        $dup = clone $section;
                                        $dup_count = $dup->clear()->where('translit', '=', $translit)->count_all();
                                        $translit = $clear_translit.'-'.$i;
                                        $i++;
                                    } while ($dup_count != 0);

                                    $s->translit = $translit;
                                }

                                if ($level == 1) { // верхний уровень
                                    // До save может не быть id, т.к. новый раздел.
                                    $s->save();
                                    $parent_id = $s->id;

                                } elseif ($level == 2) { // подсекция

                                    $s->parent_id = empty($parent_id) ? 0 : $parent_id;
                                    $s->save();

                                    $f = array_filter(explode(',', $filters));

                                    if ( ! empty($f)) {
                                        $cur_f = $s->filters->find_all()->as_array('code'); // текущие фильтры секции

                                        foreach($cur_f as $code => $fi) {
                                            $k = array_search($code, $f);
                                            if ($k === FALSE) { // кода нет среди фильтров => отцепим фильтр
                                                $fi->section_id = 0;
                                                $fi->save();
                                            } else {
                                                unset($f[$k]); // фильтр есть
                                            }
                                        }
                                        if ( ! empty($f)) { // добавить фильтры
                                            foreach($f as $code) {
                                                $fi = new Model_Filter();
                                                $fi->section_id = $s->id;
                                                $fi->code = $code;
                                                $fi->save();
                                            }
                                        }
                                    }
                                    $section_id = $s->id;
                                }
                                break;

                            case 4:

                                $g = $group->clear()->where('code', '=', $code)->find();
                                $g->section_id  = $section_id;
                                $g->code        = $code;
                                $g->name        = $name;
                                $g->sort        = $sort;
                                $g->vitrina     = $vitrina;
                                $g->active      = ($active == 'Y') ? '1' : '0';
                                $g->translit = Txt::translit($g->name);

                                $g->save();
                                break;
                        }
                    }
                    catch (Txt_Exception $ex)
                    {
                        $this->error($ex->getMessage());
                    }
                }
                break;

            case 'manufacturers':
                $brand = ORM::factory('brand');
                foreach($strings as $s) {
                    try
                    {
                        $s = trim($s);
                        if (empty($s)) continue;
                        list(
                            $code,   // 1
                            $name,   // 2
                            $active, // 3
                            $sort    // 4
                            ) = $this->parse('©', $s, 4, array(0,1,2));
                        $b = $brand->clear()->where('code', '=', intval($code))->find();
                        $b->code = $code;
                        $b->name = $name;
                        $b->active = ($active == 'Y') ? '1' : '0';

                        // создаём транслит только для новых брендов, для старых сохраняем. потому что за транслит цепляются урлы!
                        if (empty($b->translit)) {
                            $clear_translit = $translit = Txt::translit($b->name);
                            // транслит должен быть уникальным
                            $i = 1;
                            do {
                                $dup = clone $brand;
                                $dup_count = $dup->clear()->where('translit', '=', $translit)->count_all();
                                $translit = $clear_translit.'-'.$i;
                                $i++;
                            } while ($dup_count != 0);

                            $b->translit = $translit;
                        }

                        $b->save();
                    }
                    catch (Txt_Exception $ex)
                    {
                        $this->error($ex->getMessage());
                    }
                }
                break;

            case 'country':
                $country = ORM::factory('country');
                foreach($strings as $s) {
                    try
                    {
                        $s = trim($s);
                        if (empty($s)) continue;
                        list(
                            $code,   // 1
                            $name,   // 2
                            $active, // 3
                            $sort    // 4
                            ) = $this->parse('©', $s, 4,array(0,1,2));
                        $b = $country->clear()->where('code', '=', intval($code))->find();
                        $b->code = $code;
                        $b->name = $name;
                        $b->sort = $sort;
                        $b->active = ($active == 'Y') ? '1' : '0';
                        $b->save();
                    }
                    catch (Txt_Exception $ex)
                    {
                        $this->error($ex->getMessage());
                    }
                }
                break;

            case 'product':
                $good = ORM::factory('good');
                $group = ORM::factory('group');
                $prop = ORM::factory('good_prop');
                $brand = ORM::factory('brand');
                $country = ORM::factory('country');
                $good_ids = array();

                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', strings parsing started timer: ' . $this->timer());
                
                foreach($strings as $s) {
                    $s = trim($s);
                    if (empty($s)) continue;

                    list(
                        $code,        // 1
                        $name,        // 2
                        $bcode,       // 3
                        $gcode,       // 4
                        $price,       // 5
                        $has,         // 6
                        $big,         // 7
                        $new,         // 8
                        $old_price,   // 9
                        $best,        // 10
                        $off,         // 11
                        $recommended, // 12
                        $ccode,       // 13
                        $pack,        // 14
                        $weight,      // 15
                        $active,      // 16
                        $barcode,     // 17
                        $move,        // 18
                        $code1c,      // 19
                        $size,        // 20 - пока не используется, но передается из 1С
                        $id1c,        // 21 - Уникальный код в 1C
                        $nds          // 22 - НДС товара
                        ) = $this->parse('©', $s, 22);

                    $grid = 0;
                    if ($gcode != '30006296') { // это услуги - не искать группу
                        $gr = $group->clear()->where('code', '=', $gcode)->find(); // группа
                        if ( ! $gr->loaded()) {
                            $this->error('No group found with code ' . $gcode);
                            continue;
                        }
                        $grid = $gr->id;
                    }

                    $cid = 0;
                    if (empty($ccode)) {
                        $this->error('Empty country code: ' . $s);
                    } else {
                        $c = $country->clear()->where('code', '=', $ccode)->find();

                        if ( ! $c->loaded()) {
                            $this->error('No country found with code ' . $ccode);
                            continue;
                        }
                        $cid = $c->id;
                    }

                    $bid = 0;
                    if ($bcode != '30007742') { // это подарок - не искать бренд

                        $b = $brand->clear()->where('code', '=', $bcode)->find();
                        if ( ! $b->loaded()) {
                            $this->error('No brand found with code ' . $bcode);
                            continue;
                        }
                        $bid = $b->id;
                    }

                    $g = $good->clear()->where('code', '=', $code)->find();

                    $g->code        = $code;
                    $g->code1c      = $code1c;
                    $g->id1c        = $id1c;
                    $g->move        = ($move == 'Y') ? '1' : '0';
                    $g->name        = $name;
                    $g->big         = $big;
                    $g->pack        = $pack;
                    $g->brand_id    = $bid;
                    $g->country_id  = $cid;
                    $g->barcode     = $barcode;
                    $g->group_name  = ! empty($grid) ? $gr->name : '';
                    $g->section_id  = ! empty($grid) ? $gr->section_id : 0;
                    $g->group_id    = $grid;
                    $g->translit    = Txt::translit($g->group_name.' '.$g->name);
                    $g->nds         = $nds;

                    $new_item = NULL;
                    if ( ! $g->id) { // новый товар - включим активность
                        $g->new = $new_item = 1; // Отмечаем как новую, незаполненную. НЕ ПУТАТЬ с new!!! которая означает новинку в 1С и не используется.
                    }

                    // в _good_common changed обнулится, так что запоминаем тут что нужно
                    $move_changed = $g->changed('move');
                    $more_changed = $g->changed('section_id') || $g->changed('brand_id') || $g->changed('group_name') || $g->changed('name'); // изменения, которые надо передать в продвижение

                    $qty_flag = $this->_good_common($g, $has, $price, $active, $old_price); // общие действия с товаром (там и save !!!)

                    // {{{ продвижение, если нужно
                    $m = new Model_Move();
                    $m->good_id = $g->id;

                    if ($move_changed) { // смена галочки продвигать
                        $m->do = ($g->move == 1) ? 'create' : 'delete';
                    } elseif ($g->move == 1 && ($qty_flag || $more_changed)) { // изменения в товаре
                        $m->do = 'update';
                    }
                    if ($m->do) $m->create();
                    // }}}
                    
                    if($qty_flag)
                    {
                        $good_ids[$g->id] = $g->id;
                    }

                    $p              = $prop->clear()->where('id', '=', $g->id)->find();
                    $p->id          = $g->id;
                    $p->brand_id    = $b->id;
                    $p->weight      = $weight;
                    $p->size        = $size;
                    $p->recommended = $recommended;
                    $p->best        = $best;
                    $p->off         = $off;
                    
                    if ( ! is_null($new_item)) { $p->_new_item = $new_item; }
                    $p->save();
                }
                
                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', strings parsing finished timer: ' . $this->timer());
                
                Model_Good::on_qas_change($good_ids);
                
                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', QAS change processing finished timer: ' . $this->timer());
                
                Model_Good::refresh();
                
                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', refresh finished timer: ' . $this->timer());
                
                break;

            case 'product_light':
                $good = ORM::factory('good');
                $good_ids = array();

                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', strings parsing started timer: ' . $this->timer());
                
                foreach($strings as $s) {
                    $s = trim($s);
                    if (empty($s)) continue;

                    @list($code, $has, $price, $active, $old_price) = explode('©', $s);

                    $g = $good->clear()->where('code', '=', $code)->find();
                    if ( ! $g->loaded()) {
                        $this->error('Product not found with code '.$code.' for '.$s);
                        continue;
                    }
                    
                    $qty_flag = $this->_good_common($g, $has, $price, $active, $old_price);

                    if($qty_flag)
                    {
                        $good_ids[$g->id] = $g->id;
                        
                        if($g->move) // сообщим в продвижение, если изменения в товаре
                        {
                        	$m = new Model_Move();
                        
                        	$m->good_id = $g->id;
                        	$m->do      = 'update';
                        
                        	$m->create();
                        }
                    }
                }
                
                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', strings finished started timer: ' . $this->timer());
                
                Model_Good::on_qas_change($good_ids);
                
                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', QAS change processing finished timer: ' . $this->timer());
                
                Model_Good::refresh();
                
                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', refresh finished timer: ' . $this->timer());
                break;

            case 'filter_cat_val':
                $filter = ORM::factory('filter');
                $section = ORM::factory('section');
                $value = ORM::factory('filter_value');
                $glue = FALSE;

                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', strings parsing started timer: ' . $this->timer());
                
                foreach($strings as $s) {
                    $s = trim($s);
                    if (empty($s) && empty($glue)) continue;
                    if ($glue !== FALSE) $s = $glue.$s; // может быть несколько строк - тогда клеим
                    if (substr($s, -1, 1) == '~') {
                        $glue = substr($s, 0, -1);
                        continue;
                    }
                    $glue = FALSE;

                    list(
                        $code,  // 1
                        $scode, // 2
                        $name,  // 3
                        $values // 4
                        ) = $this->parse('©', $s, 4);

                    $sec = $section->clear()->where('code', '=', $scode)->find();
                    if ( ! $sec->loaded()) {
                        $this->error('Section not found with code '.$scode.' for string '.$s);
                        continue;
                    }

                    $f = $filter->clear()->where('code', '=', $code)->find();
                    $f->code        = $code;
                    $f->section_id  = $sec->id;
                    $f->name        = $name;
                    
                    $f->save();

                    $cur_v = $value->clear()->where('filter_id', '=', $f->id)->find_all()->as_array('code'); // current values

                    $vals = array_filter(explode('][', trim($values, '[]'))); // заданные значения фильтров
                    if ( ! empty($vals)) {
                        foreach($vals as $val) {
                            $val = trim($val);
                            if (empty($val)) continue;
                            list(
                                $code, // 1
                                $name, // 2
                                $sort  // 3
                                ) = $this->parse('|', $val, 3);

                            if ( ! empty($cur_v[$code])) { // has this code - update
                                $cur_v[$code]->name      = $name;
                                //$cur_v[$code]->sort      = $sort;
                                $cur_v[$code]->filter_id = $f->id;
                                $cur_v[$code]->save();
                                unset($cur_v[$code]); // exclude code from current

                            } else { // not has this code - add
                                $v = $value->clear()->where('code', '=', $code)->find();
                                $v->name      = $name;
                                $v->code      = $code;
                                $v->filter_id = $f->id;
                                
                                $v->save();
                            }
                        }
                    }
                    foreach($cur_v as $v) $v->delete(); // delete all values that not in vals
                }
                
                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', strings parsing finish timer: ' . $this->timer());
                
                break;

            case 'filter_goods':
                $filter     = ORM::factory('filter');
                $good       = ORM::factory('good');
                $value      = ORM::factory('filter_value');
                $last_good  = FALSE;

                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', strings parsing started timer: ' . $this->timer());
                
                foreach($strings as $s) {
                    $s = trim($s);
                    if (empty($s)) continue;

                    list($gcode, $fcode, $vcodes) = $this->parse('©', $s, 3);
                    $vcodes = array_filter(explode('|', $vcodes));

                    $g = $good->clear()->where('code', '=', $gcode)->find();
                    if ( ! $g->loaded()) {
                        $this->error('Product not found with code '.$gcode.' for '.$s);
                        continue;
                    }
                    if ($last_good != $gcode) { // для новых товаров чистим фильтры
                        Model_Filter_Value::clear_good($g->id);
                        $last_good = $gcode;
                    } 
                    
                    if ( ! empty($vcodes)) {
                        $f = $filter->clear()->where('code', '=', $fcode)->find();
                        if ( ! $f->loaded()) {
                            $this->error('Filter not found with code '.$fcode.' for '.$s);
                            continue;
                        }

                        $v = $value->clear() // получим значения фильтра с этими кодами
                            ->where('code', 'IN', $vcodes)
                            ->where('filter_id', '=', $f->id)
                            ->find_all()
                            ->as_array('code');

                        foreach($v as $code => $val) {
                            if ( ! in_array($code, $vcodes)) {
                                $this->error('Value not found for code '.$code.' for '.$s);
                                continue;
                            }
                        }
                        if ( ! empty($v)) {
                            Model_Filter_Value::bind($g->id, $f->id, $v); // биндим ко всем найденным кодам
                        }
                    }

                }
                
                Log::instance()->add(Log::INFO, $this->request->action() . ($this->action ? ', action '.$this->action : '').', strings parsing finish timer: ' . $this->timer());
                
                break;
        }
    }

    /**
     * Приём курьеров от 1с
     */
    public function action_couriers()
    {
        $strings = explode("\n", $this->body);

        $courier = ORM::factory('courier');

        foreach ($strings as $s) {
            $s = trim($s);
            if (empty($s)) continue;
//            Log::instance()->add(Log::INFO, $s);

            list(
                $id,    // 1
                $name,  // 2
                $active // 3
                ) = $this->parse('©', $s, 3);

            $c = $courier->clear()->where('id', '=', $id)->find();
            $c->name = $name;
            $c->active = ($active == 'Y') ? '1' : '0';
            $c->id = $id;
            $c->save();
        }
    }

    /**
     * Выгрузка заказов на звонки
     * Или приём отчёта по принятым заказам от 1с
     */
    public function action_call()
    {
        $strings = explode("\n", $this->body);
        if ( ! empty($strings)) Model_Call::in1c($strings); // call back protocol

        $this->view->call = ORM::factory('call')->where('in1c', '=', '0')->find_all(); // call straight protocol
    }

    /**
     * Общие операции с товаром для протокола product и product_light
     * Продвижение товара в рекламе, включает или выключает товар если сменили галочку, делает update если сменились св-ва
     * Логирование появления-пропажи товара,
     * @param Model_Good $g - товар
     * @param $has - наличие
     * @param $price - цена
     * @param $active - активность
     * @param $old_price - старая цена
     * @return bool
     */
    private function _good_common(Model_Good &$g, $has, $price, $active, $old_price)
    {
        $return = FALSE; // флаг появления или пропажи товара
        list($pr0, $pr1, $pr_buy) = explode('|', $price);

        $was_price = $g->price;
        $was_price_buy = $g->price_buy;
        $g->price = sprintf("%01.2f", $pr0);
        $g->active = $active;
        $g->price_buy = sprintf("%01.2f", $pr_buy);

        if (trim($old_price) != '') $g->old_price = sprintf("%01.2f", $old_price);

        $old_qty = $g->qty;
        if (strpos($has, '*') === 0 && $g->big) {
            $has = -1; // значение для крупногабаритки - есть на складе поставщика
        } elseif($has < 0) {
            $has = 0;
        }
        
        $g->qty = $has;

        if ($g->changed('active')) {
            Model_History::log('good', $g->id, 'active '.$g->active); // протоколируем смену активности
            $return = TRUE;
        }

        if ($g->changed('price')) {
            Model_History::log('good', $g->id, 'price '.$was_price.' => '.$g->price); // протоколируем смену цены
            $return = TRUE;
        }

        if ($g->changed('qty')) {
			$changed = $this->good_qty_changed($g->id, $g->qty, $old_qty);
			
			if( $changed && ( $old_qty != 0 && $g->qty == 0 ) ){
			
				$g->saveLastSeen();
			}
			
            $return = $return || $changed;
        }

        $report = FALSE;

        if ( ! empty($g->code) && (empty($g->id) || $g->changed('price') || $g->changed('price_buy'))) {
            $report = array(
                'name' => $g->group_name . ' ' . $g->name,
                'code' => $g->code,
                'barcode' => $g->barcode,
                'was' => $was_price,
                'price' => $g->price,
            );

            if (empty($g->id))
            {
                $report['action'] = 'new';
                $report['reason'] = 'новый товар';
            }
            elseif ($g->changed('price'))
            {
                $report['action'] = 'change_price';
                $report['reason'] = 'изменение цены';
            }
            elseif ($g->changed('price_buy'))
            {
                $report['action'] = 'change_price_buy';
                $report['reason'] = 'изменение закупочной цены';
                $report['was'] = $was_price_buy * Model_Mailog::PRICE_FRANSH_RATIO;
                $report['price'] = $g->price_buy * Model_Mailog::PRICE_FRANSH_RATIO;
            }
            else
            {
                $report['action'] = NULL;
                $report['reason'] = 'другая причина';
            }
        }

        $warn_users = ($g->changed('qty') OR ($g->changed('active') && $g->active == 1)) && (intval($g->qty) > 0) && ($g->show > 0); // разослать уведомления о поставке
        if ($warn_users) {
            $warns = ORM::factory('good_warn')->where('good_id', '=', $g->id)->with('user')->with('good')->find_all()->as_array();
            if ( ! empty($warns)) {
                $warns_send = 0;
                foreach($warns as $w) {
                    if ($w->warn()) $warns_send++;
                }
                Log::instance()->add(LOG::INFO, 'Send '.$warns_send." warns for good ".$g->id.' «'.$g->group_name.' '.$g->name.'»');
            }
        }
        
        $g->save();

        if (FALSE !== $report) { // уведомление об изменении товара
            Model_Mailog::log('good_change' . ($report['action'] == 'change_price_buy' ? '_fransh' : ''), $report, 'good', $g->id, $report['action'], TRUE);
        }

        $g->set_status_price(1, sprintf("%01.2f", $pr1));

        return $return;
    }
    
    /**
     * Приём акций от 1с
     */
    public function action_actions()
    {
        $strings = explode("\n", $this->body);

        $action = ORM::factory('courier');

        foreach ($strings as $s) {
            $s = trim($s);
            if (empty($s)) continue;
//            Log::instance()->add(Log::INFO, $s);
            
            if (mb_strpos($s, 'АКЦИЯ:') === 0) // акция
            { 
            
                if ( ! ($list = $this->parse('©', trim(mb_substr($s, 5)), 5, array(1)))) continue;
                list( 
                    $code,      //   1
                    $name1c,    // * 2
                    $type,      // * 3
                    $from,      //   4
                    $to,        //   5
                        ) = $list;
            } 
            elseif(mb_strpos($s, 'НАКОПИТЕЛЬНАЯ:') === 0)
            {   
                if ( ! ($list = $this->parse('©', trim(mb_substr($s, 14)), 2))) continue;
                list( 
                    $count_from,    //   1
                    $count_to,      // * 2
                        ) = $list;
            }
            
            $a = $action->clear()->where('code', '=', $code)->find();
            $a->name = $name1c;
            switch($type)
            {
                case 'PRICE':
                    $a->type = (string) Model_Action::TYPE_PRICE;
                    break;
                case 'PRICE_QTY':
                    $a->type = (string) Model_Action::TYPE_PRICE_QTY;
                    break;
                case 'PRICE_SUM ':
                    $a->type = (string) Model_Action::TYPE_PRICE_SUM;
                    break;
                case 'GIFT_SUM':
                    $a->type = (string) Model_Action::TYPE_GIFT_SUM;
                    break;
                case 'GIFT_QTY':
                    $a->type = (string) Model_Action::TYPE_GIFT_QTY;
                    break;
            }

            $a->active      = ($active == 'Y') ? '1' : '0';
            $a->from        = $from;
            $a->to          = $to;
            $a->count_from  = $count_from;
            $a->count_to    = $count_to;
            $a->save();
            
            if(mb_strpos($s, 'УСЛОВИЯЦЕЛЬ:') === 0) $target = TRUE;
            
            if(mb_strpos($s, 'ТОВАРЫ:') === 0)
            {   
                if ( ! ($goods = $this->parse('©', trim(mb_substr($s, 7))))) continue;
                
                if ($target)
                {
                    $a->set_traget_goods($goods);
                }
                else
                {
                    $a->set_goods($goods);
                }
            }
            
            
        }
    }
    
    /**
     * Выгрузка терминалов озона для 1С 
     */
    public function action_terminals()
    {
        $this->view->terminals = ORM::factory('terminal')
            ->where('is_active', '=', 1)
            ->find_all();
    }

    /**
     * Выгрузка нас пунктов для 1С
     */
    public function action_cities()
    {
        $this->view->cities = ORM::factory('city')
            ->with('region')
            ->find_all();
    }

    /**
     * Просчет стоимости доставки
     */
    public function action_calculate_delivery()
    {
        $strings = explode("\n", $this->body);
        $return = FALSE;

        foreach($strings as $s) {
            $s = trim($s);
            if (empty($s)) continue;

            // компания-город/терминал-вес(кг)-высота-ширина-длина-стоимость-Д/Т
            list(
                $ship_code, // 1
                $city_id,   // 2
                $weight,    // 3
                $height,    // 4
                $width,     // 5
                $length,    // 6
                $price,     // 7
                $ship_type  // 8
                ) = Txt::parse_explode('©', $s, 8);

            switch ($ship_code) {

                case Model_Order::SERVICE_OZON:

                    if ($weight > OzonDelivery::MAX_WEIGHT) {
                        $return['error'] = 'Максимальный вес не более '.OzonDelivery::MAX_WEIGHT;
                        break;
                    }
                    if ($ship_type != 'T') {
                        $return['error'] = 'Для озона можно только терминал';
                        break;
                    }
                    $terminal = new Model_Terminal($city_id);
                    if ( ! $terminal->loaded()) {
                        $return['error'] = 'Терминал не найден';
                        break;
                    }
                    if ($terminal->type != Model_Terminal::TYPE_OZON) {
                        $return['error'] = 'Запрошенный терминал - не ОЗОН';
                        break;
                    }

                    $ozon = new OzonDelivery();
                    $price = $ozon->get_price($terminal->code, $weight);
                    if ( ! $price) {
                        $return['error'] = 'Не удалось рассчитать цену';
                        break;
                    }
                    $return[] = [
                        'company' => $ship_code,
                        'tariff'  => $ship_code,
                        'price'   => $price,
                        'days'    => 0,
                    ];

                    break;

                case Model_Order::SERVICE_DPD:

                    if ($ship_type == 'D') {
                        $city = new Model_City($city_id);
                        if ( ! $city->loaded()) {
                            $return['error'] = 'Город не найден';
                            break;
                        }
                    } else {
                        $terminal = new Model_Terminal($city_id);
                        if ( ! $terminal->loaded()) {
                            $return['error'] = 'Терминал не найден';
                            break;
                        }
                        // но в запрос мы должны передать ид города!
                        $city_id = $terminal->city_id;
                    }
                    $dpd = new DpdSoap();

                    // serviceCode, serviceName, cost, days
                    $resp = $dpd->ship_price($city_id, $price, $weight, $height * $length * $width * 1e-6, $ship_type == 'D');

                    if ( ! empty($resp)) {
                        foreach($resp as $v) {
                            $return[] = [
                                'company' => $ship_code,
                                'tariff'  => $v->serviceCode,
                                'price'   => $v->cost,
                                'days'    => $v->days,
                            ];
                        }
                    } else {
                        $return['error'] = 'Не удалось рассчитать цену';
                        break;
                    }
                    break;

                case Model_Order::SERVICE_YA:

                    $city = new Model_City($city_id);
                    if ( ! $city->loaded()) {
                        $return['error'] = 'Город не найден';
                        break;
                    }
                    $city_to = $city->name.', '.$city->region->name;

                    $resp = yadost::searchDeliveryList($city_to, $weight, $height, $width, $length, $price, $ship_type == 'D');

                    // "tariffName": "Standart",
                    // "tariffId": "6",
                    // "isPublic": true,
                    // "type": "TODOOR",
                    // "cost": "350",
                    if ( ! empty($resp)) {
                        foreach($resp as $v) {
                            $return[] = [
                                'company' => $ship_code.'_'.$v->delivery->unique_name,
                                'tariff'  => $v->tariffId,
                                'price'   => $v->cost,
                                'days'    => $v->maxDays,
                            ];
                        }
                    } else {
                        $return['error'] = 'Не удалось рассчитать цену';
                        break;
                    }
                    break;

                default:
                    $return['error'] = 'Неправильный код компании '.$ship_code;
                    break;
            }
            break;
        }
        // компания-ид тарифа-стоимость-срок в днях
        $this->view->return = $return;
        //Log::instance()->add(Log::INFO, print_r($return, TRUE));
    }
}
