<?php

class Model_Country extends ORM {

    //use Seo;

    protected $_table_name = 'z_country';

    protected $_belongs_to = array(
        'image225' => array('model' => 'file', 'foreign_key' => 'img225'),
    );

    protected $_table_columns = array('id' => '', 'name' => '', 'code' => '', 'active' => '', 'sort' => '', 'description' => '', 'img225' => '', 'search_words' => '');

    /**
     * Id - Name для чекбоксов
     * @param $idz
     * @return mixed
     */
    static public function id_name($idz)
    {
        return DB::select('id', 'name')
            ->from('z_country')
            ->where('id', 'IN', $idz)
//            ->order_by('sort')
            ->order_by('name')
            ->execute()
            ->as_array('id');
    }

    public function get_img()
    {
        return ORM::factory('file', $this->img225)->get_url();
    }

    public function img()
    {
        return ['img225' => [225, 120]];
    }
}