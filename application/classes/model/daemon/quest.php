<?php

class Model_Daemon_Quest extends ORM {
    
    const STATUS_NEW        = 0;
    const STATUS_WORKING    = 1;
    const STATUS_DONE       = 2;
    const STATUS_ERROR      = 3;
    
    protected $_table_name = 'z_daemon_quest';
    
    protected $_table_columns = array(
        'id'        => '',
        'action'    => '',
        'params'    => '',
        'created'   => null,
        'status'    => self::STATUS_NEW,
        'done_ts'   => 0,
        'delay'     => 0,
    );
    
    public function status_name()
    {
        switch($this->status)
        {
            case self::STATUS_WORKING:
                $name = 'В работе';
                break;
            case self::STATUS_DONE:
                $name = 'Выполнена';
                break;
            case self::STATUS_ERROR:
                $name = 'Ошибка';
                break;
            default:
                $name = 'Новая';
        }
        
        return $name;
    }
}