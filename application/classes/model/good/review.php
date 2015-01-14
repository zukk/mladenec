<?php
class Model_Good_Review extends ORM {

    protected $_table_name = 'z_good_review';

    protected $_has_many = array(
        'params' => array(
            'model' => 'section_param',
            'foreign_key' => 'review_id',
            'far_key' => 'param_id',
            'through' => 'z_review_param'
        ),
    );

    protected $_belongs_to = array(
        'author' => array('model' => 'user', 'foreign_key' => 'user_id'),
        'good' => array('model' => 'good', 'foreign_key' => 'good_id'),
    );

    protected $_table_columns = array(
        'id' => '', 'time' => '', 'user_id' => '', 'name' => '', 'text' => '',
        'good_id' => '', 'rating' => '', 'vote_ok' => '', 'vote_no' => '',
        'active' => '', 'hide' => '', 'priority' => ''
    );

    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            ),
            'good_id' => array(
                array('not_empty'),
            ),
            'rating' => array(
                array('not_empty'),
                array('digit'),
                array('range', array(':value', 1, 5)),
            ),
            'text' => array(
                array('not_empty'),
            ),
        );
    }

    protected function get_link($html = true)
    {
        $link = sprintf('/comment/view/%d', $this->id);
        return $html ? HTML::anchor($link, $this->name) : $link;
    }

    public function flag()
    {
        return array('active', 'hide');
    }

    /**
     * сохранение параметров отзыва
     * @param $review_id
     * @param $section_id
     * @param array $params
     */
    public function save_params($review_id, $section_id, array $params)
    {
        $insert = DB::insert('z_review_param');
        $array = array('good' => 1, 'bad' => -1, 'neutral' => 0, 'me' => 0);

        $doit = FALSE;
        foreach(array_keys($array) as $type) {
            if ( ! empty($params[$type])) { // старый параметр
                foreach($params[$type] as $param_id) {
                    if ( ! empty($param_id)) {
                        $doit = TRUE;
                        $insert->values(array('review_id' => $review_id, 'param_id' => $param_id));
                    }
                }
            }
            if ( ! empty($params[$type.'_add'])) { // новый параметр
                foreach($params[$type.'_add'] as $param) {
                    $p = new Model_Section_Param();
                    $p->values(array(
                        'value' => $param,
                        'section_id' => ($type == 'me') ? 0 : $section_id,
                        'type' => $array[$type],
                    ));
                    if ($p->validation()->check()) {
                        $doit = TRUE;
                        $p->save();
                        $insert->values(array('review_id' => $review_id, 'param_id' => $p->id));
                    }
                }
            }
        }
        if ($doit) $insert->execute();
    }
    
    public function admin_save()
    {
        $messages = array();
        
        $good = $this->good;
        if ( ! $good->loaded()) $messages['error'] = 'Ошибка загрузки товара';
        $good->review_count(); // Обновляем количество отзывов в товаре
        $good->save();
        
        $group = $good->group;
        if ( ! $group->loaded()) $messages['error'] = 'Ошибка загрузки группы товаров';
        $group->review_count(); // Обновляем количество отзывов в группе товаров
        $group->save();
        
        return $messages;
    }
}