<?php
/**
 * Только то, что относится к фронтенду - что видит юзер
 */
class Controller_Frontend extends Controller_Smarty
{
    protected $shop = array(); // настройки витрины, в которой находимся
    protected $is_kiosk = FALSE; // Это киоск?

    public static $scripts = [
        'j/jquery.min.js',
        'j/script.js',
        'j/jquery.maskedinput.min.js',
        'j/fancybox/jquery.fancybox.pack.js',
        'j/history.min.js',
        'j/history-exec.js',
        'j/base64.js',
        'j/adfox.js',
		'plugins/incdec/jquery.incdec.js',
		'plugins/pencilator/jquery.pencilator.js',
		'plugins/mladenecbox/jquery.mladenecbox.js',
		'plugins/mladenecaccordeon/jquery.mladenecaccordeon.js',
		'plugins/mladenecradio/jquery.mladenecradio.js',
		'plugins/mladenecradiotabs/jquery.mladenecradiotabs.js',
		'plugins/mladenecdateslider/jquery.mladenecdateslider.js',
    ];

    public static $css = [
        'j/fancybox/jquery.fancybox.css',
        'c/style.css',
        'c/averburg.css',
		'plugins/incdec/style.css',
		'plugins/mladenecbox/style.css',
		'plugins/mladenecaccordeon/style.css',
		'plugins/mladenecradio/style.css',
		'plugins/mladenecradiotabs/style.css',
		'plugins/mladenecdateslider/style.css',
    ];

    public function before()
    {
        parent::before();
        
        $bIsIframed = @Kohana::$hostnames[Kohana::$server_name]['is_iframed'];
        if ($bIsIframed) $this->request->secure(TRUE); // use only https for vk
        
        $this->shop = Kohana::$hostnames[Kohana::$server_name];

        $this->is_kiosk = strpos(Request::$user_agent, 'Kioska') !== FALSE;
        
        // Check external user.
        $this->external_account = Model_User_External::getAccountInfo($this->user);

        // If external account has contain user - replace it.
        if ( ! empty($this->external_account['user'])) {
            $this->user = $this->external_account['user'];
        }
        
        $this->layout->cart = FALSE;
        
        View::bind_global('is_kiosk',           $this->is_kiosk);
        View::bind_global('is_iframed',         $bIsIframed);
        View::bind_global('external_account',   $this->external_account);
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
        
        // {{{ синхронизация куков между доменами
        if ( ! @Kohana::$hostnames[Kohana::$server_name]['is_iframed'])
        {
            $hash = md5(Session::instance()->id().Cookie::$salt);
            $sync = array();
            foreach(Kohana::$hostnames as $key => $config) {
                if (Kohana::$server_name != $key) {
                    $sync[] = base64_encode('//' . $config['host'] . '/sync?mladenec=' . Session::instance()->id() . '&hash=' . $hash);
                }
            }
            $this->layout->sync = $sync;
            $this->layout->sid = Session::instance()->id();
        }
        // }}}
        
        parent::after();
    }
}
