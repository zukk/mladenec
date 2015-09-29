<?php
class Model_Payment extends ORM {

    const STATUS_New = 0; // новый платеж, по которому не произведено операций
    const STATUS_PreAuthorized3DS = 1; // платеж ожидает завершения 3DS авторизации
    const STATUS_PreAuthorizedAF = 2; // платеж ожидает завершения подтверждения от владельца карты
    const STATUS_Authorized = 3; //средства заблокированы (2-х стадийный платеж)
    const STATUS_Voided = 4; // средства разблокированы(2-х стадийный платеж)
    const STATUS_Charged = 5; // отправлен запрос на списание средств
    const STATUS_Refunded = 6; //возврат средств выполнен
    const STATUS_Rejected = 7; //не удалось выполнить платёж
    const STATUS_Error = 8; // Последняя операция прошла с ошибкой
    const STATUS_Gone = 9; // Оплата просрочена
    const STATUS_ChargeApproved = 10; // получено подтверждение оплаты

    const GATE_RBS = 'rbs';
    const GATE_PAYTURE = 'payture';
    const GATE_CLOUDPAY = 'cloudpay';

    const MAIL_ERROR = 'zukker@gmail.com'; // кому слать ошибки в крайнем случае

    protected $_table_name = 'z_payment';

    protected $_primary_key = 'id';

    protected $_belongs_to = [
        'order' => ['model' => 'order', 'foreign_key' => 'order_id']
    ];

    protected $_table_columns = [
        'id' => '', 'order_id' => '', 'gate' => '', 'session_id' => '', 'status' => '',
        'sum' => '', 'status_time' => '', 'form_url' => ''
    ];

    protected $_conf = [];

    /**
     * Сообщения об ошибках пишем в лог и высылаем на почту
     * @param $message
     */
    private function error($message)
    {
        $subj = 'PaymentError [payment '.$this->id.', order '.$this->order_id.']';
        Log::instance()->add(Log::ERROR, $subj.': '.$message);
        $mail = Conf::instance()->mail_payment;
        if (empty($mail)) $mail = self::MAIL_ERROR;
        mail($mail, $subj, $message);
    }

    /**
     * В конструкторе новым платежам проставляем тип оплаты и проверяем для существующих
     * @param null $id
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);

        if ($this->loaded()) {
            if ( ! in_array($this->gate, self::gates())) {
                $this->error('Unknown gate for payment');
                return FALSE;
            }
        } else {
            $new_gate = Conf::instance()->accept_cards;
            if ( ! in_array($new_gate, self::gates())) {
                $this->error('Payment disabled');
                return FALSE;
            }
            $this->gate = $new_gate;
        }

        $this->_conf = Kohana::$config->load('payment')[$this->gate];
    }

    /**
     * Вернуть все возможные шлюзы оплаты
     */
    public static function gates()
    {
        return array(self::GATE_PAYTURE, self::GATE_RBS);
    }

    /**
     * Сделать запрос к сервису и вернуть результат
     * @param $url - url запроса (script_name)
     * @param $post - параметры запроса
     * @return mixed - результаты запроса
     */
    protected function request($url, $post)
    {
        $return = FALSE;
        try {
            $curl = new Curl();

            switch ($this->gate) {

                case self::GATE_PAYTURE:
                    $xml = $curl->get_url($this->_conf['url'].$url, array('Key' => $this->_conf['key']) + $post);
                    libxml_use_internal_errors(TRUE);
                    $data = simplexml_load_string($xml);
                    libxml_use_internal_errors(FALSE);
                    if ( ! empty($data)) {
                        $array = json_decode(json_encode($data), TRUE);
                        $return = $array["@attributes"];
                        if (empty($return["Success"]) || $return["Success"] != "True") {
                            $this->error('Unsuccessful request to '.$url.' returned '.var_export($return, TRUE));
                        }
                    } else {
                        $this->error('Cannot parse XML ['.$xml.'] for url '.$url);
                    }

                    break;

                case self::GATE_RBS:

                    $json = $curl->get_url($this->_conf['url'].$url,
                        $post + array('userName' => $this->_conf['key'], 'password' => $this->_conf['pass'])
                    );

                    $return = json_decode($json, TRUE);
                    if ($return == NULL) {
                        $this->error('Cannot parse JSON ['.$json.'] for url '.$this->_conf['url'].$url);
                    }
                    if ( ! empty($return['errorCode'])) {
                        $this->error('Unsuccessful request to '.$url.' returned '.var_export($return, TRUE));
                        $return = FALSE;
                    }
                    break;
            }

        } catch (Request_Exception $e) {
            $this->error('Payment request failed for url '.$url);
        }
        return $return;
    }

    /**
     * Инициализация платежа
     * @param Model_Order $o
     * @return string - ссылку на оплату
     */
    public function init(Model_Order $o)
    {
        if ( ! empty($this->session_id)) { // у этого платежа уже есть сессия - идём на оплату
            return $this->form_url;
        }

        $total = $o->get_total(); // сколько снимать - вычисляем по заказу

        $this->order_id = $o->id;
        $this->sum = $total * 100; // сумма в копейках

        if (empty($this->id)) $this->save(); // get id here

        switch($this->gate) {
            case self::GATE_PAYTURE:
                $data = 'SessionType=Block;OrderId='.$this->id.';Amount='.($this->sum).';Rub='.$total;
                $res = $this->request('Init', array('Data' => $data));

                $this->session_id = $res['SessionId'];
                $this->form_url = $this->_conf['url']."Pay?SessionId=".$this->session_id;

                break;

            case self::GATE_RBS:
                $res = $this->request('registerPreAuth.do', [
                    'orderNumber' => $this->id,
                    'amount' => $this->sum,
                    'returnUrl' => 'http://'.Kohana::$hostnames[Kohana::$server_name]['host'].Route::url('payment', ['todo' => 'pay_success'])
                ]);
                $this->session_id = $res['orderId'];
                $this->form_url = $res['formUrl'];

                break;

        }

        $this->save();
        return $this->form_url;
    }

    /**
     * Получение статуса платежа у провайдера и действия при разных статусах
     * @throws ErrorException
     * @return mixed
     */
    public function status()
    {
        $o = new Model_Order($this->order_id);
        if ( ! $o->loaded()) throw new ErrorException('No order for payment '.$this->id.' order '.$this->order_id);

        $state = 'Error'; // статус оплаты в виде строчки. в конце метода превращается в число
        switch($this->gate) {
            case self::GATE_PAYTURE:
                $res = $this->request('PayStatus', array('OrderId' => $this->id));
                if ($res === FALSE) return $this->status;

                if ( ! empty($res['Success']) && ($res['Success'] == 'True')) {
                    $state = $res["State"];
                } else {

                    if (in_array($res['ErrCode'], array('ORDER_TIME_OUT', 'ORDER_NOT_FOUND', 'NONE'))) { // оплата просрочена ?
                        // такой статус может быть и у новых заказов, отменяем заказ только если создан больше часа назад
                        if (time() - strtotime($this->status_time) > 3600) {
                            Log::instance()->add(Log::INFO, 'Payment: Got gone status for payment '.$this->id.' order '.$this->order_id);

                            $state = 'Gone';
                            $o->status = 'X'; // отменяем заказ
                            $o->save();
                        }
                    } else {
                        Log::instance()->add(Log::WARNING, 'Payment: Error '.$res['ErrCode'].' getting status for payment '.$this->id.' order '.$this->order_id);
                    }
                }
                break;

            case self::GATE_RBS:
                $res = $this->request('getOrderStatus.do', array('orderId' => $this->session_id));
                if ($res === FALSE) return $this->status;

                $states = [
                    0 => 'New',
                    1 => 'Authorized', // Проведена предавторизация суммы заказа
                    2 => 'Charged', // Проведена авторизация суммы заказа
                    3 => 'Voided', // Авторизация отменена
                    4 => 'Refunded', // По транзакции была проведена операция возврата
                    5 => 'PreAuthorized3DS', // Инициирована авторизация через ACS банка-эмитента
                    6 => 'Rejected', // Авторизация отклонена
                ];
                if (isset($states[$res['OrderStatus']])) $state = $states[$res['OrderStatus']];
                break;
        }

        switch ($state) {
            case 'New':
                break;
            case 'PreAuthorized3DS':
                $this->status = self::STATUS_PreAuthorized3DS;
                break;
            case 'PreAuthorizedAF':
                $this->status = self::STATUS_PreAuthorizedAF;
                break;
            case 'Authorized':
                $this->status = self::STATUS_Authorized;
                Log::instance()->add(Log::INFO, 'Payment: Got authorized status for payment '.$this->id.' order '.$this->order_id);
                break;
            case 'Voided':
                $this->status = self::STATUS_Voided;
                break;
            case 'Charged':
                $this->status = self::STATUS_ChargeApproved; // если получили такой статус запросом - то оплата подтверждена
                break;
            case 'Refunded':
                $this->status = self::STATUS_Refunded;
                break;
            case 'Rejected':
                $this->status = self::STATUS_Rejected;
                break;
            case 'Gone':
                $this->status = self::STATUS_Gone;
                break;
            case 'Error':
                $this->status = self::STATUS_Error;
                break;
        }
        $this->save();
        return $this->status;
    }

    /**
     * Разблокировать деньгу
     * @return bool
     */
    public function unblock($sum = FALSE)
    {
        $success = FALSE;
        if ($sum == FALSE) $sum = $this->sum;

        switch($this->gate) {
            case self::GATE_PAYTURE:
                $res = $this->request('Unblock', array('Password' => $this->_conf['pass'], 'Amount' => $sum, 'OrderId' => $this->id));
                $success = ! empty($res['Success']) && ($res['Success'] == 'True');
                break;

            case self::GATE_RBS:
                $res = $this->request('reverse.do', array('amount' => $sum, 'orderId' => $this->session_id));
                $success = isset($res['errorCode']) && ($res['errorCode'] == 0);
                break;
        }
        if ($success) {
            $this->status = self::STATUS_Voided;
            $this->save();
            Log::instance()->add(Log::INFO, 'Payment: Unblocked '.$sum.' for payment '.$this->id.' order '.$this->order_id);
        }
        return $success;
    }

    /**
     * Списать деньгу
     * @param $sum в копейках!
     * @return bool
     */
    public function charge($sum)
    {
        $success = FALSE;

        switch($this->gate) {
            case self::GATE_PAYTURE:
                $res = $this->request('Charge', array('Password' => $this->_conf['pass'], 'Amount' => $sum, 'OrderId' => $this->id));
                $success = ! empty($res['Success']) && ($res['Success'] == 'True');
                break;

            case self::GATE_RBS:
                $res = $this->request('deposit.do', array('amount' => $sum, 'orderId' => $this->session_id));
                $success = isset($res['errorCode']) && ($res['errorCode'] == 0);
                break;
        }
        if ($success) {
            $this->status = self::STATUS_Charged;
            $this->sum = $sum; // запомним сумму которую взяли
            $this->save();
            Log::instance()->add(Log::INFO, 'Payment: Charged '.$sum.' for payment '.$this->id.' order '.$this->order_id);
        }
        return $success;
    }

    /**
     * получение статуса платежа для пользователя (строка)
     */
    public function status_info()
    {
        if ($this->status == self::STATUS_New) return 'Новый платёж';
        if ($this->status < self::STATUS_Authorized) return 'Платеж ожидает завершения авторизации'; // платеж ожидает завершения 3DS авторизации
        if ($this->status == self::STATUS_Authorized) return 'Cредства заблокированы'; //средства заблокированы (2-х стадийный платеж)
        if ($this->status == self::STATUS_Voided) return 'Cредства разблокированы';
        if ($this->status == self::STATUS_Charged) return 'Отправлен запрос на списание средств';
        if ($this->status == self::STATUS_Refunded) return 'Возврат средств выполнен';
        if ($this->status == self::STATUS_Rejected) return 'Не удалось выполнить платёж';
        if ($this->status == self::STATUS_Gone) return 'Оплата просрочена';
        if ($this->status == self::STATUS_Error) return 'Последняя операция прошла с ошибкой';
        if ($this->status == self::STATUS_ChargeApproved) return 'Cредства списаны, платёж завершен';
        return 'Неопределённый статус';
    }

    /**
     * Сохранение времени смены статуса оплаты
     * @param null|\Validation $validation
     * @return ORM
     */
    public function save(Validation $validation = NULL)
    {
        if ($this->changed('status') || (empty($this->status_time) && empty($this->status))) {
            $this->status_time = date("Y-m-d H:i:s");
            DB::update('z_order')->set(['in1c' => 0])->where('id', '=', $this->order_id)->execute(); // обо всех сменах говорим 1с

            if ($this->status == self::STATUS_Authorized) { // добавляем оплаченную сумму к заказу
                $this->order->payed += $this->sum / 100;
                $this->order->save();
                Model_History::log('order', $this->order_id, 'Авторизован платёж '.$this->id.' на сумму '.$this->sum.' коп');

            } elseif ($this->status == self::STATUS_Voided) { // убавляем возвратную сумму от заказа

                $this->order->payed -= $this->sum / 100;
                $this->order->save();
                Model_History::log('order', $this->order_id, 'Возврат средств по платежу '.$this->id.' на сумму '.$this->sum.' коп');

            } else {
                Model_History::log('order', $this->order_id, 'Платёж '.$this->id.' на сумму '.$this->sum.' коп - '.$this->status_info());
            }
        }
        return parent::save($validation);
    }
}
