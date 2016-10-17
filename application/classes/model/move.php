<?php
// продвижение
class Model_Move extends ORM {

    protected $_table_name = 'z_move';

    protected $_table_columns = array(
        'id' => '', 'good_id' => '', 'do' => '', 'done' => '',
    );

    protected $_belongs_to = array(
        'good' => array(
            'model' => 'good',
            'foreign_key' => 'good_id',
        ),
    );
}
