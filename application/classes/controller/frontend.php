<?php
/**
 * Только то, что относится к фронтенду - что видит юзер
 */
class Controller_Frontend extends Controller_Smarty
{
    protected $shop = array(); // настройки витрины, в которой находимся

    public static $scripts = [
        'j/jquery.min.js',
        'j/jquery.countdown.js',
        'j/jquery.scrollTo.min.js',
        'j/script.js',
        'j/jquery.maskedinput.min.js',
        'j/fancybox/jquery.fancybox.pack.js',
        'j/history.min.js',
        'j/history-exec.js',
        'j/base64.js',
        'j/trunk8.js',


		'plugins/incdec/jquery.incdec.js',
		'plugins/pencilator/jquery.pencilator.js',
		'plugins/mladenecbox/jquery.mladenecbox.js',
		'plugins/mladenecaccordeon/jquery.mladenecaccordeon.js',
		'plugins/mladenecradio/jquery.mladenecradio.js',
		'plugins/mladenecradiotabs/jquery.mladenecradiotabs.js',
		'plugins/mladenecdateslider/jquery.mladenecdateslider.js',

        'j/ss.min.js', //стопсоветник stopsovetnik.ru
    ];

    public static $css = [
        'j/fancybox/jquery.fancybox.css',
        'c/style.css',
        'c/countdown.css',
		'plugins/incdec/style.css',
		'plugins/mladenecbox/style.css',
		'plugins/mladenecaccordeon/style.css',
		'plugins/mladenecradio/style.css',
		'plugins/mladenecradiotabs/style.css',
		'plugins/mladenecdateslider/style.css',
    ];

    public function before()
    {
        $layout_name = 'layout';
		
		if ( ! empty( Kohana::$hostnames[Kohana::$server_name]['is_mobile'])) {
			$layout_name = 'layout_mobile';
		}
		
		if (preg_match('#site_map#ius', Request::$current->uri())) {
		    $layout_name = 'sitemap';
			$this->tmpl['vitrina'] = 'sitemap';
		}
		
        $this->layout = View::factory('smarty:' . $layout_name, $this->tmpl);
        $this->layout->body = FALSE;
        
        parent::before();

        $this->shop = Kohana::$hostnames[Kohana::$server_name];

        if ($al = $this->request->query('autologin')) { // Логиним пользователя если пришёл с автологина
            if ($user = Model_User::autologin($al)) {
                $this->user = $user;
            }
        }

        if ($token = $this->request->post('token')) { // логин через соцсети?

            if ( ! ($domain = parse_url(URL::base(), PHP_URL_HOST))) $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

            $s = Request::factory('http://ulogin.ru/token.php?token=' . $token. '&host=' . $domain)->execute()->body();
            $user = json_decode($s, true);

            if (empty($user['identity'])) exit($user['error']);

            $ulogin = ORM::factory('ulogin', array('identity' => $user['identity']));

            if ($ulogin->loaded()) {

                $orm_user = $ulogin->user;

            } else {

                $ulogin->values($user); // создаём ulogin

                if ( ! ($orm_user = Model_User::current())) { // нет залогиненного

                    $orm_user = new Model_User(['email' => $user['email']]); // нашли по мылу?

                    if ( ! $orm_user->loaded()) { // создаём нового

                        $v = [
                            'name' => trim($user['first_name']),
                            'last_name' => trim($user['last_name']),
                            'email' => trim($user['email']),
                            'login' => trim($user['email']),
                            'password' => Text::random('distinct')
                        ];
                        $orm_user->create_new($v);
                    }
                }

                $ulogin->user_id = $orm_user->id; // привязываем к нашему юзеру
                $ulogin->create();
            }

            Model_User::login($orm_user); // логиним привязанного

            if ($this->request->post('mode') == 'cart') { // зашли с корзины - вернёмся туда же
                $this->return_json( [
                    'cart' => View::factory('smarty:product/cart', $this->cartParams())->render(),
                    // 'delivery' => Controller_Product::cart_delivery(),
                    'userpad' => $orm_user->userpad()
                ]);
            } else{
                $this->return_redirect($this->request->referrer());
            }};

        $this->layout->cart = FALSE;
        
        View::bind_global('vitrina',    Kohana::$server_name);

        if ($this->request->query('admitad_uid')) {
            Cookie::set('admitad_uid', $this->request->query('admitad_uid'), 30 * 24 * 3600); // 30 дней
        }
		
		// убираем от роботов цены
		if( $this->request->query('pr') ){
			$this->layout->robots = 'noindex,nofollow,noarchive';
		}
		
		// убираем html sitemap от роботов
		if( preg_match( '#site_map#ius', Request::$current->uri() ) ){
			$this->layout->robots = 'noindex,nofollow,noarchive';
		}
    }

    public function after()
    {
        $main = $this->request->controller() == 'page' && $this->request->action() == 'index'; // index page flag
        View::bind_global('main', $main);

        $ad = new Model_Ad();
        View::bind_global('ad', $ad);
        
        $catalog = Model_Section::get_catalog(FALSE, Kohana::$server_name);
        View::bind_global('top_menu', $catalog);
        
        if (FALSE === $this->layout->cart /* && !preg_match( '#^personal#ius', $this->request->uri() ) */ ) {
            $this->layout->cart = Cart::instance();
        }
        
        $this->layout->foot_menu = Model_Menu::html('foot_menu');
        
        if ( ! Model_User::logged()) {
            $reg_poll = ORM::factory('poll')
                ->where('active','=',1)
                ->where('closed','=',0)
                ->where('type','=',  Model_Poll::TYPE_REGISTER)
                ->order_by('id','DESC')
                ->find();

            if ($reg_poll->loaded()) $this->layout->register_poll = $reg_poll;
        }
        
        // синхронизация куков между доменами
        $hash = md5(Session::instance()->id().Cookie::$salt);
        $sync = array();
        foreach(Kohana::$hostnames as $key => $config) {
            if (Kohana::$server_name != $key) {
                $sync[$config['host']] = base64_encode('//' . $config['host'] . '/sync?mladenec=' . Session::instance()->id() . '&hash=' . $hash);
            }
        }
        $this->layout->sync = $sync;
        $this->layout->sid = Session::instance()->id();

        parent::after();
    }
}
