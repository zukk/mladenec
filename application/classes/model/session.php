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
}
