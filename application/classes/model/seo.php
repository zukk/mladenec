<?php
class Model_Seo extends ORM {
    /*
     * @property id
     * @property title
     * @property description
     * @property item_id
     * @property type
     */
    protected $_table_name = 'z_seo';

    protected $_table_columns = array(
        'id' => '', 'title' => '', 'description' => '', 'keywords' => '', 'item_id' => '', 'type' => ''
    );

    /**
     * Допустимые типы
     * @param $type
     * @return array|bool
     */
    public static function types($type = NULL)
    {
        $types = array('article' => 1, 'tag' => 2, 'section' => 3, 'good' => 4, 'brand' => 5, 'new' => 6, 'action' => 7);

        if (is_null($type)) return $types;

        return isset($types[$type]) ? $types[$type] : FALSE;
    }

    /**
     * Конструктор по модели и ид
     * @param $modelName
     * @param $id
     * @return ORM
     * @throws Kohana_Exception
     */
    public static function findSeo($modelName, $id)
    {
        $seo = ORM::factory('seo')
            ->where('type', '=', self::types($modelName))
            ->where('item_id', '=', $id)
            ->find();
        if ( $seo->loaded()) return $seo;

        return ORM::factory('seo')->values(['type' => self::types($modelName), 'item_id' => $id]); // sets values if not found
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'title' => array(
                array('not_empty'),
                array(array($this, 'unique'), array('title', ':value'))
            ),
            'description' => array(
                array('not_empty'),
                array(array($this, 'unique'), array('description', ':value'))
            ),
            'item_id' => array(
                array('not_empty'),
                array('digit')
            ),
            'type' => array(
                array('not_empty'),
                array('digit'),
            )
        );
    }
}
