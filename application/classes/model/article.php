<?php
// aкции
class Model_Article extends ORM {

	use Seo;
	
    protected $_table_name = 'z_article';

    protected $_belongs_to = array(
        'minimg' => array('model' => 'file', 'foreign_key' => 'preview_img'),
        'image' => array('model' => 'file', 'foreign_key' => 'img'),
    );

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'title' => '', 'description' => '', 'active' => '', 'preview' => '', 'text' => '', 'preview_img' => '', 'img' => '',
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
            'preview' => array(
                array('not_empty'),
            ),
            'text' => array(
                array('not_empty'),
            ),
        );
    }

    /**
     * @param bool $html
     * @return string
     */
    public function get_link($html = true)
    {
        $link = sprintf('/about/article/%d', $this->id);
        return $html ? HTML::anchor($link, $this->name) : $link;
    }

    /**
     * @static
     * @return string
     */
    static function get_list_link()
    {
        return '/about/article';
    }

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('active');
    }

    /**
     * Список картинок
     * @return array
     */
    public function img()
    {
        return array('preview_img' => array(105, 105));
    }
}
