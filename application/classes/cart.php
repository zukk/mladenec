<?php

class Cart {

    const MAX_SIMPLE = 4500; // сумма заказа
    const BLAG_ID = 138549; // идентификатор товара благотворительности

    public $total           = 0;        // общая стоимость товаров в корзине
    public $qty             = 0;        // общее количество товаров в корзине
    public $actions         = [];  // action.id => Model_Action
    public $goods           = [];  // good.id => qty
    public $comments        = [];  // good.id => comment
    public $status_id       = 0;
    public $blago           = 0;        // благотворительность
    public $added           = FALSE;    // флаг - было ли добавление только что - используется при отображении корзины
    public $presents        = [];  // [ $action_id => $good_id, ... ]
    public $present_variants  = [];  // Подарки _на выбор_, $action_id => [$good_id1,$good_id2, ...]
    public $presents_selected = []; // Выбранные подарки [ $action_id => $good_id, ... ]
    public $no_presents     = [];  // ид подарков от которых человек отказался
    public $promo           = [];  // промо подарков - массив, ключ = сумма накопленная, значение = акция
    public $big             = [];  // [$id => $qty] массив id крупногабаритки в заказе
    public $discount        = 0;        // сумма скидки
    public $no_possible     = [];  // товары которых заказано больше чем есть
    public $coupon          = [];  // использовать купон на скидку
	protected $_session_id  = NULL;
    protected $force_recount = TRUE;

    public $total_action = []   // скидочная акция на всё (сумма от => процент скидки)
    ;
    
    /**
     * Конструктор может сразу добавлять
     * @param array $goods
     * @param string $session_id
     */
    function __construct($goods = array(), $session_id = NULL)
    {
        try {
            $cart_s = Session::instance(NULL, $session_id)->get('cart');
            $this->_session_id = $session_id;
        
        } catch (Session_Exception $e) {
            $cart_s = FALSE;
        }
		$this->reset_status();

        if ($cart_s instanceof Cart) {
        
            $this->total = $cart_s->total;;
            $this->goods = $cart_s->goods;
            $this->qty = $cart_s->qty;
            $this->comments = ! empty($cart_s->comments) ? $cart_s->comments : array();
            $this->blago = ! empty($cart_s->blago) ? $cart_s->blago : 0;
            $this->discount = ! empty($cart_s->discount) ? $cart_s->discount : 0;
            $this->presents_selected = ! empty($cart_s->presents_selected) ? $cart_s->presents_selected : array();
            $this->no_presents = ! empty($cart_s->no_presents) ? $cart_s->no_presents : array();
            $this->big = ! empty($cart_s->big) ? $cart_s->big : array();
            $this->no_possible = ! empty($cart_s->no_possible) ? $cart_s->no_possible : array();
            $this->coupon = ! empty($cart_s->coupon) ? $cart_s->coupon : array();
        }
        $this->add($goods); // может быть сразу добавление товаров
    }
        
    /**
     * @return Cart
     */
    static function instance()
    {
        return new self();
    }
        
    /**
     * Sets status_id for cart according to user status
     */
    private function reset_status()
    {
        $this->status_id = 0;
        if ($cur = Model_User::current()) $this->status_id = $cur->status_id;
        return $this->status_id;
    }

    /**
     * Очистить корзину
     * Однако мы запоминаем подакри от которых был отказ
     * @chainable
     * @return Cart
     */
    public function clean()
    {
        $this->save_clear();
        $this->reset_status();

        $this->blago = 0;
        $this->goods = $this->presents = $this->comments = $this->big = $this->no_possible = $this->coupon = array();
        return $this->save();
    }

    /**
     * Добавление товаров в корзину. В конце надо всё пересчитать
     * @param $goods [good_id => qty] 
     * @param bool $warn - c выставлением флага
     * @return Model_Good[]
     */
    public function add($goods, $warn = TRUE)
    {
        if (empty($goods) OR ! is_array($goods)) return $this; // нет товаров на добавление

		$oldg = $this->goods;

		foreach($goods as $good_id => $q) {

            // Добавление благотворительности уже есть в recount

            if ( ! isset($this->goods[$good_id])) $this->goods[$good_id] = 0;

            $this->goods[$good_id] += $q;
        }

		$g = $this->recount();

		if ($warn) {// нужно сообщить о добавлении товара
        	foreach ($g as $i) {
				if (empty($oldg[$i->id]) || $i->qty != $oldg[$i->id]) { // изменилось количество или есть новые товары
					$this->added = TRUE;
					break;
				}
			}
		}

		return $g;
    }

    /**
     * Сохранить в корзине комментарии к товарам
     *
     * @chainable
     * @param array $comments good.id=>comment
     * @return $this
     */
    public function set_comments($comments) {
        if (empty($comments)) return $this;
        $this->comments = $comments + $this->comments; // Новый массив (первый, $comments) имеет больший приоритет
        $this->clear_comments(); // Убираем комментарии к отсутствующим в корзине товарам
        
        return $this;
    }
    
    /**
     * Указать выбранные пользователем подарки
     * 
     * @param array $presents [$action_id => $good_id, ...]
     */
    public function select_presents($presents) {
        
        if ( empty($presents) || ! is_array($presents)) return;
        
        foreach($presents as $action_id => $present_id) {
            $this->presents_selected[$action_id] = $present_id;
            $this->presents[$action_id] = $present_id; 
        }
    }

    /**
     * Получить объекты подарков, возвращает вместе с вариантами, если возможен выбор
     *
     * @param bool $with_variants
     * @param bool $with_rejections
     * @return Model_Good
     */
    public function get_present_goods($with_variants = TRUE, $with_rejections = TRUE)
    {
        $good_ids_to_load = $this->presents;
        if ($with_variants) {
            foreach ($this->present_variants as $sp) {
                foreach($sp as $p) { $good_ids_to_load[] = $p; }
            }
        }
        if ( ! $with_rejections) {
            foreach ($this->no_presents as $np) {
                
            }
        }
        if ( ! empty($good_ids_to_load)) {
            return ORM::factory('good')->where('id', 'IN', $good_ids_to_load)->find_all()->as_array('id');
        }
        return array();
    }

    /**
     * Получить ид подарков, возвращает вместе с вариантами, если возможен выбор
     *
     * @param bool $with_variants
     * @param bool $with_rejections
     * @return Model_Good
     */
    public function get_presents($with_variants = TRUE, $with_rejections = TRUE)
    {
        if ($this->force_recount) 
        {
            $this->recount ();
            $this->presents = $this->check_actions();
        }
        
        if ($with_variants) {
            foreach ($this->presents_selected as $action_id => $good_id) {
                $this->presents[$action_id] = $good_id;
            }
        }
        
        if ($with_rejections) return $this->presents;
        
        $this->presents = array_diff_key($this->presents, $this->no_presents);
        
        return $this->presents;
    }

    /**
     * 
     * @return Model_Good
     */
    public function get_ordered_presents()
    {
        return $this->get_presents(FALSE, FALSE);
    }
    
    /**
     * Получить ид подарка акции
     *
     * @param $action_id
     * @return Model_Good
     */
    public function get_present_id($action_id)
    {
        if ( ! empty($this->presents_selected[$action_id])) return $this->presents_selected[$action_id];
        if ( ! empty($this->presents[$action_id]))          return $this->presents[$action_id];
        return FALSE;
    }

    /**
     * Получить из корзины уже имеющиеся комментарии
     * @return array
     */
    public function get_comments() {
        $this->clear_comments(); // Убираем комментарии к отсутствующим в корзине товарам
        return $this->comments;
    }
    
    /**
     * Получить комментарий к товару
     * 
     * @param int $good_id
     * @return string
     */
    public function get_comment($good_id)
    {
        return empty( $this->comments[$good_id] ) ? FALSE : $this->comments[$good_id];
    }
    
    /**
     * Убрать комментарии к отсутствующим в корзине товарам
     */
    protected function clear_comments()
    {
        foreach($this->comments as $good_id => &$c) {
            if (empty($this->goods[$good_id])) $c = FALSE;
        }
        $this->comments = array_filter($this->comments);
    }
    
    /**
     * 
     * @param Model_Good $goods
     * @return array
     */
    protected function apply_price( & $goods)
    {
        $base_price = array(); // розничная цена

        foreach($this->goods as $id => $q) { // считаем сумму по обычным ценам
            if ( ! empty($goods[$id])) {
                $base_price[$id] = $goods[$id]->price;

                $this->goods[$id] = $goods[$id]->buy_limit($q);
                if ($this->goods[$id] < $q) {
                    $this->no_possible[$id] = $goods[$id]->as_array();
                    $this->no_possible[$id]['qty'] = $this->goods[$id];
                }

                $goods[$id]->quantity = $this->goods[$id];
                $goods[$id]->total = $goods[$id]->price * $goods[$id]->quantity; // итого по этому товару
                $goods[$id]->order_comment = $this->get_comment($id); // Комментарий к товару в корзине

                $this->total += $goods[$id]->total;
                $this->qty += $this->goods[$id];

                if ($goods[$id]->big) $this->big[$id] = $goods[$id]->qty; // крупногабаритка в формате ид - кол-во!
            } else {
                unset($this->goods[$id]); // если 0 или меньше - убираем товар из корзины
                if ( isset($this->big[$id])) unset($this->big[$id]); // и из крупных
            }
        }
        
        // Применяем цены статуса ЛК
        if ($this->status_id == 0 && ($this->total >= self::MAX_SIMPLE)) $this->status_id = 1; // тоже любимый
        if ($this->status_id == 1) $this->apply_lk_price($goods);
        
        /* если
        foreach($this->goods as $id => $q)
        {
            $base_price[$id] = $goods[$id]->price;
        }
        */
        
        return $base_price;
    }

    protected function apply_lk_price( & $goods)
    {
        $this->total = 0;
        $prices = Model_Good::get_status_price($this->status_id, array_keys($goods)); // получаем цены для статуса

        foreach($goods as $good_id => $good) {
            if (empty($prices[$good_id])) $prices[$good_id] = $goods[$good_id]->price;
            $goods[$good_id]->price = $prices[$good_id];
            $goods[$good_id]->total = $good->quantity * $prices[$good_id];
            $this->total += $goods[$good_id]->total;
        }
    }
    
    /**
     * 
     * @param Model_Action $action
     * @param Model_Good $goods
     * @param array $base_price [$good_id => $price]
     * @return type
     */
    protected function count_ordered_sum($action, $goods, $base_price)
    {
        $ordered_value = 0;
        foreach($action->good_ids as $gid)  // На какую сумму заказано
        {
            if ( ! empty($goods[$gid]) AND ! empty($base_price[$gid]))
            {
                $ordered_value += $goods[$gid]->quantity * $base_price[$gid]; // цены от розницы тут
            }
        }
        return $ordered_value;
    }
    
    protected function count_ordered_qty($action, $goods)
    {
        $ordered_value = 0;
        foreach($action->good_ids as $gid)  // Какое кол-во заказано
        {
            if ( ! empty($goods[$gid]))
            {
                $ordered_value += $goods[$gid]->quantity; // суммируем количество
            }
        }
        return $ordered_value;
    }

    protected function apply_discount( & $goods, $base_price, $discount_percent, $good_ids = NULL)
    {
        foreach($goods as &$g)
        {
            if (empty($good_ids[$g->id])) continue;

            $gprice = round($base_price[$g->id] * (1 - $discount_percent / 100) * 100) / 100; // цена товара со скидкой - округляется до 1 коп
            $delta = ($g->price - $gprice) * $g->quantity;
            $this->discount += $delta;
            $this->total -= $delta; // общая сумма уменьшается!
            $g->price = $gprice;
            $g->total = $g->quantity * $gprice;
        }
    }

    /**
     *  проверка скидочных акций, перерасчёт цены если есть
     * 
     * @param Model_Good $goods
     * @param array $base_price
     */
    protected function check_price_actions( & $goods, $base_price)
    {
        $active_actions = Model_Action::by_goods(array_keys($this->goods), Conf::VITRINA_ALL, array(
            Model_Action::TYPE_PRICE_QTY,
            Model_Action::TYPE_PRICE_QTY_AB,
            Model_Action::TYPE_PRICE_SUM,
            Model_Action::TYPE_PRICE_SUM_AB
        ));

        foreach($active_actions as $a)
        {
            $discount_percent = 0;
            $ordered_value = 0;     // Сколько товаров или сумма на которую лежит в корзине

            switch ($a->type) {
                case Model_Action::TYPE_PRICE_QTY: // скидки от количества на процент
                case Model_Action::TYPE_PRICE_QTY_AB: // скидки от количества A на процент для Б
                    $values         = array_map('trim', explode('|', $a->quantity));
                    $percents       = array_map('trim', explode('|', $a->sum));
                    $ordered_value  = $this->count_ordered_qty($a, $goods);
                    break;
                
                case Model_Action::TYPE_PRICE_SUM: // скидки от cуммы на процент
                case Model_Action::TYPE_PRICE_SUM_AB: // скидки от cуммы А на процент для Б
                    $percents       = array_map('trim', explode('|', $a->quantity));
                    $values         = array_map('trim', explode('|', $a->sum));
                    $ordered_value  = $this->count_ordered_sum($a, $goods, $base_price);
                    break;
            }

            if (empty($values)) return; // нет скидочных акций

            foreach($values as $k => $val) { // Выбираем самое большое условие по кол-ву
                
                if ($ordered_value >= $val) $discount_percent = $percents[$k]; // акция сработала, перебираем пока не найдем самое большое
                else break;
            }
            
            if ($discount_percent > 0) { // применить нулевую скидку = сбросить цену без учета ЛК!

                switch ($a->type) {

                    case Model_Action::TYPE_PRICE_QTY:
                    case Model_Action::TYPE_PRICE_SUM:

                        $this->apply_discount($goods, $base_price, $discount_percent, $a->good_ids);
                        break;

                    case Model_Action::TYPE_PRICE_QTY_AB:
                    case Model_Action::TYPE_PRICE_SUM_AB:

                        $this->apply_discount($goods, $base_price, $discount_percent, $a->good_b_idz());
                        break;
                }
            }
        }
    }

    protected function check_coupon()
    {
        if ( ! empty($this->coupon))
        {
            $coupon_obj = new Model_Coupon($this->coupon);
            
            if ($coupon_obj->is_usable($this->total))
            {
                $this->total -= $this->coupon['sum'];
            }
        }
    }
	
	public function remove( $good_id )
    {
        $this->set_good_qty($good_id, 0);
		
		return $this;
	}
	
    public function get_good_qty($good_id)
    {
        if($this->force_recount) $this->recount ();
        
        return $good_id == 'blago' ? $this->blago: ( empty($this->goods[$good_id]) ? NULL : $this->goods[$good_id] );
    }
    
    /**
     * 
     * @param type $good_id
     * @param type $qty
     * @return \Cart
     */
    public function set_good_qty($good_id, $qty)
    {
        
        $old_qty = $this->get_good_qty($good_id);

		if ('blago' == $good_id) $this->blago = $qty;
		
        else $this->goods[$good_id] = $qty;
        
        if ($old_qty != $qty) $this->force_recount = TRUE;
        
        if ($old_qty < $qty) $this->added = TRUE;
        
        return $this;
    }
    
	public function inc($good_id)
    {
		return $this->set_good_qty($good_id, $this->get_good_qty($good_id) + 1);
	}

	public function dec($good_id)
    {
		return $this->set_good_qty($good_id, $this->get_good_qty($good_id) - 1);
	}

	public function change($goodId, $value ){
		$this->goods[$goodId] = (int)$value;
		return $this->recount();
	}

    /**
     * Пересчитать цену корзины и статус заказа, получить товары корзины
     * @return Model_Good[]
     */
    public function recount()
    {
        $this->save_clear(); // Удаляем все вычисляемые позже данные
        
        $this->reset_status(); // Устанавливаем статус в тот, что стоит у самого клиента
        
        $this->big = [];
        $goods = []; // [id=>qty]
        $this->goods = array_filter(array_map('abs', $this->goods)); // без отрицательных и 0
		
        if (isset($this->goods['blago'])) {
            $this->blago = abs($this->goods['blago']);
            unset($this->goods['blago']);
        }

        if ( ! empty($this->goods)) {
            $goods = ORM::factory('good') // попадают только товары с ненулевой ценой и количеством
                ->where('id', 'IN', array_keys($this->goods))
                ->where('show', '=', 1) // tut blago est
                ->where('qty', '!=', 0)
                ->find_all()
                ->as_array('id');

			foreach( $this->no_possible as $id => $_ ){
				$_ = &$goods[$id];
				
				if( empty( $this->goods[$id] ) || ($_ instanceof Model_Good && $this->goods[$id] == $_->buy_limit($this->goods[$id]))) {
					unset( $this->no_possible[$id] );
				}
			}
			
            $base_price = $this->apply_price($goods); // Пересчитываем цены, в т.ч. ЛК, убираем товары с 0 qty

            $this->check_price_actions($goods, $base_price);
            
            $this->check_coupon();

        } else {
            $this->goods = array();
        }
        $this->save();

        $this->force_recount = FALSE;
        
        return $goods;
    }

    /**
     * Сохранение корзины в сессию
     * @chainable
     * @return \Cart
     */
    public function save()
    {
        $cart = clone $this; // надо сделать копию, чтобы не пересчитывать имеющуюся корзину заново
        
        $cart->save_clear();
        
        Session::instance(null, $this->_session_id)->set('cart', $cart)->write();
		
        return $this;
    }

    
    protected function save_clear() // Очищает данные, которые не надо хранить в сессии
    {
        $this->total            = NULL;
        $this->qty              = NULL;
        $this->actions          = array();
        $this->status_id        = NULL;
        $this->added            = NULL;
        $this->presents         = array();
        $this->present_variants = array();
        $this->promo            = array();
        $this->big              = array();
        $this->discount         = NULL;
        $this->force_recount    = TRUE;
        
    }
    
    /**
     * Получить html превью корзины
     * @return string
     */
    public function __toString()
    {
		
		if( Request::current()->uri() != 'personal/basket.php' ){
			
			$cart = View::factory('smarty:common/cart', array('cart' => $this))->render();

			return $cart;
		}
		
		return '';
    }

    /**
     * Выбирает из условий акции максимальное подходящее, 
     * вычисляет уровень условия и недостающую до следующего уровня цифру
     * 
     * @param array $conditions
     * @param int $value
     * @param int $level
     * @param int $delta
     * @return int
     */
    private function check_conditions($conditions, $value, & $level = 0, & $delta = NULL) {
        $result = FALSE;
        asort($conditions);     // ...по возрастанию
        foreach($conditions as $cond) { // Выбираем самое большое условие по кол-ву
            if ($value >= $cond) { // Подходит
                $result = $cond;
                $level ++;
            } else {
                $delta = $cond - $value;
                break;
            }
        }        
        return $result;
    }
    
    /**
     * проверяет товары в корзине на предмет работы акций по этим товарам
     * товары надо скармливать те, которые получены из get_goods
     * @param $goods Model_Good[] массив товаров
     * @param $del_presents [] массив ид подарков, которые надо удалить
     * @return array массив ключи - ид акции, значение - ид подарка
     */
    public function check_actions($goods = NULL, $del_presents = FALSE)
    {
        if (is_null($goods)) $goods = $this->recount();

        $active_actions = Model_Action::by_goods(array_keys($goods));

		$this->presents = array();
        
        foreach($active_actions as $a) {
            $promo = array();
            if ($a->new_user && Model_User::logged() && Model_User::current()->sum) continue; // акция только для новых пользователей
            
            if ($a->is_gift_type()) { // обрабатываем пока только акции с подарками
                
                $condition_level    = 0;
                $present_ids        = $a->get_present_ids();

                // определим подходящее условие
                $ordered_value = 0;
                foreach($a->good_ids as $gid) { // Сколько товаров из прикрепленных к акции заказано
                   if (Model_Action::TYPE_GIFT_QTY == $a->type) { // подарок по количеству
                       if ( ! empty($goods[$gid]->quantity)) $ordered_value += $goods[$gid]->quantity;
                   } elseif (Model_Action::TYPE_GIFT_SUM == $a->type) {
                       if ( ! empty($goods[$gid]->total)) $ordered_value += $goods[$gid]->total; // на какую сумму заказано всего
                   }
                }
                if ($a->count_from && Model_User::logged()) { // накопительная акция - посчитаем сколько накопил
                    $old_ordered_value = Model_User::current()->get_goods_sum($a);
                    if(strtotime($a->count_to) >= time()) {
                        $ordered_value += $old_ordered_value; // всего накопил
                    }
                }
                $to_next_sum = null; // Сколько не хватает до следующего уровня
                $current_value = $this->check_conditions(array_keys($present_ids), $ordered_value, $condition_level, $to_next_sum);
                if (FALSE !== $current_value) { // На подарок хватает
                    $a->pq = 1; // 1 всегда дарим, если хоть на 1 хватает
                    if ($a->each) { // за каждые
                        // Если указано от 0, то если условие - количество, дарим за каждый
                        // А если условие сумма, то 1
                        if ($current_value > 0) $a->pq = floor($ordered_value / $current_value);
                        elseif (Model_Action::TYPE_GIFT_QTY == $a->type) $a->pq = $ordered_value;
                    }
                }
                // надо проверить , а не накопительная ли это акция                       
                if ($a->count_from AND ! is_null($to_next_sum)) { // считаем сколько до подарка

                    $promo = array(
                        'delta' => $to_next_sum,
                        'sum'   => $ordered_value,
                        'stage' => $condition_level + 1 // Уведомление о шаге промо
                    );
                }
                if ( ! empty($present_ids[$current_value])) {
                    foreach ($present_ids[$current_value] as $pid) {
                        $this->present_variants[$a->id][] = $pid;
                        if ( ! empty($this->presents_selected[$a->id]) AND $this->presents_selected[$a->id] == $pid) {
                            $this->presents[$a->id] = $pid;
                        }
                        if (empty($this->presents[$a->id])) {
                            $this->presents[$a->id] = $pid;
                        }
                    }
                    
                    if ($a->parent_id) { // если есть родительская акция - заполняем её данными
                        $a->name = $active_actions[$a->parent_id]->name;
                        $a->preview = $active_actions[$a->parent_id]->preview;
                    }
                    $this->actions[$a->id] = $a;
                }
                
            } elseif (in_array ($a->type, array(
                Model_Action::TYPE_PRICE_QTY,
                Model_Action::TYPE_PRICE_QTY_AB,
                Model_Action::TYPE_PRICE_SUM,
                Model_Action::TYPE_PRICE_SUM_AB
            ))) { // Тип скидка

                $sum = 0;
                foreach($a->good_ids as $gid) { // Сколько товаров из прикрепленных к акции заказано
                    $sum += $goods[$gid]->quantity * $goods[$gid]->price;
                }
                /* TODO добавить учет типа акции
                if ($sum < $a->sum) {
                    $promo['sum']       = $sum;
                    $promo['delta']     = $a->sum - $sum;
                    $promo['discount']  = $a->quantity;
                    
                }
                 */
            }

            // добавляем данные для напоминания о накопительной акции
            if ( ! empty($promo) AND (empty($a->count_to) OR (date('Y-m-d') < $a->count_to))) {
                $promo['action_id']         = $a->id;
                $promo['cart_icon']         = $a->cart_icon;
                $promo['cart_icon_text']    = $a->cart_icon_text;
                $this->promo[$a->id] = $promo;
            }
            
        }
        if ( ! empty($del_presents) && ! empty($this->no_presents)) { // удалять товары с отказами (по ID акции!!!)
            foreach($this->no_presents as $aid) {
                if ( ! empty($this->presents[$aid])) {
                    unset($this->presents[$aid]);
                }
            }
        }
        
        return $this->presents;
    }
    
    /**
     * Сколько не хватает до Любимого клиента
     * @return int
     */
    public function get_delta()
    {
        if ($this->force_recount) $this->recount();
        
        return abs($this->total - self::MAX_SIMPLE);
    }

    /**
     * Подсчёт цифры скидки
     */
    public function discount()
    {
        if ($this->force_recount) $this->recount();
        
        return $this->discount; // $this->total * $this->discount / 100;
    }

    /**
     * Подсчёт общей суммы к оплате
     */
    public function get_total()
    {
        if ($this->force_recount) $this->recount();
        
        return $this->total + $this->blago; //  - $this->discount();
    }

    public function get_qty()
    {
        if ($this->force_recount) $this->recount();
        
        return $this->qty;
    }
    
    /**
     * Есть ли внутри крупногабаритка, лежащая на складе у поставщика?
     */
    public function big_to_wait($get_if_any = FALSE)
    {
        
        if (empty($this->big)) return FALSE; // Крупногабаритки в заказе нет
        if ( ! in_array(-1, $this->big)) return FALSE; // вся крупногабаритка есть на складе
        if ($get_if_any == FALSE) return TRUE;

        $idz = array_keys($this->big); // Optimisation
        
        return ORM::factory('good')->where('id', 'IN', $idz)->find_all()->as_array('id');
    }

    /**
     * Может ли товар быть доставлен в регион?
     */
    public function can_ship()
    {
        if (empty($this->goods)) return FALSE;

        $data = DB::select('g.id', 'g.section_id', 's.parent_id')
            ->from(['z_good', 'g'])
            ->join(['z_section', 's'])
                ->on('g.section_id', '=', 's.id')
            ->where('g.id', 'IN', array_keys($this->goods))
            ->execute()
            ->as_array();

        foreach($data as $i) {
            // мы не доставляем детское питание (все подкатегории категории 28934)
            if ($i['parent_id'] == 28934) return FALSE;;
            // и категории Детские домики, горки, качели, песочницы (116957) и Кроватки и аксессуары (30025)
            if (in_array($i['section_id'], [116957, 30025])) return FALSE;
        }
        return TRUE;
    }

    /**
     * Добавить скидочный купон
     * @param $coupon
     */
    public function add_coupon(Model_Coupon $coupon)
    {
        $this->coupon = $coupon->as_array();
        $this->total -= $coupon->sum;
    }

    /**
     * Убрать скидку
     */
    function remove_coupon()
    {
        if ( ! empty($this->coupon)) {
            $this->total += $this->coupon['sum'];
            $this->coupon = array();
            $this->save();
        }
    }

    /**
     * Получить статус цен для клиента
     * @return int
     */
    public function status_id()
    {
        if ( $this->force_recount) $this->recount();
        return $this->status_id;
    }
}