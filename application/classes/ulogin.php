<?php defined('SYSPATH') or die('No direct script access.');

class Ulogin {

    protected $config = array(
        // Возможные значения: small, panel, window
        'type'             => 'panel',

        // Сервисы, выводимые сразу
        'providers' => [
            'vkontakte',
            'facebook',
            'twitter',
            'google',
        ],

        // Выводимые при наведении
        'hidden' => [
            'odnoklassniki',
            'mailru',
            'livejournal',
            'openid'
        ],

        // Обязательные поля
        'fields'         => [ 'email', 'phone'],
    );

    protected static $_used_id = array();

    public static function factory(array $config = array())
    {
        return new Ulogin($config);
    }

    public function __construct(array $config = array())
    {
        $this->config = array_merge($this->config, Kohana::$config->load('ulogin')->as_array(), $config);
    }

    /**
     * Отрисовка виджета
     * @return string
     * @throws View_Exception
     */
    public function render()
    {
        $params =
            'display='.$this->config['type'].
            '&fields='.implode(',', array_merge($this->config['username'], $this->config['fields'])).
            '&providers='.implode(',', $this->config['providers']).
            '&hidden='.implode(',', $this->config['hidden']).
            '&redirect_uri='.Request::initial()->url(TRUE). //'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']
            '&optional='.implode(',', $this->config['optional']);

        $view = View::factory('smarty:user/ulogin')
            ->set('cfg', $this->config)
            ->set('params', $params);
        do
        {
            $uniq_id = "uLogin_".rand();
        }
        while(in_array($uniq_id, self::$_used_id));

        self::$_used_id[] = $uniq_id;

        $view->set('uniq_id', $uniq_id);

        return $view->render();
    }
}