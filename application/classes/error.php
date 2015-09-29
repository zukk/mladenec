<?php
class Error {

    public static function handler(Exception $e) {
        switch (get_class($e))
        {
            case 'HTTP_Exception_404':

                $response = new Response;
                $response->status(404);
                echo $response->body(self::layout(404))->send_headers()->body();

                return TRUE;
                break;

            case 'HTTP_Exception_403':
                $context = Request::$current->controller() == 'admin' ? 'admin' : 'default';
                $response = new Response;
                $response->status(403);
                if ( ! empty($_POST['ajax'])) exit($response->body(json_encode(array('reload' => true))));
                echo $response->body(self::layout(403, $context))->send_headers()->body();

                return TRUE;
                break;

            default:
                return Kohana_Exception::handler($e);
                break;
        }
    }

    /**
     * Формирует и заполняет вьюшку ошибки (общий шаблон)
     * @param string $view
     * @param string $context
     * @return View
     */
    protected static function layout($view, $context = 'default')
    {
        switch ($context) {
            case 'admin';
                $tmpl = 'smarty:admin';
                break;
            default:
                $tmpl = 'smarty:layout';
                break;
        }

        $layout = View::factory($tmpl);

        $layout->body = View::factory('smarty:error/'.$view);
        $layout->controller = 'error';
        $layout->user = Model_User::current();

        if ($context == "default") { // реклама, корзина

            $host = empty($_SERVER['HTTP_HOST']) ? 'default' : $_SERVER['HTTP_HOST'];

            Kohana::$hostnames = Kohana::$config->load('domains')->as_array();

            foreach(Kohana::$hostnames as $key => $config) {
                if (empty(Kohana::$server_name)) { // по умолчанию - первый
                    Kohana::$server_name = $key;
                }
                if ($host == $config['host']) {
                    Kohana::$server_name = $key;
                }
            }
            reset(Kohana::$hostnames); // NOTE rewind here! do not move pointer of this array to prevent bad results in key() function!

            $layout->shop = Kohana::$hostnames[Kohana::$server_name];

            $layout->ad = $layout->body->ad = new Model_Ad();
            $layout->cart   = Cart::instance();
            $layout->config = Conf::instance();
            $layout->vitrina = Kohana::$server_name;
            $layout->top_menu = Model_Section::get_catalog(); // вывод меню каталога товаров - есть на всех страницах
        }

        if (Kohana::$environment === Kohana::DEVELOPMENT) {
            $layout->profile = View::factory('profiler/stats');
        }

        return $layout;
    }
}