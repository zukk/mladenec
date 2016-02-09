<?php

// моделька для сессий
class Model_Session extends ORM {

    protected $_table_name = 'z_session';

    protected $_table_columns = array(
        'id' => '', 'last_active' => '', 'data' => '', 'user_id' => ''
    );

    public function save(Validation $validation = NULL) {

        $this->last_active = time();
        parent::save($validation);
    }

    static function delete_old($user_id, $session_id)
    {
        DB::delete('z_session')
            ->where('user_id', '=', $user_id)
            ->where('id', '!=', $session_id)
            ->execute(); // сотрем все старые сессии

    }
}
