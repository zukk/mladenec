<?php
class Model_Tag_Tree extends ORM {

    protected $_table_name = 'z_tag_tree';

    protected $_table_columns = array('id' => '', 'name' => '', 'code' => '', 'lft' => '', 'rht' => '',	'depth' => '');

    protected $_has_many = array(
        'tags' => array('model' => 'tag', 'foreign_key' => 'tree_id'),
    );

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            ),
        );
    }
}
