<?php
/**
 * Model for process history search.
 * Use for a context search helper.
 * 
 * @package     mladenec.ru
 * @subpackage  search
 * @category    history
 * 
 * @author      iFabrik <input@ifabrik.ru>
 * @version     $Id$
 */
class Model_Search_History extends ORM
{
    /**
     * {@inheritdoc}
     */
    protected $_table_name = 'z_search_history';

    /**
     * {@inheritdoc}
     */
    protected $_created_column = array('column' => 'created_at', 'format' => 'Y-m-d H:i:s');

    /**
     * {@inheritdoc}
     */
    protected $_updated_column = array('column' => 'updated_at', 'format' => 'Y-m-d H:i:s'); 

    /**
     * {@inheritdoc}
     */
    protected $_table_columns = array(
        'id'                => '', 
        'search_query'      => '', 
        'tokenized_query'   => '',
        'rate'              => 0,
        'vitrina'           => ''
    );
}
