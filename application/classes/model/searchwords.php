<?php

class Model_Searchwords extends ORM {

    protected $_table_name = 'search_words';

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'status' => '', 'is_error' => ''
    );
}
