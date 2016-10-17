<?php

class Model_Action_Group extends ORM
{
    protected $_table_name = 'z_action_group';

    protected $_table_columns = array(
        'id'                => '',
        'active'            => '',  // Группа акций активна. Активируется исходя из активности акций внутри
        'require_all'       => '',  // Require All Actions Active - все allowed условия должны быть активны, чтобы отображалась акция
        'name'              => '',  
        'banner'            => '',  // URL файла плашки
        'preview'           => '',  // Краткое описание
        'text'              => '',  // Описание
        'show'              => '',  // Опубликовать
        'main'              => '',  // Отображать на главной
        'show_wow'          => 0,   // Отображать в WOW акциях
        'show_actions'      => 0,   // Отображать в списке акций
        'show_vitrina'      => 0,   // id витрины на которой отображается группа акций, 0 = все витрины. ID заданы в Conf
        'order'             => '',  // сортировка
        'show_gifticon'     => 0,   // Отображать значок подарка у товара
        'cart_icon'         => '',  // иконка, появляющаяся в накопительных акциях внизу в корзине
        'cart_icon_text'    => '',  // префикс текста к иконке, появляющийся в накопительных акциях внизу в корзине
        'show_goods'        => '',  // Отображать товары
        'incoming_link'     => '',  // Флаг входящей ссылки, 
        'link_comment'      => '',  // ... и комментарий к ней
        'sync_1c'           => '',
        'visible_goods_cnt' => '',
    );
    
    protected $_has_many = array(
        'actions' => array(
            'model'         => 'action',
            'far_key'       => 'action_id',
            'foreign_key'   => 'group_id',
        ),
    );

    public static function get_by_goods($good_ids, $active = TRUE)
    {
        $action_groups_q = ORM::factory('action_group')
                ->join('z_action')->on('z_action.group_id', '=', 'action_group.id')
                ->join('z_action_good')->on('z_action_good.action_id', '=', 'z_action.id')
                ->where('z_action_good.good_id','IN', $good_ids);
        if ($actiove)
        {
            $action_groups_q
                    ->where('z_action.active','=',1)
                    ->where('z_action_group.active','=',1);
        }
        
        return $action_groups_q->find_all()->as_array();
    }
    
    /**
     * Получить список активных групп акций, относящихся к товарам, для показа иконок акций у товаров
     * сюда не попадают акции, в которых участвуют все товары и не попадают акции без флага show_gifticon
     * @param $good_ids
     * @return array массив ид товара => акции в которых он учавствует
     */
    public static function by_goods($good_ids)
    {
        // TODO: переписать!!!!
        
        $return = $ag = array();

        if (empty($good_ids)) return $return;

        $active_actions = self::get_active();
        if (empty($active_actions)) return $return;

        $active_actions = array_filter($active_actions, function ($a) { return ! $a->total;});
        if (empty($active_actions)) return $return;

        $action_goods = DB::select('good_id', 'action_id')
            ->from('z_action_good')
            ->where('action_id', 'IN', array_keys($active_actions))
            ->where('good_id', 'IN', $good_ids)
            ->execute()
            ->as_array();

        if (empty($action_goods)) return $return; // Нет совпадающих товаров

        foreach($action_goods as $item)
        {
            if (empty($ag[$item['good_id']])) $ag[$item['good_id']] = array();
            $ag[$item['good_id']][$item['action_id']] = &$active_actions[$item['action_id']];
        }

        foreach($ag as $good_id => &$actions) {
            foreach ($actions as $id => $a) {
                if ( ! empty($a->show_gifticon)) { // убираем эту иконку из списка
                    if ($a->parent_id) { // есть родительская акция - берем от неё id и name, тип + описание - своё
                        $return[$good_id][$a->parent_id] = ['name' => $actions[$a->parent_id]->name, 'preview' => $a->preview, 'type' => $a->type];
                    } else {
                        if ( ! empty($return[$good_id][$a->id])) { // не перезаписываем если уже поставлено подчиненной акцией
                            $return[$good_id][$a->id] = ['name' => $a->name, 'preview' => $a->preview, 'type' => $a->type];
                        }
                    }
                }
            }
        }

        return $return;
    }
    
    /**
     * @param bool $html
     * @return string
     */
    public function get_link($html = true)
    {
        $link = sprintf('/actions/%d', $this->id);
        return $html ? HTML::anchor($link, $this->name) : $link;
    }
    
    public function get_action_types()
    {
        return DB::select('type', 'id')->distinct('type')->from('z_action')->where('group_id','=', $this->id)->execute()->as_array('id','type');
    }
    
    public function actions()
    {
        return $this->actions->find_all()->as_array();
    }
}