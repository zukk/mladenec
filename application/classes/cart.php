<?php

class Cart {

    const WEIGHT_RATE = 1.25; // увеличивающий коэф на объем
    const VOLUME_RATE = 1.5; // увеличивающий коэф на вес

    const MAX_SIMPLE = 4500; // сумма заказа
    const BLAG_ID = 138549; // идентификатор товара благотворительности

    public $total           = 0;        // общая стоимость товаров (без благотворительности и доставки) в корзине
    public $qty             = 0;        // общее количество товаров в корзине
    public $actions         = [];  // action.id => Model_Action
    public $goods           = [];  // good.id => qty
    public $comments        = [];  // good.id => comment
    public $comment_email   = [];  // good.id => comment_email
    public $status_id       = 0;
    public $blago           = 0;        // благотворительность
    public $added           = FALSE;    // флаг - было ли добавление только что - используется при отображении корзины
    public $presents        = [];  // [ $action_id => $good_id, ... ]
    public $coupon_presents = [];  // ид товаров полученных как подарки за купон
    public $present_variants  = [];  // Подарки _на выбор_, $action_id => [$good_id1,$good_id2, ...]
    public $presents_selected = []; // Выбранные подарки [ $action_id => $good_id, ... ]
    public $no_presents     = [];  // [ $action_id => $action_id, ... ] ид акций в  которых человек отказался от подарка
    public $promo           = [];  // промо подарков - массив, ключ = сумма накопленная, значение = акция
    public $sborkable       = [];  // [$id => $qty] массив id товаров для которых возможна бесплатная сборка
    public $big             = [];  // [$id => $qty] массив id крупногабаритки в заказе
    public $to_wait         = [];  // [$id => $qty] массив id товаров не со склада в заказе
    public $discount        = 0;        // сумма скидки
    public $no_possible     = [];  // товары которых заказано больше чем есть
    public $coupon          = FALSE;  // код купона или FALSE если нету
    public $coupon_error    = FALSE;  // ошибка если код купона непуст и неприменим
    public $delivery_open   = FALSE; // открыть ли форму с адресом доставки?

    public $weight          = 0; // вес корзины - в кг
    public $width           = 1; // размеры корзины - в см
    public $length          = 1; // длина
    public $height          = 1; // высота
    public $gift_sum        = 0; // сумма купленных сертификатов
    public $ship_wrong = FALSE; // флаг проблемного веса / объёма в заказе

	protected $_session_id  = NULL;
    protected $force_recount = TRUE;

    public $total_action = [];   // скидочная акция на всё (сумма от => процент скидки)

    /**
     * Конструктор может сразу добавлять
     * @param array $goods
     * @param string $session_id
     */
    function __construct($goods = [], $session_id = NULL)
    {
        try {
            $cart_s = Session::instance(NULL, $session_id)->get('cart');
            $this->_session_id = $session_id;
        
        } catch (Session_Exception $e) {
            $cart_s = FALSE;
        }
		$this->reset_status();

        if ($cart_s instanceof Cart) {
            $this->total = $cart_s->total;
            $this->goods = $cart_s->goods;
            $this->qty = $cart_s->qty;
            $this->comments = ! empty($cart_s->comments) ? $cart_s->comments : [];
            $this->comment_email = ! empty($cart_s->comment_email) ? $cart_s->comment_email : [];
            $this->blago = ! empty($cart_s->blago) ? $cart_s->blago : 0;
            $this->discount = ! empty($cart_s->discount) ? $cart_s->discount : 0;
            $this->presents_selected = ! empty($cart_s->presents_selected) ? $cart_s->presents_selected : [];
            $this->no_presents = ! empty($cart_s->no_presents) ? $cart_s->no_presents : [];
            $this->big = ! empty($cart_s->big) ? $cart_s->big : [];
            $this->sborkable = ! empty($cart_s->sborkable) ? $cart_s->sborkable : [];
            $this->to_wait = ! empty($cart_s->to_wait) ? $cart_s->to_wait : [];
            $this->no_possible = ! empty($cart_s->no_possible) ? $cart_s->no_possible : [];

            $this->weight = ! empty($cart_s->weight) ? $cart_s->weight : 0;
            $this->width = ! empty($cart_s->width) ? $cart_s->width : 1;
            $this->height = ! empty($cart_s->height) ? $cart_s->height : 1;
            $this->length = ! empty($cart_s->length) ? $cart_s->length : 1;
            $this->ship_wrong = ! empty($cart_s->ship_wrong);

            $this->coupon = [];
            if ( ! empty($cart_s->coupon)) {
                if ( ! is_array($cart_s->coupon)) { // всегда массив ждём
                    $this->load_coupon(strval($cart_s->coupon));
                } elseif (empty($cart_s->coupon['id'])) {
                    $this->load_coupon(strval($cart_s->coupon['name']));
                } else {
                    $this->coupon = $cart_s->coupon;
                }
            }

            $this->delivery_open = ! empty($cart_s->delivery_open);
        }
        $this->add($goods); // может быть сразу добавление товаров
    }
        
    /**
     * @return Cart
     */
    static function instance($goods = [])
    {
        return new self($goods);
    }
        
    /**
     * Sets status_id for cart according to user status
     */
    private function reset_status()
    {
        $this->status_id = 0;
        if ($cur = Model_User::current()) $this->status_id = $cur->status_id;
        if ( ! empty($this->coupon['type']) && $this->coupon['type'] == Model_Coupon::TYPE_LK) {
            $this->status_id = 1;
        }
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
        $this->coupon_error = FALSE;
        $this->coupon = $this->goods = $this->presents = $this->comments = $this->sborkable = $this->big = $this->to_wait = $this->no_possible = [];

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
            if ( ! isset($this->goods[$good_id])) $this->goods[$good_id] = 0;
            $this->goods[$good_id] += $q;
        }

        // Добавление благотворительности уже есть в recount
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
    public function set_comments($comments)
    {
        if (empty($comments)) return $this;
        //$this->comments = $comments['comments'] + $this->comments; // Новый массив (первый, $comments) имеет больший
        // приоритет
        if(!empty($comments['comments'])){
            $this->comments = $comments['comments']; // Новый массив (первый, $comments) имеет больший приоритет
        }
        if(!empty($comments['comment_email'])){
            $this->comment_email = $comments['comment_email'];
        }

        $this->clear_comments(); // Убираем комментарии к отсутствующим в корзине товарам
        
        return $this;
    }
    
    /**
     * Указать выбранные пользователем подарки
     * 
     * @param array $presents [$action_id => $good_id, ...]
     */
    public function select_presents($presents)
    {
        if ( empty($presents) || ! is_array($presents)) return;
        
        foreach($presents as $action_id => $present_id) {

            if ($present_id == -1) { // отказ от подарка в накопительной акции
                $this->no_presents[$action_id] = 1;
                if (isset($this->presents[$action_id])) unset($this->presents[$action_id]);
                if (isset($this->presents_selected[$action_id])) unset($this->presents_selected[$action_id]);
            } else {
                $this->presents_selected[$action_id] = $present_id;
                $this->presents[$action_id] = $present_id;
                if (isset($this->no_presents[$action_id])) unset($this->no_presents[$action_id]);
            }
        }
        $this->save();
    }

    /**
     * Получить объекты подарков, возвращает вместе с вариантами, если возможен выбор
     *
     * @return Model_Good
     */
    public function get_present_goods()
    {
        $good_ids_to_load = $this->presents;

        foreach ($this->present_variants as $sp) {
            foreach($sp as $p) { $good_ids_to_load[] = $p; }
        }

        foreach ($this->coupon_presents as $p) {
            $good_ids_to_load[] = $p;
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
            $this->recount();
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
     * Получить из корзины уже имеющийся email
     * @return array
     */
    public function get_comment_email() {

        return $this->comment_email;
    }
    /**
     * Получить email для товара
     * @return array
     */
    public function get_commentid_email($good_id) {

        return empty( $this->comment_email[$good_id] ) ? FALSE : $this->comment_email[$good_id];
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
     * Подсёт цены, ограничений на кол-во, статуса по кгт и нет на складе
     * @param Model_Good[] $goods
     * @return array
     */
    protected function check_qty_wait_lk( & $goods)
    {
        $base_price = []; // сюда положим цену от которой потом считаем скидки

        foreach($this->goods as $id => $q) { // считаем сумму по обычным ценам
            if ( ! empty($goods[$id]) && ($goods[$id] instanceof Model_Good)) {
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

                if ($goods[$id]->big) $this->big[$id] = $goods[$id]->qty; // крупногабаритка в формате ид - кол-во
                if ($goods[$id]->sborkable()) $this->sborkable[$id] = $goods[$id]->qty; // товары со сборкой ид - кол-во
                if ($goods[$id]->qty == -1) $this->to_wait[$id] = $goods[$id]->qty; // нет на складе, в формате ид - ид
            } else {
                unset($this->goods[$id]); // если 0 или меньше - убираем товар из корзины
                if ( isset($this->sborkable[$id])) unset($this->sborkable[$id]); // и сборка
                if ( isset($this->big[$id])) unset($this->big[$id]); // и из крупных
                if ( isset($this->to_wait[$id])) unset($this->to_wait[$id]); // и из нет на сладе
            }
        }

        if ( isset($this->goods[Model_Good::SBORKA_ID1C]) && empty($this->sborkable)) { // в корзине есть сборка но нет товаров для нее - скинем сборку
            unset($this->goods[Model_Good::SBORKA_ID1C]);
        }

        // Применяем цены статуса ЛК
        if ($this->status_id == 0 && ($this->total >= self::MAX_SIMPLE)) $this->status_id = 1; // тоже любимый
        if ($this->status_id == 1) $this->apply_lk_price($goods);
        
//        foreach($this->goods as $id => $q) $base_price[$id] = $goods[$id]->price; // считать скидку от цены клиента, без этой строки - от розницы

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

        $idz = $action->total ? array_keys($goods) : $action->good_ids;  // участники акции

        foreach($idz as $gid)  // На какую сумму заказано
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

    /*
     * Применить скидку на товары от указанных цен на процент
     */
    protected function apply_discount( & $goods, $base_price, $discount_percent, $good_ids)
    {
        foreach($goods as &$g)
        {
            if (empty($good_ids[$g->id])) continue;

            $gprice = round($base_price[$g->id] * (1 - $discount_percent / 100) * 10) / 10; // цена товара со скидкой - округляется до 10 коп
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
        $active_actions = Model_Action::by_goods(array_keys($this->goods));

        foreach($active_actions as $a) {
            if ($a->is_price_type()) {
                $discount_percent = 0;
                $ordered_value = 0;     // Сколько товаров или сумма на которую лежит в корзине

                switch ($a->type) {
                    case Model_Action::TYPE_PRICE_QTY: // скидки от количества на процент
                    case Model_Action::TYPE_PRICE_QTY_AB: // скидки от количества A на процент для Б
                        $values = array_map('trim', explode('|', $a->quantity));
                        $percents = array_map('trim', explode('|', $a->sum));
                        $ordered_value = $this->count_ordered_qty($a, $goods);
                        break;

                    case Model_Action::TYPE_PRICE_SUM: // скидки от cуммы на процент
                    case Model_Action::TYPE_PRICE_SUM_AB: // скидки от cуммы А на процент для Б
                        $percents = array_map('trim', explode('|', $a->quantity));
                        $values = array_map('trim', explode('|', $a->sum));
                        $ordered_value = $this->count_ordered_sum($a, $goods, $base_price);
                        break;
                }

                if (empty($values)) continue; // нет скидочных акций

                foreach ($values as $k => $val) { // Выбираем самое большое условие по кол-ву

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

        if ( ! empty($this->coupon)) {
            $this->use_coupon($goods, $base_price);
        }
    }

    /**
     * Проверка купона на существование и запись его в корзину если есть
     * @param $name
     * @return array|bool
     */
    public function load_coupon($name)
    {
        $coupon = new Model_Coupon(['name' => trim($name), 'active' => 1]);
        if ( ! $coupon->loaded()) {
            $this->coupon_error = 'Купона с таким кодом не существует';
            return FALSE;
        }
        $this->coupon = $coupon->as_array();
        return TRUE;
    }

    /**
     * Пытаемся использовать прикрепленный к корзине купон. Если не можем - заполняем ошибку
     * @param $goods [] товары корзины
     * @param $base_price [] цены на товары, от которых считать скидки
     * @return bool
     */
    protected function use_coupon($goods, $base_price)
    {
        $this->coupon_error = FALSE;

        $user = Model_User::current();

        if (empty($this->total)) { $this->coupon_error = 'В корзине нет товаров'; return FALSE; };

        if (empty($this->coupon['id'])) { $this->coupon_error = 'Нет такого купона'; return FALSE; }
        $coupon = new Model_Coupon($this->coupon['id']);

        if ( ! $coupon->is_begun()) { $this->coupon_error = 'Купон может быть активирован с '.$coupon->from; return FALSE; };

        if ($coupon->is_expired()) { $this->coupon_error = 'Купон просрочен с '.$coupon->to; return FALSE; };

        if ($coupon->used >= $coupon->uses) { $this->coupon_error = 'Превышен лимит использований купона'; return FALSE; };

        if ($this->total < $coupon->min_sum) { $this->coupon_error = 'Минимальная сумма товаров '.$coupon->min_sum; return FALSE; };

        if ($user) { // есть пользователь - посчитаем сколько купонов потратил

            $uses = ORM::factory('order')
                ->where('user_id', '=', $user->id)
                ->where('coupon_id', '=', $coupon->id)
                ->where('status', '!=', 'X')
                ->count_all();

            if ($uses >= $coupon->per_user) { $this->coupon_error = 'Превышен лимит использований купона'; return FALSE; };

            if ( ! empty($coupon->user_id) && $coupon->user_id != $user->id) { $this->coupon_error = 'Купон предназначен для другого пользователя'; return FALSE;}
        }

        switch ($coupon->type) {

            case Model_Coupon::TYPE_SUM:  // скидка на сумму

            case Model_Coupon::TYPE_SUB: // или скидка на сумму за подписку

                // если в заказе есть сертификаты - их нельзя оплачивать купоном
                $price_gift = $this->gift_sum;

                if ($coupon->type == Model_Coupon::TYPE_SUB && empty($user->sub)) { $this->coupon_error = 'Вы не подписаны на e-mail рассылки. Подписаться на рассылки можно в '.HTML::anchor(Route::url('user'), 'личном кабинете'); return FALSE;}
                if ($coupon->type == Model_Coupon::TYPE_SUB && empty($user->email_approved)) { $this->coupon_error = 'Вы не подтвердили email'; return FALSE;}

                $this->total = $this->total - $coupon->sum - $price_gift;
                $this->total = max(0, $this->total) + $price_gift; // TODO
                $this->discount += $coupon->sum;

                break;

            case Model_Coupon::TYPE_PERCENT:  // скидка на процент на конкретные товары
                $cgoods = $coupon->get_goods(array_keys($this->goods));
                if (empty($cgoods))  { $this->coupon_error = 'В корзине нет товаров, для которых активен купон'; return FALSE; };

                $used = FALSE;

                foreach($cgoods as $discount => $qty_goodz) {
                    foreach ($qty_goodz as $min_qty => $goodz) {

                        $qty = 0; // считаем сколько товаров из этой группы есть в корзине, для
                        $good_qtys = []; // сюда соберем ид товаров

                        foreach ($goodz as $good_id => $g) { // посчитаем сколько товаров

                            if ($g->old_price == 0) { // для товаров с перечеркиванием купон не применяется
                                $qty += $this->goods[$good_id];
                                $good_qtys[$good_id] = $this->goods[$good_id];
                            }
                        }

                        if ($qty >= $min_qty) { // условие на количество - выполнено

                            // считаем скидку в процентах за каждый, при учете что даём скидку не более чем на max_sku товаров одного наименования
                            foreach($good_qtys as $good_id => $count) {

                                if ($coupon->max_sku > 0 && $count > $coupon->max_sku) {

                                    $gprice = round($base_price[$good_id] * (1 - $discount / 100)); // цену округляем до 1 коп
                                    $total = $base_price[$good_id] * ($count - $coupon->max_sku) + $gprice * $coupon->max_sku;
                                    $each = $total / $count;
                                    $percent = (1 - $each / $base_price[$good_id]) * 100;
                                    $this->apply_discount($goods, $base_price, $percent, [$good_id => $good_id]);
                                } else {
                                    $this->apply_discount($goods, $base_price, $discount, [$good_id => $good_id]); // применяем скидку на все
                                }

                            }
                            $used = TRUE;
                        }
                    }
                }

                if ( ! $used ) { $this->coupon_error = 'В корзине недостаточно товаров, для которых активен купон'; return FALSE; };
                break;

            case Model_Coupon::TYPE_CHILD; // купон со скидкой на ДР ребенка - скидка 10% на все кроме подгузов и питания, если есть старая цена - не действует

                $used = FALSE;

                foreach($this->goods as $good_id => $g) {

                    $catalog = Model_Section::get_catalog();
                    $forbidden_sections = array_merge(array_keys($catalog[28934]->children), array_keys($catalog[29777]->children)); // подгузники и питание

                    // если на товар есть старая цена, то базовая цена = старая цена (купон выключает скидочные акции)
                    // все остальные типы акций уже отключены так как в расчетах используется base_price (цена розница)
                    if ( ! in_array($g->section_id, $forbidden_sections) && $g->old_price == 0) {
                        $this->apply_discount($goods, $base_price, 10, [$good_id => $good_id]); // применяем скидку 10% на все
                        $used = TRUE;
                    }
                }

                if ( ! $used ) { $this->coupon_error = 'В корзине нет товаров, для которых активен купон'; return FALSE; };
                break;

            case Model_Coupon::TYPE_PRESENT:
                $cgoods = $coupon->get_goods();
                if (empty($cgoods))  { $this->coupon_error = 'В корзине нет товаров, для которых активен купон'; return FALSE; };

                $this->coupon_presents = [];
                foreach($cgoods as $discount => $qty_goodz) {
                    foreach ($qty_goodz as $min_qty => $goodz) {
                        foreach ($goodz as $good_id => $g) {
                            $this->coupon_presents[] = $good_id;
                        }
                    }
                }
                //print_r($this->coupon_presents);

                break;

            case Model_Coupon::TYPE_LK;
                $user = Model_User::current();
                if ($user && $user->status_id == 1) {
                    $this->coupon_error = 'Купон не может быть использован, Вы уже получили статус &laquo;Любимый клиент&raquo;'; return FALSE;
                }
                if ($user) {
                    $user->status_id = 1;
                    $user->save();
                    $coupon->used();
                }

                $this->status_id = 1;
                break;
        }

        return TRUE;
    }

    public function remove($good_id)
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
     * @param int $good_id
     * @param int $qty
     * @return \Cart
     */
    public function set_good_qty($good_id, $qty)
    {
        $old_qty = $this->get_good_qty($good_id);

		if ('blago' == $good_id) {
            $this->blago = $qty;
        }  else {
            $this->goods[$good_id] = $qty;
        }
        
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

	public function change($goodId, $value)
    {
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

        $goods = []; // [id=>qty]
        $this->goods = array_filter(array_map('abs', $this->goods)); // без отрицательных и 0

        if (isset($this->goods['blago'])) {
            $this->blago = abs($this->goods['blago']);
            unset($this->goods['blago']);
        }

        if ( ! empty($this->goods)) {
            $goods = ORM::factory('good') // попадают только товары с ненулевым количеством
                ->with('prop')
                ->where('good.id', 'IN', array_keys($this->goods))
                //->where('show', '=', 1)
                ->where('qty', '!=', 0)
                ->find_all()
                ->as_array('id');

            foreach ($this->no_possible as $id => $good_arr) { // перепроверяем тут количество и переборы лимитов
				$good = &$goods[$id];

				if (empty($this->goods[$id]) || ($good instanceof Model_Good && $this->goods[$id] == $good->buy_limit($this->goods[$id]))) {
					unset($this->no_possible[$id]);
				}
			}

            // считаем вес и размер и сумму купленных сертификатов
            $this->weight = 0;
            $this->width = $this->height = $this->length = 1;
            $this->gift_sum = 0;

            foreach($goods as $id => $g) {

                // вес
                $w = $g->prop->weight;
                if ($w == '0.00') $this->ship_wrong = TRUE;
                $this->weight += $w * $this->goods[$id];

                // размер
                $size = $g->prop->size;
                if (preg_match('~^(\d+)x(\d+)x(\d+)$~', $size, $matches)) {
                    if ($matches[1] * $matches[2] * $matches[3] == 1) $this->ship_wrong = TRUE;

                    for($i = 0; $i < $this->goods[$id]; $i++) {
                        $this->width += $matches[1]; // ширина - сумма ширин товаров
                        $this->length = max($this->length, $matches[2]); // длина - максимум длин
                        $this->height = max($this->height, $matches[3]); // высота - максимум высот
                    }

                } else {
                    $this->ship_wrong = TRUE;
                }

                // учет подарочных сертификатов
                if (strpos($g->code, 'syst_gift') !== FALSE) { // подарочный сертификат
                    $this->gift_sum += $g->price;
                }
            }

            $this->weight *= self::WEIGHT_RATE; // к весу добавим упаковку
            $this->width = ceil($this->width / 10); // размеры переведем в см
            $this->height = ceil($this->height / 10);
            $this->length = ceil($this->length / 10);

            $base_price = $this->check_qty_wait_lk($goods); // Пересчитываем цены, в т.ч. ЛК, убираем товары с 0 // qty

            $this->check_price_actions($goods, $base_price); // проверяет акции и купон

        } else {
            $this->goods = [];
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

        Session::instance(NULL, $this->_session_id)->set('cart', $cart)->write();

        return $this;
    }
    
    protected function save_clear() // Очищает данные, которые не надо хранить в сессии
    {
        $this->total            = NULL;
        $this->qty              = NULL;
        $this->actions          = [];
        $this->status_id        = NULL;
        $this->added            = NULL;
        $this->presents         = [];
        $this->present_variants = [];
        $this->promo            = [];
        $this->big              = [];
        $this->to_wait          = [];
        $this->sborkable        = [];
        $this->discount         = NULL;

        $this->force_recount    = TRUE;
    }
    
    /**
     * Получить html превью корзины
     * @return string
     */
    public function __toString()
    {
        if ($this->get_total() > 0 && (('/'.Request::current()->uri()) == Route::url('cart'))) return ''; // если мы в корзине и она не пуста - не показываем малую корзинку

        return View::factory('smarty:common/cart', array('cart' => $this))->render();
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
     * проверяет товары в корзине на предмет работы подарочных акций по этим товарам
     * товары надо скармливать те, которые получены из get_goods
     * @param $goods Model_Good[] массив товаров
     * @param $del_presents [] массив ид подарков, которые надо удалить
     * @return array массив ключи - ид акции, значение - ид подарка
     */
    public function check_actions($goods = NULL, $del_presents = FALSE)
    {
        if (is_null($goods)) $goods = $this->recount();

        $active_actions = Model_Action::by_goods(array_keys($goods));

		$this->presents = [];
        
        foreach($active_actions as $a) {
            $promo = [];
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
                    $old_ordered_value = Model_User::current()->get_funded($a);
                    if(strtotime($a->count_to) >= time()) {
                        $ordered_value += $old_ordered_value['sum']; // всего накопил
                    }
                }
                $to_next_sum = null; // Сколько не хватает до следующего уровня
                $current_value = $this->check_conditions(array_keys($present_ids), $ordered_value, $condition_level, $to_next_sum);

                if ( ! $a->check_per_day()) $current_value = FALSE; // проверить акцию на ограничение срабатываний в день

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
                        'stage' => count($present_ids) < 2 ? -1 : $condition_level + 1 // Уведомление о шаге промо
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
     * Получить товар с доставкой со склада поставщика?
     */
    public function to_wait()
    {
        if (empty($this->to_wait)) return FALSE;

        return ORM::factory('good')
            ->where('id', 'IN', array_keys($this->to_wait))
            ->find_all()
            ->as_array('id');
    }

    /**
     * Может ли товар быть доставлен в регион?
     */
    public function can_ship()
    {
        if (empty($this->goods)) return FALSE;

        $section_ids = DB::select('g.id', 'g.section_id', 's.parent_id', 's.vitrina')
            ->from(['z_good', 'g'])
            ->join(['z_section', 's'])
                ->on('g.section_id', '=', 's.id')
            ->distinct('section_id')
            ->where('g.id', 'IN', array_keys($this->goods))
            ->execute()
            ->as_array('section_id');

        if (empty($section_ids)) return FALSE;
        
        if ( ! Conf::instance()->regional_shipping_allowed($section_ids)) return FALSE;
        
        return TRUE;
    }

    /**
     * Убрать купон из корзины
     */
    function remove_coupon()
    {
        if ( ! empty($this->coupon)) {
            $this->coupon = [];
            $this->recount();
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

    /* получить вес заказа в килограммах */
    public function weight()
    {
        return $this->weight;
    }

    /* получить объём заказа в кубометрах  */
    public function volume()
    {
        return $this->width * $this->height * $this->length * 1e-6; // cм в кубометры
    }

    /**
     * Вычисление цены доставки корзины
     * @param $latlong
     * @param $zone_id
     * @param int $mkad_or_city
     * @return FALSE - цена не определена или число - рассчитанная цена
     */
    public function ship_price($latlong = 0, $zone_id = NULL, $mkad_or_city = 0)
    {
        if (is_null($zone_id) || $latlong == 0) { // нет зоны доставки - попробуем определить из крайнего адреса

            $zone_id = 0;

            if (Model_User::logged()) {
                $addr = Model_User::current()->address();
                if ( ! empty($addr)) {
                    $last = current($addr);
                    $zone_id = $last->zone_id;
                    $latlong = $last->latlong;
                    if ($zone_id == Model_Zone::ZAMKAD) {
                        $mkad_or_city = $last->mkad;
                    }
                }
            }
        }

        if ($zone_id == 0) { // это регион или зона не определена

            return FALSE;

        } else { // есть зона - определим по зоне на ближайшую дату

            $z = new Model_Zone($zone_id);
            if ( ! $z->loaded()) return FALSE;

            $date = key($this->allowed_date($z, $latlong)); // первая дата
            $allowed_times = $this->allowed_time($z, $date, $latlong);
            $time_id = key($allowed_times['times']); // первое время

            $zt = new Model_Zone_Time($time_id);
            if ( ! $zt->loaded()) return FALSE;

            $sum =  $zt->get_price($this->total);
            if ($zone_id == Model_Zone::ZAMKAD) $sum += intval($mkad_or_city) * Model_Order::PRICE_KM;

            return $sum;
        }
    }

    /**
     * Получить 10 дат доставки для зоны
     * @param Model_Zone $zone зона доставки
     * @return array ts => morning
     */
    public function allowed_date($zone = NULL, $latlong = 0)
    {
        $add = 0;

        if ($this->total > 0) {
            if ( ! empty($this->to_wait)) {
                $add = 3;   // + 3 дня если нет на складе
            } elseif ( ! empty($this->big)) {
                $add = 1;   // + 1 день если просто крупногабаритка
            }
        }

        if ($zone == FALSE) $add += 1; // регионы + 1 день на отгрузку

        $hm = intval(date('Gi')); // часыминуты
        $deadline = 1200;

        $exclude_dates = [];
        if ( ! empty($zone) && $zone instanceof Model_Zone) {
            // получение списка возможных дат доставки
            $exclude_dates = DB::select('id', 'morning')
                ->from('z_no_delivery')
                ->where('id', '>=', strftime('%Y-%m-%d'))
                ->where('zone_id', '=', $zone->id)
                ->execute()
                ->as_array('id', 'morning'); // эти даты надо исключить

            if (Model_Zone::locate($latlong, Model_Zone::MYTISHI)) { //по мытищам на сегодня до 16-30
                $deadline = 1600;
            }
        }

        $i = $hm >= $deadline ? 1 : 0; // c 1200 часов доставка на сегодня вырубается
        $dates = 10; // число выдаваемых дат
        $return = [];

        do { // набиваем $dates дат, кроме исключённых
            $d = strtotime('+ '.($i).' days midnight');
            $i++;
            if ($i < $add) continue;
            $key = strftime('%Y-%m-%d', $d);
            if ( ! isset($exclude_dates[$key]) || $exclude_dates[$key] == 1) {
                $return[$key] = ! empty($exclude_dates[$key]); // TRUE for morning
                $dates--;
            }
        } while($dates > 0);

        return $return;
    }

    /**
     * Получить возможные интервалы доставки для даты в формате (Y-m-d)
     * @param Model_Zone|bool $zone - зона доставки
     * @param string $date - дата
     * @param string $latlong - координаты точки для проверки замкад в пятницу или сегодня - мытищи
     * @param bool|float $sum - считать цену
     * @return array|bool - со св-вами - times - интервалы, friday_mkad - флаг того что это доставка за мкад в птн и интервалы ограничены
     */
    public function allowed_time($zone, $date, $latlong, $sum = FALSE)
    {
        if (empty($zone)) return FALSE; // нет зоны - нет времени

        if (empty($date) || strtotime($date) < strtotime('today midnight')) $date = date('Y-m-d', time() + 3600 * 24 * 2); // нет даты +2 дня

        $time = intval(date('Gi'));
        $return = [
            'zamkad'    => $zone->id == Model_Zone::ZAMKAD,
            'times'     => [0 => 'Нет доступных интервалов на эту дату'],
        ];

        $excluded = DB::select('morning') // эти даты исключить из выбора
            ->from('z_no_delivery')
            ->where('zone_id', '=', $zone->id)
            ->where('id', '=', $date)
            ->execute()
            ->current();

        // c 1800 часов доставка на завта утро - вырубается
        if (strtotime($date) == strtotime('+1 days midnight')
            && $time >= 1800
            && ! isset($excluded))
        {
            $excluded = 1;
        }

        if ($excluded === 0) return $return;

        $week_day = date('N', strtotime($date));
        $q = $zone->times
            ->order_by('morning')
            ->order_by('sort')
            ->where('active', '=', 1)
            ->where('week_day', '&', 1 << ($week_day - 1)); // учёт дня недели

        // по пятницам проверяем замкад если больше 3 км - только первый интервал (но не в мытищах)
        if ($week_day == 5 && ! Model_Zone::locate($latlong, Model_Zone::MYTISHI) && ! Model_Zone::locate($latlong, Model_Zone::MKAD)) {
            $closest_mkad = Model_Zone::closest_mkad($latlong);
            $dist = Model_Zone::distance($latlong, implode(' ', $closest_mkad));
            if ($dist >= 3) {
                $return['friday_mkad'] = TRUE;
                $q->limit(1);
            }
        }

        if ($excluded == 1) { // утреннее время исключено
            $q->where('morning', '=', 0);
        }

        $times = $q->find_all()->as_array();
        if (empty($times)) return $return;

        if ($date == date('Y-m-d')) { // сегодня исключаем интервалы до окончания которых меньше 3ч часов
            foreach ($times as $k => $t) {
                $from_to = Txt::extract_time($t->name);
                if ($from_to['to'] < $time / 100 + 3) {
                    unset($times[$k]);
                };
            }
        }
        $return['times'] = [];
        //$price = ($date == '2016-12-31') ? 500 : 0; // +500 рублей 31.12

        foreach($times as $time) {
            if ($date == '2015-12-31' && ! in_array($time->id, [2, 3, 32, 33])) { // оставить только 2 интервала - 14-18 и 15-19
                continue;
            }
            $return['times'][$time->id] = [
                'name' => $time->name,
                'price' => $time->get_price($sum === FALSE ? $this->total : $sum),
            ];
        }
        reset($return['times']);
        $first_price = current($return['times']);

        // !!! акция нутрилон - бесплатная доставка внутри мкад если есть товары из списка
        if ($first_price > 0 && Model_Zone::locate($latlong, Model_Zone::MKAD)) { // внутри мкад
            $total = DB::select([DB::expr('COUNT(*)'), 'total'])
                ->from('z_good')
                ->where('id', 'IN', array_keys($this->goods))
                ->where('id1c', 'IN', [
                    50061439,
                    30001108,
                    30001109,
                    30011695,
                    50056098,
                    50056099,
                    50056100,
                    30012319,
                    30001113,
                    30001114,
                    30009925,
                    30001120,
                    30001121,
                    30011584
                ])
                ->execute()
                ->get('total', 0);

            if ($total > 0) {
                // и еще от всех цен доставки по интервалам надо отнять первую цену
                foreach($return['times'] as &$t) {
                    if (ctype_digit($t)) $t -= $first_price;
                }
            }
        }

        return $return;
    }

    /**
     * HTML большой корзины
     * @return string
     * @throws View_Exception
     */
    function checkout($full = FALSE)
    {
        $goods = $this->recount();

        $params = [
            'goods' => $goods,
            'images' => Model_Good::many_images([70], array_keys($goods)), // все картинки размера 70 - одним запросом
            'cart' => $this,
            'promo' => $this->promo,
            'blago' => $this->blago,
            'presents' => $this->check_actions($goods),
            'coupon_presents' => $this->coupon_presents,
            'present_goods' => $this->get_present_goods(),
            'comments' => $this->get_comments(),
            'comment_email' => $this->get_comment_email(),
            'session_params' => Session::instance()->get('cart_delivery'),
            'delivery' => Controller_Product::cart_delivery(),
            'slider' => $this->slider($goods),
            'sborka' => empty($this->sborkable) ?  FALSE : new Model_Good(['code' => Model_Good::SBORKA_ID1C]),
        ];

        return View::factory($full ? 'smarty:product/cart' : 'smarty:cart/goods', $params)->render();
    }

    /**
     * Слайдер для корзины с товарами
     * @param $goods Model_Good[]
     * @return array []
     * @throws Kohana_Exception
     */
    function slider(&$goods)
    {
        $return = ['goods' => []];

        $promos = [];
        foreach($goods as $g) $promos = array_merge($promos, $g->get_promos());

        $return['method'] = 'cart_set';

        if ( ! empty($promos)) { // есть промо к товарам в корзине

            $return['method'] = 'cart2_set';

            $prmkey = rand(0, count($promos) - 1);
            $promo = $promos[$prmkey];
            $slider_goods = $promo->get_goods();

            foreach ($slider_goods as $y => $sg) if ( ! empty($goods[$sg->id])) unset($slider_goods[$y]); // есть в корзине - исключим

            $sliderCount = count($slider_goods);
            $slider_goods = array_slice($slider_goods, 0, 5);

            if ( ! empty($promo->slider_header)) {
                $return['id']   = $prmkey;
                $return['name'] = $promo->slider_header;
            }
            $return['page'] = 1;

        } else { // нет промо - случайное предложение из товарных наборов для корзины

            $sets = ORM::factory('good_set')
                ->where('active', '=', 1)
                ->where('cart', '=', 1)
                ->order_by('id', 'DESC')
                ->find_all()
                ->as_array();

            $sets_count = count($sets);

            if ($sets_count >= 1) {
                $set = $sets[rand(0, count($sets) - 1)];
                if ( ! empty($set) AND ($set instanceof Model_Good_Set) AND $set->loaded()) {
                    $slider_page = rand(-10, 10);
                    $slider_goods = Model_Good::get_set_slider($set->pk(), $total, $slider_page, 5, TRUE, array_keys($goods));

                    if ( ! empty($slider_goods)) {
                        $return['id']   = $set->id;
                        $return['name'] = $set->name;
                        $return['page']  = $slider_page;
                    }
                }
            }
        }

        if ( ! empty($slider_goods)) {
            $return['goods'] = $slider_goods;

            $slider_good_ids = [];
            foreach($slider_goods as $sg) $slider_good_ids[] = $sg->id;

            // цены и картинки
            $return['price']= Model_Good::get_status_price(1, $slider_good_ids);
            $return['images']= Model_Good::many_images(array(255), $slider_good_ids);
        }

        if ( ! empty($sliderCount)) $return['count'] = $sliderCount;

        return $return;
    }

    /**
     * проверка что в корзине только подарочные карты
     * @return bool
     */
    public function gift_only()
    {
        if ( ! $this->total) {
            $this->recount();
        }
        $total_price = '';
        foreach($this->recount() as $id => $rec) {
            $total_price += $rec->price;
        }
        return $this->gift_sum > 0 && $this->gift_sum == $total_price;
    }
}
