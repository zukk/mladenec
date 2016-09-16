<?php
// aкции
class Model_Action extends ORM
{
    use Seo;

    const TYPE_PRICE        = 0;  // Скидка по перечеркиванию
    const TYPE_PRICE_QTY    = 1;  // Скидка от количества на процент
    const TYPE_PRICE_QTY_AB = 5;  // Скидка от количества товара А на процент для товара Б
    const TYPE_PRICE_SUM    = 2;  // Скидка от суммы на процент
    const TYPE_PRICE_SUM_AB = 6;  // Скидка от суммы товара А на процент для товара Б
    const TYPE_GIFT_SUM     = 3;  // подарок от суммы заказанных участвующих товаров
    const TYPE_GIFT_QTY     = 4;  // подарок к определенному количеству участвующих в акции товаров;

    const PROMO_DISCOUNT    = 1;
    const PROMO_PRESENT     = 2;

    const CACHE_KEY_ACTIVE = 'active_actions';
    protected $_reload_on_wakeup = FALSE;

    public $pq = 0; // число полученных подарков в акции
    public $good_ids = []; // [gid => gid] id товаров, попавших в акцию (для by_goods)
    public $actiontag_id = []; //
    public $action_id = ''; //

    protected $_table_name = 'z_action';

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'active' => '', 'vitrina_active'=>'all', 'allowed' => 0,
        'show' => '',                   // Опубликовать
        'show_goods' => '',             // Отображать товары в акции
        'vitrina_show'=>'all', 'each' => '',
        'total' => '', 'from' => '', 'to' => '',
        'type'                  => '',  // Тип акции
        'preview' => '', 'text' => '',
        'banner'                => '',  // URL файла плашки
        'cart_icon'             => '',  // иконка, появляющаяся в накопительных акциях внизу в корзине
        'cart_icon_text'        => '',  // префикс текста к иконке, появляющийся в накопительных акциях внизу в корзине
        'incoming_link'=>'','link_comment'=>'',
        'quantity'              => '',  // Количество
        'sum'                   => '',  // Сумма
        'new_user'              => 0,   // Условия акции применяются только к новым пользователям.
        'main'                  => '',  // Отображать на главной
        'show_wow'              => 0,   // Отображать в WOW акциях
        'show_actions'          => 0,   // Отображать в списке акций
        'show_gifticon'         => 0,   // Отображать значок подарка у товара
        'parent_id'             => '',
        'order'                 => '',
        'visible_goods'         => '',
        'count_from'            => '', // Считать от
        'count_to'              => '', // Считать по
        'presents_instock'      => 0,  // Наличие подарков
        'require_all_presents'  => 0,   // Чтобы акция включилась - в наличии д.б. все подарки.
        'per_day'               => 0, // ограничение на число срабатываний акции в день
        'sales_notes'           => '',  // фраза для товаров акции в sales_notes в YML
    );

    protected $_has_many = array(
        'goods' => array(
            'model' => 'good',
            'through'    => 'z_action_good',
            'foreign_key'   => 'action_id',
            'far_key'   => 'good_id',
        ),
        'goods_b' => array(
            'model' => 'good',
            'through'    => 'z_action_good_b',
            'foreign_key'   => 'action_id',
            'far_key'   => 'good_id',
        ),
        'presents' => array(
            'model'         => 'good',
            'through'       => 'z_action_present',
            'foreign_key'   => 'action_id',
            'far_key'       => 'good_id'
        ),
        'actiontag' => array(
            'model'         => 'actiontag',
            'through'       => 'z_actiontag_ids',
            'foreign_key'   => 'action_id',
            'far_key'       => 'actiontag_id'
        )
    );

    /**
     * @param bool $html
     * @return string
     */
    public function get_link($html = true)
    {
        $link = sprintf('/actions/%d', $this->id);
        return $html ? HTML::anchor($link, $this->name) : $link;
    }

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return ['active', 'allowed', 'show', 'show_goods', 'each', 'new_user', 'total', 'main', 'show_wow', 'incoming_link', 'show_actions', 'require_all_presents'];
    }

    /**
     * Является ли акция подарочной?
     *
     * @return boolean
     */
    public function is_gift_type()
    {
        return in_array($this->type, array(
            self::TYPE_GIFT_SUM,
            self::TYPE_GIFT_QTY
        ));
    }
    /**
     * Является ли акция скидочной?
     *
     * @return boolean
     */
    public function is_price_type()
    {
        return in_array($this->type , array(
            self::TYPE_PRICE,
            self::TYPE_PRICE_QTY,
            self::TYPE_PRICE_QTY_AB,
            self::TYPE_PRICE_SUM,
            self::TYPE_PRICE_SUM_AB
        ));
    }
    /**
     * Является ли акция АБ-типа?
     *
     * @return boolean
     */
    public function is_ab_type()
    {
        return in_array($this->type , array(
            self::TYPE_PRICE_QTY_AB,
            self::TYPE_PRICE_SUM_AB
        ));
    }

    /**
     * Гугл переводчик говорит, что "накопительная" = "funded"
     * @return bool
     */
    public function is_funded()
    {

        return ( ! empty($this->count_from)) || ( ! empty($this->count_to));
    }

    /**
     * @param int $vitrina
     * @param int $metatype 0 is "all types"
     * @return string
     */
    public static function get_cache_key($vitrina, $metatype, $funded)
    {
        return is_null($metatype) ? NULL : self::CACHE_KEY_ACTIVE . $vitrina . $metatype . $funded;
    }

    /**
     * получить возможные типы акций
     * @static
     * @return array
     */
    public static function types()
    {
        return array(
            self::TYPE_PRICE        => 'Скидка по перечеркиванию',
            self::TYPE_PRICE_QTY    => 'Скидка от количества на процент',
            self::TYPE_PRICE_QTY_AB => 'Скидка от количества товара А на процент для товара Б',
            self::TYPE_PRICE_SUM    => 'Скидка от суммы на процент',
            self::TYPE_PRICE_SUM_AB => 'Скидка от суммы товара А на процент для товара Б',
            self::TYPE_GIFT_SUM     => 'Подарок от суммы заказанных участвующих товаров',
            self::TYPE_GIFT_QTY     => 'Подарок к определенному количеству участвующих в акции товаров',
        );
    }

    /**
     * получить возможные типы акций
     * @static
     * @return array
     */
    public function type_name()
    {
        $types = $this->types();
        return empty($this->type)?$types[0]:$types[$this->type];
    }

    public function is_expired()
    {
        return ( ! empty($this->to) ) AND ($this->to < date('Y-m-d G:i:00'));
    }

    public function is_begun()
    {
        return (empty($this->from)) || ($this->from < date('Y-m-d G:i:00'));
    }

    /**
     * Получить массив подарков [val => [id1,id2,id3],val2 => [id4,id5]]
     *
     * @param bool $reverse
     * @return array
     */
    public function get_present_ids($reverse = FALSE, $instock = TRUE)
    {
        $q = DB::select('good_id','val')
            ->from('z_action_present')
            ->where('z_action_present.action_id', '=', $this->id)
            ->order_by('z_action_present.val', $reverse? 'DESC': 'ASC');

        if ($instock)
        {
            $q->join('z_good')
                ->on('z_action_present.good_id','=','z_good.id')
                ->where('z_good.qty','>','0');
        }

        $presents = $q->execute()->as_array();

        $return = [];
        foreach($presents as $p) { $return[$p['val']][] = $p['good_id']; }
        return $return;
    }

    /**
     * Есть ли в наличии подарки
     *
     * @return boolean
     */
    public function is_presents_instock()
    {
        return $this->presents_instock ? TRUE : FALSE;
    }

    public function is_activatable()
    {
        $activatable = FALSE;

        if ( ! $this->loaded()) throw new Exception ('Unable to calculate is action active for not loaded action');

        if (
            $this->allowed
            AND $this->is_begun()
            AND ! $this->is_expired()
            AND ( $this->visible_goods OR $this->total )
            AND ( ! $this->is_gift_type() OR $this->is_presents_instock() )

        )
        {
            $activatable = TRUE;
        }

        return $activatable;
    }

    /**
     * Включает и выключает акции - используется по хрону и при сохранении в админке
     *
     * @param Model_User $current_user Объект текущего пользователя
     */
    public static function activator($current_user)
    {
        $drop_cache_types = [0 => 0];

        $reports = []; // Отчеты об изменениях
        $activate_reports = []; // Отчеты об изменениях - включить

        $current_user_name = '#' . $current_user->id
            . ' (' . $current_user->name . ' '
            . $current_user->second_name . ' '
            . $current_user->last_name   . ')';

        /* *** Activation: *** */
        $actions_to_enable = ORM::factory('action')
            ->where('active', '=', 0)    // пока не активна
            ->where('allowed', '=', 1)   // разрешена!!!

            ->where_open()
                ->where('from', 'IS', NULL)          // срок начала НЕ установлен
                ->or_where_open()                    // - или -
                    ->where('from', 'IS NOT', NULL)  // срок начала установлен
                    ->where('from', '<=', date('Y-m-d G:i:00')) // ... и пора начинаться
                ->where_close()
            ->where_close()

            ->where_open()
                ->where('to', 'IS', NULL)            // срок окончания не установлен
                ->or_where_open()                    // - или -
                    ->where('to', 'IS NOT', NULL)    // срок окончания установлен
                    ->where('to', '>=', date('Y-m-d G:i:00')) // ... и еще НЕ истек
                ->where_close()
            ->where_close()

            ->where_open()
                ->where('total', '=', 1)     // и участвуют все товары
                ->or_where('visible_goods', '>', 0) // или не все но есть хотя бы один
            ->where_close()

            ->where_open()
                ->where('presents_instock', '=', 1) // Есть в наличии подарки
                ->or_where('type', 'IN', [self::TYPE_PRICE, self::TYPE_PRICE_SUM,self::TYPE_PRICE_SUM_AB, self::TYPE_PRICE_QTY,self::TYPE_PRICE_QTY_AB]) // Подарки ни к чему, акция по цене
            ->where_close()

            ->find_all()
            ->as_array('id');

        foreach($actions_to_enable as $ae) {
            if ( ! $ae->active) {
                $ae->active = 1;
                $ae->save();
                $activate_reports[$ae->id] = array(
                    'action'    => $ae,
                    'event'     => 'on',
                    'msg'       => $current_user_name . ': Включил акцию.'
                );
                $drop_cache_types[$ae->type] = $ae->type;

                Log::instance()->add(Log::INFO, 'Action #' . $ae->id . ' activated');
            }
        }

        /* *** Deactivation: *** */
        $actions_to_disable = ORM::factory('action')
            ->where('active',  '=', 1) // Пока работает
            ->where_open()
                ->where('allowed', '=', 0) // Запрещена админом
                ->or_where('from', '>=', date('Y-m-d G:i:00')) // Не началась
                ->or_where_open()
                    ->where('to', 'IS NOT', NULL) // Срок окончания установлен
                    ->where('to', '<', date('Y-m-d G:i:00')) // ... и истек
                ->or_where_close()
                ->or_where_open()
                    ->where('visible_goods', '=', 0) // нет товаров
                    ->where('total', '=', 0) // и учавствуют не все
                ->or_where_close()
                ->or_where_open()
                    ->where('type', 'IN', [self::TYPE_GIFT_SUM, self::TYPE_GIFT_QTY])   // должны быть подарки
                    ->where('presents_instock', '=', 0)     // ...но нет в наличии
                ->where_close()
            ->where_close()
            ->find_all()
            ->as_array('id');

        foreach($actions_to_disable as $ad) {
            $ad->active = 0;
            $ad->save();
            $drop_cache_types[$ad->type] = $ad->type;

            if( ! empty($activate_reports[$ad->id])) unset($activate_reports[$ad->id]);

            $report = array(
                'event'     => 'off',
                'action'    => $ad
            );
            if ( ! $ad->is_begun()) {
                Log::instance()->add(Log::INFO, 'Action #' . $ad->id . ' not begin, dectivated by robot');
                $report['msg']  = $current_user_name . ': Указано включить позже.';

            } elseif ($ad->is_expired()) {
                Log::instance()->add(Log::INFO, 'Action #' . $ad->id . ' expired, dectivated by robot');
                $report['msg']  = $current_user_name . ': Истек срок действия.';

            } elseif ( ! $ad->visible_goods) {
                Log::instance()->add(Log::INFO, 'Action #' . $ad->id . ' has no visible goods, dectivated by robot');
                $report['msg']  = $current_user_name . ': Закончились участвующие в акции товары.';

            } elseif ($ad->is_gift_type() && ! $ad->is_presents_instock()) {
                Log::instance()->add(Log::INFO, 'Action #' . $ad->id . ' has no presents, dectivated by robot');
                $report['msg']  = $current_user_name. ': Закончились подарки.';

            } elseif ( ! $ad->allowed) {
                Log::instance()->add(Log::INFO, 'Action #' . $ad->id . ' not allowed, dectivated by robot');
                $report['msg']  = $current_user_name. ': Отключил акцию.';
            }
            $reports[] = $report;
        }

        $reports = array_merge($reports, $activate_reports);

        if ( ! empty($reports)) { // есть изменения в акциях
            self::send_reports($reports);  // сообщить об изменениях
            Cache::instance()->delete(self::CACHE_KEY_ACTIVE); // почистить кеш списка акций
        }
    }

    private static function send_reports($reports,$mail_subject = 'Изменения в акциях')
    {
        $to = Conf::instance()->mail_action;

        if (empty($to)) return FALSE;

        foreach ($reports as $r)  // Запишем изменения в акциях в историю
        {
            if (empty($r['action'])) return FALSE;

            Model_History::log('action',
                $r['action']->id,
                $r['event'],
                array('active'=>$r['action']->active,'allowed'=>$r['action']->allowed,'incoming_link'=>$r['action']->incoming_link)
            );
        }
        Log::instance()->add(Log::INFO, 'Sending ' . count($reports) . ' reports of actions changes');

        return Mail::htmlsend('action_events', array('reports'=>$reports), $to, $mail_subject);
    }

    public function count_visible_goods()
    {
        return DB::select(DB::expr('count(`id`) as `cnt`'))
            ->from('z_good')
            ->join('z_action_good')->on('z_good.id', '=', 'z_action_good.good_id')
            ->where('z_action_good.action_id', '=', $this->id)
            ->where('z_good.show','=',1)
            ->where('z_good.qty','!=',0)
            ->execute()
            ->get('cnt');
    }

    /**
     * Товары должны быть сохранены ДО вызова этой функции
     *
     * @param array $action_ids
     * @return array [action_id => presents_instock]
     */
    public static function check_presents($action_ids)
    {
        $presents_stock     = []; // [action_id=> instock]
        $action_presents    = []; // [action_id=> [good_id=>instock]]
        $actions            = [];

        if (empty($action_ids)) return [];

        Log::instance()->add(Log::INFO, 'Counting presents for actions: ' . implode(',', $action_ids));

        $presents_q = DB::select('z_good.id', 'z_action_present.action_id', 'z_good.active', 'z_good.qty', 'z_action.require_all_presents', 'z_action.presents_instock')
            ->from('z_action_present')
            ->join('z_good')    ->on('z_good.id',   '=', 'z_action_present.good_id')
            ->join('z_action')  ->on('z_action.id', '=', 'z_action_present.action_id')
            ->where('z_action_present.action_id', 'IN', $action_ids)
            ->where('z_action.allowed', '=', 1);

        $presents = $presents_q->execute()->as_array();

        foreach ($presents as $p)
        {
            $action_presents[ $p['action_id'] ][ $p['id'] ] = ($p['qty'] != 0 AND $p['active'] != 0); // надо посчитать подарки
            $actions[ $p['action_id'] ]['require_all_presents']
                = $p['require_all_presents'];
            $actions[ $p['action_id'] ]['presents_instock'] = $p['presents_instock'];
        }

        // Проверяем, чтобы у акций которые требуют всех подарков были _ВСЕ_ подарки
        foreach ($action_presents as $action_id => $pr_arr)
        {
            if ($actions[$p['action_id']]['require_all_presents'])
            {
                if (FALSE === array_search(FALSE, $pr_arr)) // Все в наличии, 
                {
                    if ( 0 == $actions[$p['action_id']]['presents_instock']) $presents_stock[$p['action_id']] = '1'; //а раньше не были
                }
                else // не в наличии
                {
                    if ( 1 == $actions[$p['action_id']]['presents_instock']) $presents_stock[$p['action_id']] = '0'; // а раньше были
                }
            }
            else
            {
                if (FALSE === array_search(TRUE, $pr_arr)) // Хоть один в наличии
                {
                    if ( 1 == $actions[$p['action_id']]['presents_instock']) $presents_stock[$p['action_id']] = '0'; // а ни одного не было
                }
                else // ни одного в наличии
                {
                    if ( 0 == $actions[$p['action_id']]['presents_instock']) $presents_stock[$p['action_id']] = '1'; //а были
                }
            }
        }

        return $presents_stock;
    }

    /**
     * Товары должны быть сохранены ДО вызова этой функции
     *
     * @param array $good_ids
     * @return boolean
     */
    public static function check_by_goods($good_ids)
    {
        $reports = [];

        $gift_action_ids = DB::select('action_id')->from('z_action_present')
            ->where('good_id','IN', $good_ids)
            ->execute()->as_array('action_id','action_id');

        $action_regular_ids = DB::select('action_id')->from('z_action_good')
            ->where('good_id','IN', $good_ids)
            ->execute()->as_array('action_id','action_id');

        $ab_action_ids = DB::select('action_id')
            ->from('z_action_good_b')
            ->where('good_id','IN', $good_ids)
            ->execute()->as_array('action_id','action_id');

        $presents_stock = self::check_presents($gift_action_ids);

        $action_ids = array_merge($action_regular_ids, $ab_action_ids, array_keys($presents_stock));

        if (empty($action_ids)) return; // Не найдено акций - нечего делать.

        Log::instance()->add(Log::INFO, 'When checking by goods # ' . implode(', ' ,$good_ids) . ' - checking actions: ' . implode(', ' ,$action_ids) . '.');

        $actions = ORM::factory('action')
            ->where('id', 'IN', $action_ids)
            ->where('allowed', '=', 1)
            ->order_by('parent_id', 'DESC')
            ->find_all()->as_array('id');

        if (empty($actions)) return; // Нет разрешенных акций

        // Пересчитаем видимые товары
        $visible_goods = DB::select(array('z_action_good.action_id','aid'), DB::expr('count(`z_action_good`.`good_id`) as `cnt`'))
            ->from('z_action_good')
            ->join('z_good')->on('z_good.id', '=', 'z_action_good.good_id')
            ->where('z_good.show', '=', 1)
            ->where('z_good.qty', '!=', 0)
            ->group_by('aid')
            ->execute()
            ->as_array('aid', 'cnt');

        foreach($actions as &$act)
        {
            if (isset($presents_stock[$act->id]) ) // подарочная акция!
            {
                Log::instance()->add(Log::INFO, 'In action  ' . $act->id . ' presents changed');

                if ($presents_stock[$act->id])
                {
                    $act->presents_instock = '1';
                    $reports[] = [
                        'event' => 'presents_instock',
                        'msg' => 'Подарки появились на складе ',
                        'action' => $act
                    ];
                }
                else
                {
                    $act->presents_instock = '0';
                    $reports[] = [
                        'event' => 'presents_off',
                        'msg' => 'Закончились подарки ',
                        'action' => $act
                    ];
                }
            }

            $act->visible_goods = empty($visible_goods[$act->id]) ? 0 : $visible_goods[$act->id];

            $act->save();
        }

        if ( ! empty($reports)) self::send_reports($reports, 'Изменения в подарках в акциях');
    }

    public function count_presents_instock()
    {
        if ($this->is_price_type()) return 0; // не подарочная акция - всегда нет

        $sub_actions = ORM::factory('action')
            ->where('parent_id','=',$this->id)
            ->where('type', 'IN', [self::TYPE_GIFT_SUM, self::TYPE_GIFT_QTY])
            ->find_all()->as_array('id');

        if (count($sub_actions)) { // Есть подчиненные
            $sub_presents = 0;
            foreach ($sub_actions as $sa) { if ($sa->is_presents_instock()) $sub_presents++; }

            if (0 == $sub_presents) { // Ни у одной из подчиненных нет подарков на складе
                $this->presents_instock = 0;
                return $this->presents_instock;
            }
        }

        $presents = $this->presents->find_all()->as_array('id', 'qty');

        if (empty($presents)) return 1; // подарки если не прикреплены - всегда есть
        $instock = count(array_filter($presents)); // сколько подарков есть на складе

        $instock_flag = $instock > 0;

        if ($this->require_all_presents AND $instock < count($presents)) {
            $instock_flag = FALSE;
        }

        if ($instock_flag) {
            if ($this->presents_instock == 0) {
                $this->presents_instock = 1;
                self::send_reports(
                    [['event' => 'presents_instock', 'msg' => 'Подарки появились на складе ','action' => $this]],
                    'Акция #' . $this->id . ', подарки появились на складе'
                );
            }

        } else {
            if ($this->presents_instock > 0) {
                $this->presents_instock = 0;
                self::send_reports(
                    [['event' => 'presents_off', 'msg' => 'Закончились подарки ', 'action' => $this]],
                    'Акция #' . $this->id . ', закончились подарки'
                );
            }
        }

        return $this->presents_instock;
    }

    /**
     * получить ид товаров акции
     * @param bool $show только те, которые отображаются на сайте
     * @param bool $active только те, что есть в наличии и цена больше 0 и активные
     */
    public function good_idz($show = FALSE)
    {
        $query = DB::select('good_id')->from(array('z_action_good', 'ag'));
        $query->where('ag.action_id', '=', $this->id);

        if ($show) {
            $query->join(array('z_good', 'g'))
                ->on('g.id', '=' , 'ag.good_id')
                ->where('g.qty', '!=', 0)
                ->where('g.show', '=', 1);
        }
        return $query->execute()->as_array('good_id', 'good_id');
    }

    /**
     * получить ид Б - товаров акции
     */
    public function good_b_idz()
    {
        return DB::select('good_id')
            ->from('z_action_good_b')
            ->where('action_id', '=', $this->id)
            ->execute()
            ->as_array('good_id', 'good_id');
    }

    /**
     * Update подарков акции в админке
     */
    public function up_presents($new_presents)
    {
        $old_presents = DB::select('id')
            ->from('z_action_present')
            ->where('action_id', '=', $this->id)
            ->execute()
            ->as_array('id','id');

        $del_presents = array_diff($old_presents, array_keys($new_presents));

        if ( ! empty($del_presents)) { // Удаляем расхождения
            DB::delete('z_action_present')
                ->where('action_id', '=', $this->id)
                ->where('id', 'IN', $del_presents)
                ->execute();
        }
    }

    /**
     * Поставить товары для которых применится условие акции
     * @param $idz
     * @return bool
     */
    public function set_goods_b($idz)
    {
        /** Защита от ситуации когда случайно добавили дубли */
        if (is_array($idz)) $idz = array_unique($idz);
        else                return FALSE;

        DB::delete('z_action_good_b')->where('action_id', '=', $this->id)->execute();

        if ( ! empty($idz))
        {
            $ins = DB::insert('z_action_good_b')->columns(array('action_id','good_id'));

            foreach ($idz as $id)
            {
                $ins->values(array(
                    'action_id' => $this->id,
                    'good_id' => $id
                ));
            }
            $ins->execute();
        }
    }

    /**
     * Поставить товары для акции
     * @param $idz
     * @return bool
     */
    public function set_goods($idz)
    {
        /** Защита от ситуации когда случайно добавили дубли */
        if (is_array($idz)) {
            $idz = array_unique($idz);
        } else {
            return FALSE;
        }

        DB::delete('z_action_good')->where('action_id', '=', $this->id)->execute();

        if ( ! empty($idz)) {
            $ins = DB::insert('z_action_good')->columns(array('action_id','good_id'));
            foreach ($idz as $id) {
                $ins->values(array(
                    'action_id' => $this->id,
                    'good_id' => $id
                ));
            }
            $ins->execute();
        }
    }

    /**
     * Сохранение привязанных товаров
     */
    public function admin_save()
    {
        $goods      = Request::current()->post('goods');
        $misc       = Request::current()->post('misc');
        $errors     = [];
        $messages   = [];
        if ( ! is_array($goods)) { $goods = []; } // когда нет прикрепленных товаров

        $show_good = Request::current()->post('goods_show'); // Прикрепленные отображаемые
        if ( ! is_array($show_good)) $show_good = [];  // Нет прикрепленных отображаемых

        $old_good_idz = $this->good_idz(); // Были прикреплены
        $this->set_goods($goods);

        $this->save(); // Чтобы пересчитались прикрепленные товары

        $goods_new    = array_diff($old_good_idz, $goods); // Добавлены товары
        $goods_delete = array_diff($goods, $old_good_idz); // Удалены товары
        if ( ! empty($goods_new))    { Model_History::log('action', $this->id, 'goods_add', $goods_new); }
        if ( ! empty($goods_delete)) { Model_History::log('action', $this->id, 'goods_del', $goods_delete); }

        if (in_array($this->type, array(self::TYPE_PRICE_QTY_AB, self::TYPE_PRICE_SUM_AB)))
        {
            $old_good_b_idz = $this->good_b_idz(); // Были прикреплены
            $this->set_goods_b(Request::current()->post('goods_b'));

            $goods_new    = array_diff($old_good_b_idz, $goods); // Добавлены товары
            $goods_delete = array_diff($goods, $old_good_b_idz); // Удалены товары
            if ( ! empty($goods_new))    Model_History::log('action', $this->id, 'goods_b_add', $goods_new);
            if ( ! empty($goods_delete)) Model_History::log('action', $this->id, 'goods_b_del', $goods_delete);
        }
        if ($this->is_gift_type())
        {
            if ( ! empty($misc['presents'])) {
                $new_presents = $misc['presents'];
            } else {
                $new_presents = [];
            }
            $this->up_presents($new_presents);
            if ( ! empty($misc['present_new']['good_id']) AND isset($misc['present_new']['val'])) {
                $present_id = $misc['present_new']['good_id'];
                $present = ORM::factory('good', $misc['present_new']['good_id']);
                if ( ! $present->loaded()) {
                    $errors = 'Невозможно добавить подарок #' . $present_id;
                }
                if (DB::insert('z_action_present')
                    ->columns(array('good_id','action_id','val','warn_on_qty'))
                    ->values(array(
                        'good_id'     => $present_id,
                        'action_id'   => $this->id,
                        'val'         => $misc['present_new']['val']         ? $misc['present_new']['val']         : 0,
                        'warn_on_qty' => $misc['present_new']['warn_on_qty'] ? $misc['present_new']['warn_on_qty'] : 10
                    ))->execute()) {
                    $messages[] = 'Подарок # ' . $present_id . ' добавлен';
                }
            }

            $cnt = $this->count_presents_instock(); // пересчитаем количество подарков на складе
            Log::instance()->add(Log::INFO, 'Action presents counter: ' .$cnt);
            if ($this->changed('presents_instock')) $this->save();

            if ( ! empty($misc['warn_on_qtys'])) { //
                foreach($misc['warn_on_qtys'] as $woq_gid => $woq_qty) {
                    DB::update('z_action_present')
                        ->set(['warn_on_qty'=>$woq_qty])
                        ->where('action_id','=',$this->pk())
                        ->where('good_id','=',$woq_gid)
                        ->execute();
                }
            }
        }

        self::activator(Model_User::current()); // Перерасчет активности акций по условиям
        return ['errors' => $errors, 'messages' => $messages];
    }

    public function save(Validation $validation = NULL)
    {
        $current_user = Model_User::current();
        if ( ! $current_user) $current_user_name = '#Robot';
        else $current_user_name = '#Robot' . $current_user->id . $current_user->name;

        if ($this->loaded()) {
            if ( ! $this->total) { // участвуют не все товары -
                $this->visible_goods = $this->count_visible_goods();

                $mail_subject = 'Акция #' . $this->id;
                $reports = [];

                if ($this->visible_goods == 0) { // Кончились товары доступные к отображению
                    $mail_subject .= ', нет товаров в акции';
                    $reports[] = ['event' => 'visible_goods_off', 'msg' => 'Кончились товары в акции ' , 'action' => $this];
                }
                $old_active = $this->active;
                if ($this->is_activatable()) {
                    $this->active = 1;
                    Log::instance()->add(Log::INFO, 'Action enabled');
                } else {
                    $this->active = 0;
                    Log::instance()->add(Log::INFO, 'Action disabled');
                }

                if ($this->changed('allowed')) {
                    if (0 == $this->allowed) {
                        $mail_subject .= ', ОТКЛючена';
                        $reports[] = ['event' => 'disallow', 'msg' => 'Отключил администратор ' . $current_user_name, 'action' => $this];
                    } else {
                        if (1 == $this->active) {
                            $mail_subject .= ', ВКЛючена';
                            $reports[] = ['event' => 'on', 'msg' => 'Включил администратор ' . $current_user_name, 'action' => $this];
                        } else {
                            $mail_subject .= ', ожидает ВКЛючения';
                            $reports[] = ['event' => 'allow', 'msg' => 'Разрешил к запуску администратор ' . $current_user_name .', ожидает включения', 'action' => $this];
                        }
                    }
                }

                /* Если сняли галочку "входящая ссылка" */
                if ( ! $this->incoming_link AND $this->changed('incoming_link')) {
                    $mail_subject .= ', снят флаг входящей ссылки';
                    $reports[] = ['event' => 'incoming_link_flag_off', 'msg' => 'Флаг входящей ссылки снят администратором ' . $current_user_name , 'action' => $this];
                    Model_History::log('action', $this->id, 'incoming_link_flag_off', ['incoming_link' => 0]);
                }

                if ( ! empty($reports) OR $old_active != $this->active) {
                    if ($this->active) {
                        $mail_subject .= ', ВКЛючена';
                        $reports[] = array('event'=>'on', 'msg' => 'ВКЛючена', 'action'=>$this);
                    } else {
                        $mail_subject .= ', ОТКЛючена';
                        $reports[] = array('event'=>'off', 'msg' => 'ОТКЛючена', 'action'=>$this);
                    }
                    self::send_reports($reports, $mail_subject);
                }
            }
        } else { // нельзя включать новые созданные акции
            $this->active = 0;
        }

        parent::save($validation);

        $actiontag_id = Request::current()->post('actiontag_id');

        if(isset($actiontag_id) && !empty($actiontag_id)){

            DB::delete('z_actiontag_ids')
                ->where('action_id', '=', $this->id)
                ->execute();

            $ins = DB::insert('z_actiontag_ids')->columns(array('action_id','actiontag_id'));

            foreach ($actiontag_id as $id) {
                $ins->values(array(
                    'action_id' => $this->id,
                    'actiontag_id' => $id
                ));
            }
            $ins->execute();
        }
        /*
        if ($this->parent_id > 0 AND $this->is_gift_type() AND $this->presents_instock = 0) {
            $parent_action = ORM::factory('action',$this->parent_id)->find();
            if ($parent_action->loaded()) {
                $old_parent_pi = $parent_action->presents_instock;
                $parent_action->count_presents_instock();
                if ($parent_action->presents_instock != $old_parent_pi) {
                    $parent_action->save();
                }
            }
        }
        */
        /* Очищаем кеш после изменения акции, т.к. могло измениться количество прикрепленных товаров */
        Cache::instance()->delete(self::CACHE_KEY_ACTIVE);
        Cache::instance()->delete($this->visible_goods_ids_cache_key());
        Cache::instance()->delete($this->visible_goods_ids_cache_key(TRUE));
        Cache::instance()->delete($this->visible_goods_ids_cache_key(FALSE));
    }

    /**
     * @param int $funded
     * @param int $vitrina
     * @return Model_Action[]
     */
    public static function get_active($funded = FALSE)
    {
        $cache_key = self::CACHE_KEY_ACTIVE;
        $active_actions_cache = Cache::instance()->get($cache_key);

        if (empty($active_actions_cache)) {

            $active_actions = ORM::factory('action')
                ->where('active', '=', 1)
                ->find_all()
                ->as_array('id');

            Cache::instance()->set($cache_key, serialize($active_actions));

        } else {

            $active_actions = unserialize($active_actions_cache);
        }

        $return = [];
        if ( ! empty($funded)) { // надо отсеять только накопительные
            if ($active_actions) {
                foreach($active_actions as $id => $a) {
                    if ($a->count_from) $return[$id] = $a;
                }
            }
        } else {
            $return = $active_actions;
        }

        return $return;
    }

    /**
     *
     * @param array $incart - товары, лежащие в корзине (в массиве $this->good_ids)
     * @return Model_Good[]
     */
    public function get_b_goods($incart = FALSE)
    {
        if ($incart AND empty($this->good_ids)) return []; // Если нет товаров попавших в акцию - грузить нечего

        $b_goods = ORM::factory('good')
            ->join('z_action_good_b')
            ->on('z_action_good_b.good_id', '=', 'good.id')
            ->where('z_action_good_b.action_id', '=', $this->id)
            ->where('active', '=', 1);

        if($incart) $b_goods->where('good.id', 'IN', $this->good_ids);

        return $b_goods->find_all()->as_array('id');
    }

    protected function set_good_ids($ids)
    {
        $this->good_ids = [];

        $this->add_good_ids($ids);

        return $this;
    }

    public function add_good_ids($gid)
    {
        if ( ! is_array($gid)) $gid = array($gid);

        foreach($gid as $id)
        {
            $this->good_ids[$id] = $id;
        }

        return $this;
    }

    /**
     * Получить список активных акций, относящихся к товарам
     * @param $good_ids
     * @return Model_Action[] массив акций с заполненным $good_ids для каждой акции
     */
    public static function by_goods($good_ids)
    {
        if (empty($good_ids)) return [];

        $return = $not_total_ids = $ab_ids = [];
        $active_actions = self::get_active();
        if (empty($active_actions)) return [];

        foreach($active_actions as $action)
        {
            if ($action->total)  // в этой акции участвуют все товары - добавляем все
            {
                $return[$action->id] = $action->add_good_ids($good_ids);
            }
            elseif ($action->count_from)  // накопительная - тоже включаем всегда в список, мог накопить раньше, но и ищем товары
            {
                $return[$action->id] = $action;
                $not_total_ids[] = $action->id;
            }
            elseif (FALSE !== in_array($action->type, [self::TYPE_PRICE_QTY_AB,  self::TYPE_PRICE_SUM_AB]))
            {
                $ab_ids[]        = $action->id; // Чтобы потом проверить вхождение товаров Б
                $not_total_ids[] = $action->id; // Но и товары А надо проверить
            }
            else
            {
                $not_total_ids[] = $action->id;
            }
        }

        if (empty($not_total_ids)) return $return; // Есть только акции в которых участвуют все товары

        $action_goods = DB::select('good_id', 'action_id')
            ->from('z_action_good')
            ->where('action_id', 'IN', $not_total_ids)
            ->where('good_id', 'IN', $good_ids)
            ->execute()
            ->as_array();

        if (empty($action_goods)) return $return; // Нет совпадающих товаров

        foreach($action_goods as $ag)
        {
            $action = $active_actions[$ag['action_id']];

            $return[$action->id] = $action->add_good_ids($ag['good_id']);
        }

        // Проверяем чтобы у АБ акций - в корзине были и А и Б товары
        foreach ($ab_ids as $ab) {
            if ( ! empty($return[$ab]))
            {
                $b_good_id = DB::select('good_id')
                    ->from('z_action_good_b')
                    ->where('action_id','=',$ab)
                    ->where('good_id', 'IN', $good_ids)
                    ->limit(1)   // Нам достаточно чтобы хоть 1 входил
                    ->execute()
                    ->get('good_id');

                if (empty($b_good_id)) unset($return[$ab]); // Убираем те АБ, у которых нет товаров Б
            }
        }

        return $return;
    }

    /**
     * Получить список активных акций, относящихся к товарам, для показа иконок акций у товаров
     * сюда не попадают акции, в которых учавствуют все товары
     * @param $good_ids
     * @return array массив ид товара => акции в которых он учавствует
     */
    public static function for_icons($good_ids)
    {
        $return = $ag = [];

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
            if (empty($ag[$item['good_id']])) $ag[$item['good_id']] = [];
            $ag[$item['good_id']][$item['action_id']] = &$active_actions[$item['action_id']];
        }

        foreach($ag as $good_id => &$actions) {
            foreach ($actions as $id => $a) {
                if (($a->show || ($a->parent_id && ! empty($actions[$a->parent_id]->show))) && ! empty($a->show_gifticon)) {
                    if ($a->parent_id) { // есть родительская акция - берем от неё id и name, тип + описание - своё
                        $return[$good_id][$a->parent_id] = [
                            'name' => $actions[$a->parent_id]->name,
                            'preview' => $a->preview,
                            'type' => $a->is_gift_type() ? 'gift' : 'sale',
                            'discount' => $a->type == self::TYPE_PRICE
                        ];
                    } else {
                        if (empty($return[$good_id][$a->id])) { // не перезаписываем если уже поставлено подчиненной акцией
                            $return[$good_id][$a->id] = [
                                'name' => $a->name,
                                'preview' => $a->preview,
                                'type' => $a->is_gift_type() ? 'gift' : 'sale',
                                'discount' => $a->type == self::TYPE_PRICE
                            ];
                        }
                    }
                }
            }
        }
        return $return;
    }

    protected function visible_goods_ids_cache_key($active = NULL)
    {
        $key = 'action_' . $this->id;
        if (is_null($active)) $key .= '_an';
        else $key .= ($key ? '_a1' : '_a0');

        return $key;
    }

    /**
     * проставляет юзеру номер заказа с которого считать сумму по акции
     * @param $user_id
     * @param $order_id
     */
    public function set_count_from($user_id, $order_id)
    {
        DB::query(Database::INSERT,
            'INSERT INTO z_action_user (action_id, user_id, from_order)'
            .' VALUES('.$this->id.', '.$user_id.', '.$order_id.')'
            .' ON DUPLICATE KEY UPDATE from_order = VALUES(from_order)'
        )->execute();
    }

    /**
     * Возвращает true/false в зависимости от того, можно ли давать подарок с ограничением на количество в день
     * @return bool
     */
    public function check_per_day()
    {
        if (empty($this->per_day)) return TRUE;
        if ( ! Model_User::logged()) return FALSE;

        $user_id = Model_User::current()->id;

        // сначала проверим что этот человек ещё не учавствовал в этой акции
        $already = DB::select('o.id')
            ->from(['z_order_good', 'og'])
            ->join(['z_order', 'o'])
            ->on('o.id', '=', 'og.order_id')
            ->where('o.user_id', '=', $user_id)
            ->where('og.action_id', '=', $this->id)
            ->where('o.status', '!=', 'X')
            ->execute()
            ->get('id');

        if ($already) return FALSE;

        $today = DB::select(DB::expr('COUNT(o.id) as cnt')) // сколько сегодня уже поучавствовало?
        ->from(['z_order', 'o'])
            ->join(['z_order_good', 'og'])
            ->on('o.id', '=', 'og.order_id')
            ->where('o.created', '>', date('Y-m-d'))
            ->where('og.action_id', '=', $this->id)
            ->where('o.status', '!=', 'X')
            ->execute()
            ->get('cnt');

        return $today < $this->per_day;
    }

    /**
     * сохранение публичных свойств при сериализации
     * @return string
     */
    public function serialize()
    {
        foreach (array('_primary_key_value', '_object', '_changed', '_loaded', '_saved', '_sorting', '_original_values', 'good_ids') as $var)
        {
            $data[$var] = $this->{$var};
        }
        return serialize($data);
    }

    /**
     * получить строку sales_notes по настройкам акций в которых участвует товар
     *
     */
    public static function sales_notes($gid)
    {
        $return = '';
        foreach(self::by_goods([$gid]) as $action) {
            if ($action->sales_notes > '') {
                $return .= $action->sales_notes.' ';
            }
        }
        if ($return == '') return 'Доставка: выбор удобного для Вас интервала, бесплатно при заказе от 2000р. Оплата: наличные, Visa/Mastercard';
        return $return;
    }

}
