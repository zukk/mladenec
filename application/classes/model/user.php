<?php

class Model_User extends ORM {

    /* Username when working from CLI */
    const ROBOT_ID = 7;

    const STATUS_CHANGE = 20000; // сумма заказа для присвоения любимых клиентов

    protected $_table_name = 'z_user';

    protected static $current = NULL; // текущий залогиненный юзер

    protected $allow = FALSE; // кэш модулей, с которыми можно работать этому юзеру
    
    protected $_table_columns = array(
        'id' => '', 'status_id' => '', 'email' => '', 'login' => '', 'password' => '', 'checkword' => '', 'name' => '', 'second_name' => '', 'last_name' => '',
        'phone' => '', 'phone2' => '', 'last_visit' => '', 'created' => '', 'sub' => '', 'in1c' => '', 'order_notify' => '', 'sum' => ''
    );

    protected $_has_many = array(
        'kids' => array(
            'model' => 'user_child',
            'foreign_key' => 'user_id',
        ),
        'orders' => array(
            'model' => 'order',
            'foreign_key'   => 'user_id',
        ),
        'comments' => array(
            'model' => 'comment',
            'foreign_key'   => 'user_id',
        ),
        'good_reviews' => array(
            'model' => 'good_review',
            'foreign_key'   => 'user_id',
        ),
        'returns' => array(
            'model' => 'return',
            'foreign_key'   => 'user_id',
        ),
    );

    public function filters() {
        return array(
            'phone' => array(
                array('Txt::phone_clear', array(':value')),
            ),
            'phone2' => array(
                array('Txt::phone_clear', array(':value')),
            )
        );
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
                array('not_empty'),
                array('email'),
//                array(array($this, 'unique'), array('email', ':value')), - moved to register function due to duplicate mails in some users
            ),
            'phone' => array(
                array('not_empty'),
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

        $cart = Cart::instance()->recount();

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
    public static function reset_password($id)
    {
        $user = ORM::factory('user', $id);
        if ( ! $user->loaded()) return false;

        $salt = Text::random();
        $pass = Text::random();
        $user->password = $salt.md5($salt.$pass);
        $user->save();

        Mail::htmlsend('reset', array('user' => $user, 'passwd' => $pass), $user->email, 'Для Вас был создан новый пароль пользователя');

        return $user;
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

        return $return;
    }

    /**
     * подсчёт суммы уже сделанных заказов конкретного товара
     * @param $action - Акция по которой считаем сумму
     * @return float
     */
    public function get_goods_sum(Model_Action $action)
    {
        
        $from_order = 0;
        if ($action->count_from) { // накопительная
            $from_order = DB::select('from_order')
                ->from('z_action_user')
                ->where('z_action_user.user_id', '=', $this->id)
                ->where('z_action_user.action_id', '=', $action->id)    
                ->execute()->get('from_order');
            
            $last_order_with_present = DB::select('order_id')
                ->from('z_order_good')
                    ->join('z_order')
                    ->on('z_order_good.order_id','=','z_order.id')
                ->where('action_id', '=', $action->id)
                ->where('z_order.user_id', '=', $this->id)
                ->order_by('order_id','DESC')
                ->limit(1)
                ->execute()->get('order_id');
            
            $from_order = ($from_order > $last_order_with_present) ? $from_order : $last_order_with_present;
        }
        
        if ( ! $action->total) { // в акции участвуют не все товары
            $good_idz = $action->good_idz();
            if ( empty($good_idz)) return 0;
        }

        $q = DB::select(DB::expr('SUM(og.price * og.quantity) as sum'))
            ->from(array('z_order', 'o'))
                ->where('o.status', '=', 'F')
                ->where('o.id', '>', $from_order)
                ->where('o.created' , '>=', $action->count_from)
                ->where('o.user_id', '=', $this->id)
                ->where('o.created', '<', $action->count_to)
            ->join(array('z_order_good', 'og'))
                ->on('og.order_id', '=', 'o.id');

        if ( ! $action->total) $q->where('og.good_id', 'IN', $action->good_idz());
/*
        if ($from_order) {
            $q->where('o.id', '>', $from_order);
        } else {
            $q->where('o.created' , '>=', $action->count_from);
        }
*/  
        $result = $q->execute()->current();
        return floatval($result['sum']);
    }
    
    public function admin_save()
    {
        $messages     = array('errors'=>array(), 'messages'=>array());
        $misc         = Request::current()->post('misc');
        $modules      = array();
        $current_user = self::current();
        
        if ( ! empty($misc['access'])) {
            $modules = $misc['access'];
        }

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
        $current_user    = self::current();
        $allowed_modules = $this->allow();
        $messages        = array();
        
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
	 * Checks whether a column value is unique.
	 * NO!! Excludes itself if loaded.
	 *
	 * @param   string   $field  the field to check for uniqueness
	 * @param   mixed    $value  the value to check for uniqueness
	 * @return  bool     whteher the value is unique
	 */
	public function unique($field, $value)
	{
		$model = ORM::factory($this->object_name())
			->where($field, '=', $value)
			->find();
		
		return !$model->loaded();
		
		/* if ($model->loaded())
		{	
			return ( ! ($model->loaded() AND $model->pk() != $this->pk()));
		}

		return ( ! $model->loaded()); */
	}
}
