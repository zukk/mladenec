<?php
/**
 * Sms - отсылка, добавление в очередь, проверка статуса
 */
class Model_Sms extends ORM  {

    /* how much messages to be sended within a one step */
    const SEND_RATE         = 25;
    
    const STATUS_NEW        = 0;
    const STATUS_SENDING    = 1;
    const STATUS_SENT       = 2;
    const STATUS_ERROR      = 3;
    
    const SENDING_OK_CACHE_NAME = 'SMS_SENDING_OK';
    const SENDING_OK_DELAY      = 86400; // сутки
    
    public $qty = 0; // for qty in brand

    protected static $curl      = NULL;
    protected static $config    = NULL;

    protected $_table_name = 'z_sms';

    protected $_table_columns = [
        'id' => '',
        'user_id'=>'',
        'order_id'=>'',
        'phone' => '',
        'text' => '',
        'created_ts' => 0,
        'sent_ts' => '',
        'status' => 0,
        'priority' => 0,
        'gateway_answer' => ''
    ];
    
    protected $_belongs_to = [
        'user' => array('model' => 'user', 'foreign_key' => 'user_id')    
    ];

    /**
     * Добавить смс в очередь отправки
     * @param $phone
     * @param $text
     * @param int $user_id
     * @param int $order_id
     * @return mixed
     */
    public static function to_queue($phone, $text, $user_id = 0, $order_id = 0)
    {
        $phone = Txt::phone_clear($phone);
        if ( ! $phone) {
            Log::instance()->add(Log::INFO, 'Error when trying to add SMS to queue to user #' . $user_id . ' - wrong phone ' . $phone . ' format.');
            return FALSE;
        }
        
        $sms = ORM::factory('sms');
        $sms->values(array(
            'user_id'    => $user_id,
            'order_id'   => $order_id,
            'phone'      => Txt::phone_clear($phone),
            'text'       => $text,
            'created_ts' => time()
        ));
        $sms->save();
        
        Daemon::new_task();

        return $sms->id;
    }
    
    /**
     * Проверяет, корректно ли рассылаются СМС. 
     * Если за сутки ни одной смс
     */
    public static function sending_ok()
    {
        $last_sent = Cache::instance()->get(self::SENDING_OK_CACHE_NAME);
        
        if ( ! $last_sent)
        {
            $last_sent = DB::select('sent_ts')
                ->from('z_sms')
                ->where('sent_ts', '>', 0)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->execute()
                ->get('sent_ts');
            
            Cache::instance()->set(self::SENDING_OK_CACHE_NAME, $last_sent, self::SENDING_OK_DELAY);
        }

        return ($last_sent  > ( time() - self::SENDING_OK_DELAY ) );
    }

    /**
     * Отправка смс
     * На реальном сервере делает запрос curl, читает ответ XML
     * На тестовом сервере шлёт письмо на email = номер телефона
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function send()
    {
        if (is_null(self::$curl))   self::$curl     = new Curl();
        if (is_null(self::$config)) {
            $sms_config = Kohana::$config->load('sms')->as_array();
            if (Conf::instance()->sms_method == Model_Config::SMS_MTS) {
                self::$config = $sms_config['mts'];
            } else {
                self::$config = $sms_config['aquiropay'];
            }
        }

        if (Txt::phone_is_correct($this->phone) AND Txt::phone_is_mobile($this->phone)) {
            $phone = substr($this->phone, 1); // no leading +
            $cf = $this->order_id;
			
            if (Kohana::$environment == Kohana::PRODUCTION) { // по-настоящему шлём смс только на боевом
				
				if (Conf::instance()->sms_method == Model_Config::SMS_MTS ){

					$_request = "http://mcommunicator.ru/m2m/m2m_api.asmx/SendMessage?" . http_build_query([
						'msid' => $phone,
						'message' => trim($this->text),
						'naming' => 'mladenec.ru',
						'login' => self::$config['login'],
						'password' => md5( self::$config['password'] )
					]);
					$response = self::$curl->get_url($_request);
					
					libxml_use_internal_errors(TRUE);
					$data = simplexml_load_string($response);
					libxml_use_internal_errors(FALSE);

					if ( ! empty($data) && ctype_digit($data) && intval($data) > 0) {
						$this->gateway_answer = $data;
						Log::instance()->add(Log::INFO, 'SMS ' . $this->text . ' to ' . $phone . ', gate response: ' . $data);
                        $this->status = self::STATUS_SENT;
					} else {
						$this->gateway_answer = $response;
						Log::instance()->add(Log::ERROR, 'Incorrect response from sms mts gate: ' . $response);
                        $this->status = self::STATUS_ERROR;
					}

				} else {

					$response = self::$curl->get_url(self::$config['url'], array(
						'opcode' => 'send_message',
						'product_id' => self::$config['product_id'],
						'recipient' => $phone,
						'cf' => $cf,
						'text' => trim($this->text),
						'token' => md5(self::$config['merchant_id']
							. self::$config['product_id']
							. $phone
							. $cf
							. self::$config['secret_word']),
					));

					libxml_use_internal_errors(TRUE);
					$data = simplexml_load_string($response);
					libxml_use_internal_errors(FALSE);

					if ( ! empty($data->status)) {
						$this->gateway_answer = $data->status;
						Log::instance()->add(Log::INFO, 'SMS ' . $this->text . ' to ' . $phone . ', gate response: ' . $response);
                        $this->status = self::STATUS_SENT;
					} else {
						$this->gateway_answer = $response;
						Log::instance()->add(Log::ERROR, 'Incorrect response from sms acquiropay gate: ' . $response);
                        $this->status = self::STATUS_ERROR;
					}
				}
				
            } else { // на тестовом - шлём письмо с данными об смс
                $fake_sms = new Mail();
                $fake_sms->setText($this->text);
                $fake_sms->send('m.zukk@ya.ru', $phone . ' sms ' . $cf);
                $this->gateway_answer = 'SENT MAIL';
                $this->status = self::STATUS_SENT;
            }

        } else {

            $this->gateway_answer = 'Робот: не мобильный или некорректный номер '.$phone;
            $this->status = self::STATUS_ERROR;
        }
        
        $this->sent_ts = time();
        $this->save();
        
        // Если закешилась ошибка отправки, при удачной отправке сбрасываем кеш
        if ( ! self::sending_ok()) Cache::instance()->delete(self::SENDING_OK_CACHE_NAME);
    }
}
