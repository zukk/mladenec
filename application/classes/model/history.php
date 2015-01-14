<?php

// моделька для истории изменений в админке и не только
class Model_History extends ORM {

    protected $_table_name = 'z_history';

    protected $_table_columns = array(
        'id' => '', 'ip' => '', 'timestamp' => '', 'user_id' => '', 'module' => '', 'item_id' => '', 'action' => '', 'changes' => '',
    );

    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
    );


    /**
     * Добавить запись в историю
     * @param string $module
     * @param mixed $item_id
     * @param $action
     * @param array $changes
     * @throws HTTP_Exception_403
     * @return int
     */
    public static function log($module, $item_id, $action, $changes = array())
    {
        $user = Model_User::current();

        $h = new self();

        if ( ! empty($_SERVER['REMOTE_ADDR'])) {
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
        } else {
            $ip = 0;
        }
        $h->values(array(
            'ip' => $ip,
            'user_id' => ! empty($user) ? $user->id : 0,
            'module' => $module,
            'item_id' => $item_id,
            'action' => $action,
            'changes' => json_encode($changes),
        ));
        $h->save();

        return $h->id;
    }
}
