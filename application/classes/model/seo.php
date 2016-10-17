<?php
class Model_Seo extends ORM {
    /*
     * @property id
     * @property title
     * @property description
     * @property item_id
     * @property type
     */
    const SEO_TYPE_ARTICLE = 1;
    const SEO_TYPE_TAG     = 2;
    const SEO_TYPE_SECTION = 3;
    const SEO_TYPE_GOOD    = 4;
    const SEO_TYPE_BRAND   = 5;
    const SEO_TYPE_NEW     = 6;
    const SEO_TYPE_ACTION  = 7;
    
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
        $types = array(
            'article' => self::SEO_TYPE_ARTICLE,
            'tag'     => self::SEO_TYPE_TAG,
            'section' => self::SEO_TYPE_SECTION,
            'good'    => self::SEO_TYPE_GOOD,
            'brand'   => self::SEO_TYPE_BRAND,
            'new'     => self::SEO_TYPE_NEW,
            'action'  => self::SEO_TYPE_ACTION
            );

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
