<?php

class Model_Article extends ORM {

	use Seo;
	
    protected $_table_name = 'z_article';

    protected $_belongs_to = [
        'minimg' => ['model' => 'file', 'foreign_key' => 'preview_img'],
        'image' => ['model' => 'file', 'foreign_key' => 'img'],
    ];

    protected $_table_columns = [
        'id' => '', 'name' => '', 'title' => '', 'description' => '', 'active' => '', 'preview' => '', 'text' => '', 'preview_img' => '', 'img' => '',
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                ['not_empty'],
            ],
            'preview' => [
                ['not_empty'],
            ],
            'text' => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @param bool $html
     * @return string
     */
    public function get_link($html = true)
    {
        $link = Route::url('article', ['id' => $this->id]);
        return $html ? HTML::anchor($link, $this->name) : $link;
    }

    /**
     * @static
     * @return string
     */
    static function get_list_link()
    {
        return Route::url('article');
    }

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return ['active'];
    }

    /**
     * Список картинок
     * @return array
     */
    public function img()
    {
        return ['preview_img' => [105, 105]];
    }
}
