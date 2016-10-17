<?php

class Model_Section_Param extends ORM {

    protected $_table_name = 'z_section_param';

    protected $_table_columns = array(
        'id' => '', 'section_id' => '', 'type' => '', 'value' => '', 'popularity' => ''
    );
    /**
     * Валидация
     * @return array
     */
    public function rules() {
        return array(
            'value' => array(
                array('not_empty'),
                array(array($this, 'unique_val'),
                    array(':value', ':type', ':section_id')),
            ),
        );
    }

    /**
     * @static Возвращает список параметров секции
     * @param $section_id
     * @return array
     */
    public static function for_section($section_id)
    {
        $params = self::factory('section_param')
            ->where('section_id', 'IN', array($section_id, 0))
            ->order_by('popularity', 'DESC')
            ->find_all();

        $return = array();
        foreach($params as $p) {
            if ($p->section_id == 0) $p->type = 'me';
            if ( ! empty($return[$p->type]) && count($return[$p->type]) == 7) continue;
            $return[$p->type][$p->id] = $p->value;
        }
        return $return;
    }

    /**
     * @static Возвращает массив параметров отзывов в виде удобном для показа в списке отзывов
     * @param array $review_ids
     * @param $section_id
     * @return array
     */
    public static function for_reviews(array $review_ids, $section_id)
    {
        $return = array();

        if (empty($review_ids)) return array();

        $params = DB::select('review_id', 'type', 'value', 'section_id')
            ->from('z_section_param')
            ->join('z_review_param')
            ->on('param_id', '=', 'id')
            ->where('section_id', 'IN', array($section_id, 0))
            ->where('review_id', 'IN', $review_ids)
            ->order_by('review_id')
            ->execute()
            ->as_array();

        $totals = array();
        foreach($params as $p) {
            if ($p['section_id'] == 0) $p['type'] = 'me';
            if (!isset($totals[$p['type']][$p['value']])) $totals[$p['type']][$p['value']] = 0;
            $totals[$p['type']][$p['value']]++;
            $return[$p['review_id']][$p['type']] = $p['value'];
        }
        $return['total'] = $totals;
        
        foreach($return['total'] as $type => $val) {
            arsort($return['total'][$type], SORT_NUMERIC);
            $return['total'][$type] = array_slice(array_filter($return['total'][$type], function($v) {return $v > 1;}), 0, 5);
        }

        return $return;
    }

    /**
     * Проверка уникальности значений для этой модели - добавляем секцию и тип
     * @param $value
     * @param $type
     * @param $section_id
     * @return bool
     */
    public function unique_val($value, $type, $section_id)
    {
        $model = ORM::factory($this->object_name())
            ->where('value', '=', $value)
            ->where('type', '=', $type)
            ->where('section_id', '=', $section_id)
            ->find();

        if ($this->loaded())
        {
            return ( ! ($model->loaded() AND $model->pk() != $this->pk()));
        }

        return ( ! $model->loaded());
    }
}