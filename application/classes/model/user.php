<?php

class Model_User extends ORM {
    /* Username when working from CLI */

    const ROBOT_ID = 7;
    const STATUS_CHANGE = 20000; // сумма заказа для присвоения любимых клиентов
    const CHILD_DISCOUNT_NO = 0; // скидка за заполнение данных о детях - можно получить
    const CHILD_DISCOUNT_ON = 1; // - получена, но не использована
    const CHILD_DISCOUNT_USED = 2; // - использована

    protected $_table_name = 'z_user';
    protected static $current = NULL; // текущий залогиненный юзер
    protected $allow = FALSE; // кэш модулей, с которыми можно работать этому юзеру
    protected $_table_columns = [
        'id' => '', 'status_id' => '', 'email' => '', 'login' => '', 'password' => '', 'checkword' => '', 'name' => '', 'second_name' => '', 'last_name' => '',
        'phone' => '', 'phone_active' => '', 'phone2' => '', 'last_visit' => '', 'created' => '', 'sub' => '', 'in1c' => '', 'order_notify' => '',
        'sum' => 0, // сумма заказов
        'qty' => 0, // число заказов
        'last_order' => 0, // номер последнего заказа
        'pregnant' => 0,
        'pregnant_terms' => '',
        'child_discount' => '',
    ];

    protected $_has_one = [
        'segment' => ['model' => 'user_segment', 'foreign_key' => 'user_id'],
    ];
    protected $_has_many = [
        'phones' => ['model' => 'user_phone', 'foreign_key' => 'user_id'],
        'ulogins'   => ['model' => 'ulogin', 'foreign_key' => 'user_id'],
        'kids' => [
            'model' => 'user_child',
            'foreign_key' => 'user_id',
        ],
        'orders' => [
            'model' => 'order',
            'foreign_key'   => 'user_id',
        ],
        'comments' => [
            'model' => 'comment',
            'foreign_key'   => 'user_id',
        ],
        'good_reviews' => [
            'model' => 'good_review',
            'foreign_key'   => 'user_id',
        ],
        'returns' => [
            'model' => 'return',
            'foreign_key'   => 'user_id',
        ],
    ];

    public function filters() {
        return [
            'phone' => [
                ['Txt::phone_clear', [':value']],
            ],
            'phone2' => [
                ['Txt::phone_clear', [':value']],
            ]
        ];
    }
	
	public function get_goods()
    {
		
		$goods = ORM::factory('user_good')->where('user_id', '=', $this->id )->select()->as_array('id');
		
		return $goods; 
	}
  
    public function rules()
    {
        return array(
/*
            'password' => array(
                array('not_empty'),
            ),
*/
            'email' => array(
//                array('not_empty'),
                array('email'),
//                array(array($this, 'unique'), array('email', ':value')), - moved to register function due to duplicate mails in some users
            ),
            'phone' => array(
                array('phone', array(':value', 11)),
            ),
            'phone2' => array(
                array('phone', array(':value', 11)),
            ),
            'name' => array(
                array('not_empty'),
            ),
        );
    }

    /**
     * {@inhertidoc}
     */
    public function create(Validation $validation = NULL)
    {
        parent::create($validation);
        Model_History::log('user', $this->id, 'create', $this->as_array()); // запоминаем в истории

        return $this;
    }

    /**
     * @static Логиним юзера
     * @param array|Model_User $data
     * @return bool|Model_User
     */
    public static function login($data)
    {
        if (is_array($data) AND ! empty($data['login']) AND ! empty($data['password'])) {
            $login = $data['login'];
            $password = $data['password'];

            $user = new Model_User;

            $user->password($password)
                ->where_open()
                    ->where('login', '=', $login)
                    ->or_where('email', '=', $login)
                ->where_close();

            $user = $user->find();

        } else {

            $user = $data;
        }

        if ( ! ($user instanceof Model_User) OR ! $user->loaded()) return FALSE;

        DB::update('z_user')->set(array('last_visit' => time()))->where('id', '=', $user->id)->execute();

        self::$current = $user;

        if (is_array($data) && ! empty($data['remember'])) {
            $user->remember();
        }
        Session::instance()->set('user', serialize($user))->write();

        Cart::instance()->recount(); // при логине всегда пересчитываем корзину

        return $user;
    }

    /**
     * Запомнить юзера в куку
     */
    public function remember()
    {
        Cookie::set('remember', $this->id, 60 * 60 * 24 * 365); // запомним на год
    }

    /**
     * Add password check to query
     * @param $password
     * @return Kohana_ORM
     */
    public function password($password)
    {
        return $this->where(DB::expr("MD5(CONCAT(MID(PASSWORD, 1, 8), ".Database::instance('default')->escape($password)."))"),  '=', DB::expr('MID(PASSWORD, 9, 32)'));
    }

    /**
     * Вытираем юзера из сессии, обнуляем текущего юзера
     * @return bool
     */
    public static function logout()
    {
        $cart = Cart::instance();
		$goods = $cart->goods;
		
        Cookie::delete('remember'); // delete from cookie
        Session::instance()->destroy(); // delete from session
		Session::instance()->restart();
		Session::instance()->read();
		
        $cart = Cart::instance();
        $cart->add($goods);

        self::$current = FALSE; // delete from class

        return FALSE;
    }

    /**
     * Залогинен ли сейчас юзер
     * @return bool
     */
    public static function logged()
    {
        return self::current() instanceof Model_User;
    }

    /**
     * @static return current user
     * @return Model_User
     */
    public static function current()
    {
        if (self::$current instanceof Model_User) return self::$current; // есть в классе - берём
		try {
            if (($u = Session::instance()->get('user'))
                && ($user = unserialize($u))
                && ($user instanceof Model_User)
            ) {
                self::$current = $user; // есть в сесии
            }
            if (($user_id = Cookie::get('remember'))  // cookies are signed and secure till nobody knows Cookie::$salt
                AND ($user = ORM::factory('user', $user_id))
                AND ($user->loaded())
            ) {
                self::$current = $user; // есть в запомнить меня
                $user->remember();
            }
        } catch (Session_Exception $e) {
            self::$current = NULL;
        }

        return self::$current;
    }

    /**
     * Automatically log in as a Robot, an special user for CLI
     * 
     * @return self
     */
    public static function i_robot() {
            
        $user = ORM::factory('user', self::ROBOT_ID);
        if ($user->loaded()) {
            self::$current = $user;
        } else {
            self::$current = NULL;
            Log::instance()->add(Log::ERROR, 'Cannot log in as a Robot #' . self::ROBOT_ID);
        }
        
        return self::$current;
    }
    
    /**
     * Update user - set him as known by 1c
     * @return Database_Query
     */
    function in1c()
    {
        return DB::update('z_user')
            ->set(array('in1c' => 1))
            ->where('id', '=', $this->id)
            ->execute();
    }

    /**
     * Включить или выключить скидку за данные о детях для пользователя
     * Включает только если юзер ещё её не использовал
     * @param on bool - включить или выключить
     * @return Model_Coupon|ORM
     */
    function child_discount($on = TRUE)
    {
        $cart = Cart::instance();

        if ($on) {
            if ($this->child_discount == Model_User::CHILD_DISCOUNT_ON) { // возвращаем сгенеренный
                $coupon = ORM::factory('coupon', ['user_id' => $this->id]);
                if ( ! $coupon->loaded()) return FALSE;
            }
            if ($this->child_discount == Model_User::CHILD_DISCOUNT_NO) { // не получал

                $coupon = Model_Coupon::generate(200, 201, 1, 1, $this->id); // генерим

                $this->child_discount = Model_User::CHILD_DISCOUNT_ON;
                $cart->load_coupon($coupon->name);
                $cart->recount();
                if (Valid::email($this->email) && $this->login == 'zukk') Mail::htmlsend('child_discount', ['user' => $this, 'coupon' => $coupon], $this->email, 'Ваш промо-код');
            }

        } else {

            if ($this->child_discount == Model_User::CHILD_DISCOUNT_ON) { // у него была возможность получить - убираем купон
                $this->child_discount = Model_User::CHILD_DISCOUNT_NO;
                $coupon = ORM::factory('coupon', ['user_id' => $this->id]);
                if ($coupon->loaded()) $coupon->delete();
                $coupon = FALSE;
            }
        }
        $this->save();

        if ( ! empty($coupon)) return $coupon;
    }

    /**
     * Если имя модуля не задано - возвращает список модулей, к которым есть доступ
     * Если задано - возвращает bool - есть ли доступ к этому модулю
     * @param bool $module_name
     * @return bool
     */
    function allow($module_name = FALSE)
    {
        if ($this->allow === FALSE) { // модули ещё не вытаскивали

            $this->allow = DB::select('module')
                ->from('z_user_admin')
                    ->where('user_id', '=', $this->id)
                ->execute()
                ->as_array('module', 'module');
        }

        if (empty($module_name)) return $this->allow;

        if ( ! empty($this->allow['admin'])) return TRUE; // полный доступ

        return ! empty($this->allow[$module_name]); // проверяем доступ по имени модуля
    }

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('status_id', 'sub');
    }

    /**
     * сброс пароля
     * @param $id int - Ид пользователя
     * @return Model_User|bool
     */
    public static function reset_password($id, $password = FALSE)
    {
        $user = ORM::factory('user', $id);
        if ( ! $user->loaded()) return false;

        $salt = Text::random();
        $pass = $password == FALSE ? Text::random() : $password;
        $user->password = $salt.md5($salt.$pass);
        $user->save();

        if ($user->validation()->check()) {

            $user->save();
            Mail::htmlsend('reset', array('user' => $user, 'passwd' => $pass), $user->email, 'Для Вас был создан новый пароль пользователя');
            return $user;
        }

        return $user->validation()->errors('user');
    }

    /**
     * Отписка мыла от рассылки
     * @param $mail - мыло, которое надо отписать
     * @return int - число затронутых записей
     */
    public static function unsubscribe($mail)
    {
        $return = DB::update('z_user')
            ->set(array('sub' => 0))
            ->where('email', '=', $mail)
            ->execute();

        rrapi::unsubscribe($mail);

        return $return;
    }

    /**
     * подсчёт накоплений по накопительной акции
     * @param $action - Акция по которой считаем сумму
     * @return array ['sum' => x, 'qty' => x, 'from_order' => x]
     */
    public function get_funded(Model_Action $action)
    {
        if (empty($action->count_from)) return 0;

        $from_order = intval(DB::select('from_order')
            ->from('z_action_user')
            ->where('z_action_user.user_id', '=', $this->id)
            ->where('z_action_user.action_id', '=', $action->id)
            ->execute()->get('from_order'));

        $last_order_with_present = DB::select('order_id') // учитывает возможность повторного накопления
            ->from('z_order_good')
                ->join('z_order')
                ->on('z_order_good.order_id','=','z_order.id')
            ->where('action_id', '=', $action->id)
            ->where('z_order.user_id', '=', $this->id)
            ->where('z_order.status', '!=', 'X')
            ->order_by('order_id','DESC')
            ->limit(1)
            ->execute()->get('order_id');

        $from_order = intval(($from_order > $last_order_with_present) ? $from_order : $last_order_with_present);

        if ($action->total) { // все товары - считаем сумму заказов (без доставки)

            $q = DB::select(DB::expr('SUM(o.price) as sum'), DB::expr('COUNT(*) as qty'))
                ->from(['z_order', 'o'])
                ->where('o.status', '=', 'F')
                ->where('o.id', '>', $from_order)
                ->where('o.created' , '>=', $action->count_from)
                ->where('o.created', '<', $action->count_to)
                ->where('o.user_id', '=', $this->id);

        } else { // считаем сумму по товарам акции

            $good_idz = $action->good_idz();
            if ( empty($good_idz)) return 0;

            $q = DB::select(DB::expr('SUM(og.price * og.quantity) as sum'), DB::expr('SUM(og.quantity) as qty'))
                ->from(['z_order', 'o'])
                ->join(['z_order_good', 'og'])
                    ->on('og.order_id', '=', 'o.id')
                ->where('o.status', '=', 'F')
                ->where('o.id', '>', $from_order)
                ->where('o.created' , '>=', $action->count_from)
                ->where('o.created', '<', $action->count_to)
                ->where('o.user_id', '=', $this->id)
                ->where('og.good_id', 'IN', $good_idz);
        }
        $return = $q->execute()->as_array()[0];
        return ['qty' => intval($return['qty']), 'sum' => floatval($return['sum']), 'from_order' => $from_order];
    }
    
    public function admin_save()
    {
        $messages     = ['errors' => [], 'messages' => []];
        $misc         = Request::current()->post('misc');
        $modules      = [];
        $current_user = self::current();
        
        if ( ! empty($misc['access'])) $modules = $misc['access'];

        /* Настройками доступа могут управлять только админы с полным доступом */
        if ($current_user->allow('admin')) {
            /* Не дать поменять доступ самому себе */
            if($current_user->id != $this->id) {
                $messages = $this->set_module_access($modules);
            } else {
                $messages['errors'][] = 'Запрещено менять доступ самому себе';
            }
        }
        
        return $messages;
    }
    
    private function set_module_access($modules = array())
    {
        $allowed_modules = $this->allow();
        $messages        = [];
        
        $changes = array_diff_key($modules, $allowed_modules);
        $changes_allowed = array_diff_key($allowed_modules, $modules);
        
        $changes = array_keys(array_merge($changes, $changes_allowed));
        
        if (empty($changes)) {
            // We have nothing to do
            return $messages;
        }
        
        // Даем админский доступ
        if ( ! empty($modules['admin']) AND empty($allowed_modules['admin'])) {
            // Чистим таблицу от лишних записей
            DB::delete('z_user_admin')->where('user_id','=',$this->id)->execute();
            // Вместо вычищенных добавляем админ
            DB::insert('z_user_admin',array('user_id','module'))->values(array($this->id, 'admin'))->execute();
            
            $messages['messages'][] = 'Пользователю #' . $this->id . ' (' . $this->name . ') разрешен полный доступ в админ-панель.';
            Model_History::log($this->object_name(), $this->id, 'grant_admin_access',array('comment'=>'Разрешен любой доступ в админ-панель'));
            $this->allow = FALSE;
            
            return $messages;
        }
        
        // Забираем все виды доступа
        // Сняты все флаги, но раньше доступ был, удаляем все-все
        if (empty($modules) AND ! empty($allowed_modules)) {
            DB::delete('z_user_admin')->where('user_id','=',$this->id)->execute();
            $messages['messages'][] = 'Пользователю #' . $this->id . ' (' . $this->name . ') запрещен любой доступ в админ-панель.';
            Model_History::log($this->object_name(), $this->id, 'deny_admin_access',array('comment'=>'Запрещен любой доступ в админ-панель'));
            $this->allow = FALSE;
            
            return $messages;
        }
        
        $do_insert = FALSE;
        $insert    = DB::insert('z_user_admin',array('user_id','module'));
        $deny      = array();
        $grant     = array();
        
        
        foreach ($changes as $ch_mod) {
            $name = ' #' . $ch_mod . ' ' . Kohana::message('admin',$ch_mod);
            // Забираем лишний доступ
            if ( empty($modules[$ch_mod]) AND ! empty($allowed_modules[$ch_mod])) {
                DB::delete('z_user_admin')->where('user_id','=',$this->id)->where('module','=', $ch_mod)->execute();
                $deny[] = $name;
                $this->allow = FALSE;
            }
            
            // Добавляем новые
            if ( ! empty($modules[$ch_mod]) AND empty($allowed_modules[$ch_mod])) {
                 $insert->values(array($this->id, $ch_mod));
                 $do_insert = TRUE;
                 $this->allow = FALSE;
                 $grant[] = $name;
            }
        }
        if ($do_insert) {
            $insert->execute();
        }
        
        if ( ! empty($deny)) {
            // Сообщение о запрещении доступа
            $msg = 'Пользователю #' . $this->id . ' (' . $this->name 
                        . ') запрещен доступ к модулям ' . implode(',', $deny) . ' ' 
                        . ' в админ-панель.';
            $messages['messages'][] = $msg;
            Model_History::log($this->object_name(), $this->id, 'deny_access',$deny);
        }
        
        if ( ! empty($grant)) {
            // Сообщение о разрешении доступа
            $msg = 'Пользователю #' . $this->id . ' (' . $this->name 
                        . ') разрешен доступ к модулям ' . implode(',', $grant) . ' ' 
                        . ' в админ-панель.';
            $messages['messages'][] = $msg;
            Model_History::log($this->object_name(), $this->id, 'allow_access',$grant);
        }
        
        return $messages;
    }

    /**
     * Сделать попытку автологина, вернуть юзера или FALSE
     * @param $hash
     * @return bool|Model_User
     */
    public static function autologin($hash)
    {
        $id = DB::select('id')->from('z_user')->where('autologin', '=', $hash)->limit(1)->execute()->get('id');
        if (empty($id)) return FALSE;

        $user = new self($id);
        self::login($user);
        return $user;
	}

    /**
     * Получить все телефоны пользователя
     * @return mixed
     */
    public function get_phones()
    {
		return $this->phones->order_by('id', 'desc')->find_all()->as_array('id');
	}

    /**
     * Получить активный телефон юзера (последний)
     * @return bool
     */
    public function get_phone_active()
    {
		if ( ! $this->loaded()) return FALSE;
		
		$phones = $this->get_phones();
		
		if ( ! empty($phones[$this->phone_active ])) return $phones[$this->phone_active ]->phone;

		return FALSE;
	}
        
     /**
     * Получить количество недель беременности
     * @return mixed
     */
    public function get_pregnant_weeks() {
        $weeks = floor((time() - $this->pregnant_terms) / (7 * 24 * 60 * 60));
        return $weeks = ($weeks <= 41) ? $weeks : null;
    }

    /**
     * Получить url аватарки
     * @return string
     */
    public function get_avatar() {
        if (empty($this->avatar_file_id))
            return false;
        $return = ORM::factory('file', $this->avatar_file_id)->get_url();
        return $return;
    }

    /**
     * Получить количество избранных
     * @return string
     */
    public function get_deffered_count() {
        return ORM::factory('deferred')
                ->where('user_id', '=', $this->id)
                ->count_all();
    }

    /**
     * Определяет, может ли пользватель делать заказ в один клик
     * @param null $good
     * @return bool|void
     */
    public static function can_one_click($good = NULL) {
        if (in_array(Request::$client_ip, ['127.0.0.1', '10.0.2.2']))
            return TRUE;

        $region = Session::instance()->get('region'); // регион пользователя
        $return = in_array($region, ['RU-MOW', 'RU-MOS']);
        if ($good) $return = $return && $good->big && $good->price > 4000;

        return $return;
    }

    /**
     * Получить активные адреса пользователя по дате использвания
     * @return array
     * @throws Kohana_Exception
     */
    function address()
    {
        return ORM::factory('user_address')
            ->where('user_id', '=', $this->id)
            ->where('active', '=', 1)
            ->order_by('last_used', 'DESC')
            ->order_by('id', 'DESC')
            ->find_all()
            ->as_array('id');
    }

    /**
     * Создание нового пользователя - шлёт СМС-ки и письма, и подписывает на рассылку
     * НЕ логинит!
     * @param $v - данные пользователя
     * @return Model_User
     */
    function create_new($v)
    {
        $salt = Text::random();

        $phone = Txt::phone_clear( isset($v['phone']) ? $v['phone'] : '');
        $this->values([
            'name'      => $v['name'],
            'login'     => $v['login'],
            'email'     => $v['email'],
            'password'  => $salt.md5($salt.$v['password']),
            'phone'     => strval($phone),
            'created'   => time(),
            'sub'       => 1, // подписка на рассылку - по умолчанию
        ])->create();

        Mail::htmlsend('register', array('user' => $this, 'passwd' => $v['password']), $v['email'], 'Добро пожаловать!'); // письмо о регистрации

        if ($phone && Txt::phone_is_mobile($v['phone'])) {
            Model_Sms::to_queue($v['phone'], 'Добро пожаловать! Логин:'.$v['email']."\n".'Пароль: '.$v['password']);  // смс о регистрации
        }

        return $this;
    }

    /**
     * API для работы с subscribe.ru
     * @param $action
     * @param array $params
     * @param string $session
     * @param int $request_id
     * @return mixed
     */
    static function _subscribe_api($action, $params = array(), $session = '', $request_id = 0)
    {
        return TRUE; // не вызываем апи т.к. не работаем сейчас с subscribe.ru

        $params['one_time_auth'] = [
            'login'     => 'mladenec',
            'sublogin'  => 'mladenec',
            'passwd'    => 'voo3Gal'
        ];

        $curl = new Curl();

        $returner = json_decode($curl->get_url('https://pro.subscribe.ru/api' . $session, [
            'apiversion'    => 100,
            'json'          => 1,
            'request.id'    => rand(100, 1000000),
            'request'       => json_encode(array_merge(['action' => $action], $params))
        ]), TRUE);

        if ( ! empty( $returner['REDIRECT'])) {
            $returner = self::_subscribe_api($action, $params, $returner['REDIRECT']);
        }

        return $returner;
    }

    /*
     * Средний чек клиента
     */
    function avg_check()
    {
        if ($this->qty == 0) return 0;
        return $this->sum/$this->qty;
    }

    /**
     * Сохранение юзера - синхрон с ГР
     */
    function save($validation = NULL)
    {
        if ($this->changed('sub') || $this->changed('last_order') || $this->changed('sum') || $this->changed('qty') || $this->changed('pregnant')) {
            GetResponse::renew($this->id);
            if ($this->sub == 0) rrapi::unsubscribe($this->email);
        }
        parent::save($validation);
    }

}
