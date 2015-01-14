<?php
class Model_Event extends ORM
{
    
    protected $_table_name = 'z_event';

    protected $_table_columns = array(
        'id'        => '',
        'timestamp' => null,
        'user_id'   => '',
        'type'      => '',
        'item_id'   => '',
        'message'   => '',
    );

    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
    );

    const T_GOOD_ADD            = 11; // Впервые запись в БД
    const T_GOOD_INSTOCK        = 12; // qty != 0, а до этого было 0
    const T_GOOD_OUTSTOCK       = 13; // qty == 0, а до этого было != 0
    const T_GOOD_APPEAR         = 14; // show == 1 впервые
    const T_GOOD_SHOW           = 15; // show == 1, a до этого было == 0
    const T_GOOD_HIDE           = 16; // show == 0, a до этого было != 0
    const T_GOOD_PRICE_CHANGE   = 17; // show == 0, a до этого было != 0
    
    const T_ACTION_ACTIVE   = 21; // Включилась акция
    const T_ACTION_UNACTIVE = 22; // Выключилась акция

    /**
     * Добавить запись в лог событий
     * @param int $type
     * @param int $item_id
     * @return int
     */
    public static function log($type, $item_id, $message = '')
    {
        $user = Model_User::current();

        $h = new self();
        
        $h->values(array(
            'user_id'   => ! empty($user) ? $user->id : 0,
            'type'      => $type,
            'item_id'   => $item_id,
            'message'   => empty($message) ? Kohana::message('event', $type, '') : $message
        ));
        
        $h->save();

        return $h->id;
    }
}
