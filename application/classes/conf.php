<?php
/**
 * Класс для работы с конфигом сайта [из БД]
 */
class Conf {

    const VITRINA_ALL       = 0;
    const VITRINA_MLADENEC  = 1;
    const VITRINA_EATMART   = 2;
    
    /**
     * @var	Conf Singleton instance container
     */
    protected static $_instance;

    /**
     * Get the singleton instance of this class
     * @return	Conf
     */
    public static function instance()
    {
        if (self::$_instance === NULL) self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * конструктор - берём данные из базы или кэша
     */
    function __construct()
    {
        $config = Cache::instance()->get('config');
        if (empty($config) || ! is_array($config)) {
            $config = ORM::factory('config', 1)->as_array();
            if ( ! empty($config['logo_id'])) {
                $config['logo'] = ORM::factory('file', $config['logo_id'])->as_array();
            }
            Cache::instance()->set('config', $config);
        }

        foreach($config as $k => $v) $this->{$k} = $v;
    }

    /**
     * Чистим кэш конфига
     */
    public static function clear_cache()
    {
        Cache::instance()->delete('config');
    }
}
