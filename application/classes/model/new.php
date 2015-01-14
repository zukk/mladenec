<?php
class Model_New extends ORM {

	use Seo;
	
    protected $_table_name = 'z_new';

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'img' => '', 'date' => '', 'preview' => '', 'text' => '', 'active' => '', 'title' => '', 'description' => ''
    );

    protected $_belongs_to = array(
        'image' => array('model' => 'file', 'foreign_key' => 'img')
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
            'date' => array(
                array('not_empty'),
                array('date'),
            ),
            'preview' => array(
                array('not_empty'),
            ),
            'text' => array(
                array('not_empty'),
            ),
        );
    }

    public function get_link($html = true)
    {
        $link = sprintf('/about/news/%d', $this->id);
        return $html ? HTML::anchor($link, $this->name) : $link;
    }

    static function get_list_link()
    {
        return '/about/news';
    }

    /**
     * Порядок в списке в админке
     * @return $this
     */
    public function admin_order()
    {
        return $this->order_by('date', 'DESC');
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
        return array('img' => array(80, 80));
    }
}
