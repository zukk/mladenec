<?php
class Model_Menu extends ORM {

    protected $_table_name = 'z_menu';

    protected $_belongs_to = array(
        'parent' => array('model' => 'menu', 'foreign_key' => 'parent_id')
    );

    protected $_table_columns = array(
        'id' => '', 'link' => '', 'parent_id' => '', 'name' => '', 'description' => '', 'text' => '', 'show' => '', 'menu' => '', 'sort' => '',
    );

    /**
     * @return array
     */
    public function rules()
   	{
   		return array(
   			'link' => array(
   				array('not_empty'),
                array(array($this, 'unique'), array('link', ':value')),
   			),
   			'name' => array(
   				array('not_empty'),
   			),
            'text' => array(
                array('not_empty'),
            ),
   		);
   	}

    /**
     * @static Получение html-кода менюшки в нужном шаблоне
     * TODO кэшировать результат запроса
     * @param string $tmpl
     * @return string
     */
    public static function html($tmpl = 'menu')
    {
        $tree = Cache::instance()->get('menu');

        if (empty($tree)) {

            $menu = ORM::factory('menu')
                ->where('show', '=', 1)
                ->where('menu', '=', 1)
                ->order_by('parent_id')
                ->order_by('sort')
                ->find_all()
                ->as_array('id');

            $tree = array();
            foreach($menu as $item) {
                if ($item->parent_id > 0) {
                    $tree[$item->parent_id]['children'][$item->id] = $item->as_array();
                } else {
                    $tree[$item->id] = $item->as_array() + array('children' => '');
                }
            }
            Cache::instance()->set('menu', $tree);
        }

        return View::factory('smarty:'.$tmpl, array('menu' => $tree))->render();
    }

    public function get_link($html = true)
    {
        return $html ? HTML::anchor($this->link, $this->name) : $this->link;
    }

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('show', 'menu');
    }

    /**
     * Список страниц для админки
     * @return array
     */
    public function admin_order()
    {
        return $this->order_by('sort');
    }

    /**
     * @static Получить список возможных родительский страниц
     * @param int $exclude
     * @return Database_Result
     */
    public static function parents($exclude = 0)
    {
        return ORM::factory('menu')
            ->select(array('id', 'name'))
            ->where('parent_id', '=', 0)
            ->where('id', '!=', $exclude)
            ->order_by('sort')
            ->find_all()
            ->as_array('id', 'name');
    }
}
