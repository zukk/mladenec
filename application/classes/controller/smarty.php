<?php

class Controller_Smarty extends Controller {

    protected $layout = FALSE; // common layout for all the pages

    protected $user; // сurrent (logged in) user
    
    protected $config; // configuration

    protected $tmpl = array(); // array for template variables
    
    protected $force_profiling = FALSE;
	
    public function before()
    {
        $host = empty($_SERVER['HTTP_HOST']) ? 'default' : $_SERVER['HTTP_HOST'];

        Kohana::$hostnames = Kohana::$config->load('domains')->as_array();

        foreach(Kohana::$hostnames as $key => $config) {
            if (empty(Kohana::$server_name)) Kohana::$server_name = $key;
            if ($host == $config['host']) Kohana::$server_name = $key;
        }
        reset(Kohana::$hostnames); // NOTE rewind here! do not move pointer of this array to prevent bad results in key() function!

        // Fetch user.
        $this->user = Model_User::current();

        View::bind_global('host',$_SERVER['HTTP_HOST']);
		
        if ( ! ( $this->layout instanceof Smarty_View )) {
            $this->layout = $this->layout = View::factory('smarty:empty', $this->tmpl);
            $this->layout->body = FALSE;
        }
        
        $this->config = Conf::instance();
        
        View::bind_global('user',    $this->user);
        View::bind_global('config',  $this->config);
    }

    public function after()
    {
        if (FALSE === $this->layout->body) {
            $controller = $this->request->controller();
            if ($controller == 'admin_ajax') $controller = 'admin/ajax';
            if ($controller != 'admin_json') $this->layout->body = View::factory('smarty:'.$controller.'/'.$this->request->action(), $this->tmpl); // inside
        }
        
        // Profiler:
        if ( Request::initial() === Request::current() // No profiling in a subqueries
                AND ! empty($this->user->login)
                AND  in_array($this->user->login, ['zukk'])
        ) {
            $this->layout->force_profiling = $this->force_profiling;
            $this->layout->profile = View::factory('profiler/stats');
        }

        if (Kohana::$environment === Kohana::DEVELOPMENT) error_reporting(E_ALL && ~E_NOTICE);

        $this->response->body($this->layout->render());
    }

    /**
     * Возврат json
     * @param $json
     */
    protected function return_json($json)
    {
        $this->response->body(json_encode($json));
        $this->response->headers('Content-Type', 'application/json; charset=utf-8')->send_headers();
        echo $this->response->body();
        exit();
    }

    /**
     * Возврат xml
     * @param $xml
     */
    protected function return_xml($xml)
    {
        $this->response->body($xml);
        $this->response->headers('Content-Type', 'text/xml; charset=utf-8')->send_headers();
        echo $this->response->body();
        exit();
    }

    /* возврат от формы - перегрузка страницы */
    public function return_reload()
    {
        if ($this->request->post('ajax') || $this->request->is_ajax()) {
            $this->return_json(array('reload' => true));
        } else {
            $this->request->redirect();
        }
    }

    /* возврат от формы - ошибки */
    public function return_error($e)
    {
        if ($this->request->post('ajax') || $this->request->is_ajax()) {
            $this->return_json(array('error' => $e));
        } else {
            $this->layout->error = $e;
        }
    }

    /* возврат от формы - ok */
    public function return_ok()
    {
        if ($this->request->post('ajax') || $this->request->is_ajax()) {
            $this->return_json(array('ok' => true));
        } else {
            $this->request->redirect();
        }
    }

    /* возврат от формы - редирект */
    public function return_redirect($url)
    {
        if ($this->request->post('ajax') || $this->request->is_ajax()) {
            $this->return_json(array('redirect' => $url));
        } else {
            $this->request->redirect($url);
        }
    }

    /* возврат от формы - html-код */
    public function return_html($html)
    {
        if ($this->request->post('ajax') || $this->request->is_ajax()) {
            $this->return_json(array('html' => $html));
        } else {
            $this->layout->html = $html;
        }
    }

    /**
     * Читает дату из Смарти-массива селектов
     * @param array $arr
     * @return string
     */
    public function read_date($arr)
    {
        if (empty($arr['Date_Day']) OR empty($arr['Date_Month']) OR empty($arr['Date_Year'])) return NULL;
        if (empty($arr['Time_Hour'])) $arr['Time_Hour'] = '00';
        if (empty($arr['Time_Minute'])) $arr['Time_Minute'] = '00';

        $d = date('Y-m-d H:i:s', strtotime(
            $arr['Date_Year'].'-'.$arr['Date_Month'].'-'.$arr['Date_Day']
                .' '.$arr['Time_Hour'].':'.$arr['Time_Minute'].':00'
        ));
        return $d;
    }
}
