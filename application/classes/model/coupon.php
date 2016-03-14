<?php
// скидочные купоны
class Model_Coupon extends ORM {

    const TYPE_SUM = 0; // купон со скидкой на сумму от суммы
    const TYPE_PERCENT = 1; // купон со скдкой на процент на часть товаров
    const TYPE_LK = 2; // купон даёт ЛК
    const TYPE_PRESENT = 3; // купон даёт подарок
    const TYPE_CHILD = 4; // купон на день рождения ребенка - скидка 10% на все кроме подгузов и питания
    const TYPE_SUB = 5; // купон со скидкой на сумму от суммы - за согласие на рассылку - генерится при регистрации,

    //const CHILD_DISCOUNT = 'kidz'; // скрытый купон со скидкой за данные о детях/беременности

    protected $_table_name = 'z_coupon';

    protected $_table_columns = [
        'id' => '',
        'type' => '', // тип купона - 0 = купон со скидкой на сумму от суммы, 1 = купон со скдкой на процент на часть товаров
        'name' => '',  //  код купона, до 16 знаков, большие и малые буквы - одно и тоже
        'sum' => '',  // номинал купона (сумма скидки)
        'min_sum' => '',  // минимальная сумма заказа, когда можно применять купон
        'per_user' => 1,  // разрешённое число использований купона одним и тем же пользователем
        'active' => '', // флаг активности купона
        'in1c' => '', // знает ли о купоне 1с
        'from' => '',  // начало активности купoна (или null)
        'to' => '', // начало активности купона (или null)
        'uses' => '', // максимальное число использований купона
        'used' => '', // число использований купона (факт)
        'user_id' => '', // пользователь
        'max_sku' => 0, // для купона со скидкой в % - максимум товара одного наим, на который скидка
    ];

    protected $_has_many = [
        'goods' => [
            'model' => 'good',
            'through'    => 'z_coupon_good',
            'foreign_key'   => 'coupon_id',
            'far_key'   => 'good_id',
        ],
    ];

    protected $_belongs_to = [
        'user' => [
            'model' => 'user',
            'foreign_key'   => 'user_id',
        ],
    ];

    /**
     * Правила валидации для купона
     *
     */
    public function rules()
    {
        return [
            'name' => [
                ['not_empty'],
                ['max_length', [':value', 16]],
                [[$this, 'unique'], ['name', ':value']],
            ],
    /*        'sum' => [
                ['not_empty'],
            ],
    */        
            'per_user' => [
                ['not_empty'],
            ],
            'uses' => [
                ['not_empty'],
            ],
            'min_sum' => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * Возвращает строку с расшифровкой типа купона или массив всех типов
     */
    public static function type($type = FALSE)
    {
        $types = [
            self::TYPE_SUM      => 'Купон со скидкой на сумму',
            self::TYPE_PERCENT  => 'Купон со скидкой на процент на товары',
            self::TYPE_LK       => 'Купон дающий статус Любимый Клиент навсегда',
            self::TYPE_PRESENT  => 'Купон дающий подарок',
            self::TYPE_CHILD    => 'Купон со скидкой на ДР ребенка',
        ];

        if ($type === FALSE) return $types;
        return ! empty($types[$type]) ? $types[$type] : FALSE;
    }


    /**
     * Сгенерировать новый купон
     */
    public static function generate($sum, $min_sum = 0, $per_user = 1, $uses = 1, $user_id = 0, $type = self::TYPE_SUM, $from = NULL, $to = NULL)
    {
        $c = new self;
        if ($min_sum == 0) $min_sum = $sum + 1;
        do {
            $code = Text::random('distinct');

            $c->values([
                'name'      => $code,
                'per_user'  => $per_user,
                'uses'      => $uses,
                'user_id'   => $user_id,
                'sum'       => $sum,
                'min_sum'   => $min_sum,
                'type'      => $type,
                'from'      => $from,
                'to'        => $to,
                'active'    => 1
            ]);
        } while ( ! $c->validation()->check());

        $c->save();
        return $c;
    }

    /**
     * проверка, не просрочен ли купон
     */
    public function is_expired()
    {
        return ( ! empty($this->to)) && ($this->to < date('Y-m-d G:i:00'));
    }

    /**
     * проверка, стартовал ли купон
     */
    public function is_begun()
    {
        return (empty($this->from)) || ($this->from < date('Y-m-d G:i:00'));
    }

    /**
     * проверка что купон можно использовать
     */
    public function is_usable($sum = NULL)
    {
        if (Model_User::logged()) { // есть пользователь - посчитаем сколько купонов потратил

            if ($this->user_id > 0 && $this->user_id != Model_User::current()->id) { // купон именной и не на текущего юзера
                return FALSE;
            }

            $uses = ORM::factory('order')
                ->where('user_id', '=', Model_User::current()->id)
                ->where('coupon_id', '=', $this->id)
                ->where('status', '!=', 'X')
                ->count_all();

            if ($uses >= $this->per_user) return FALSE;
        }

        return $this->active && $this->is_begun() && ( ! $this->is_expired()) && $this->used < $this->uses && (is_null($sum) || $sum >= $this->min_sum);
    }

    /**
     * Добавить использование купону
     */
    public function used($user_id = FALSE)
    {
        $this->used = $this->used + 1;
        Model_History::log('coupon', $this->id, 'использование', [], $user_id);
        if (($user = Model_User::current()) && ($this->user_id == $user->id)) { // привязанный к юзеру купон - это за заполнение данных о детях

            if ($this->type == Model_Coupon::TYPE_SUM) {
                $user->child_discount = Model_User::CHILD_DISCOUNT_USED;
                $user->save();
            }
            if ($this->type == Model_Coupon::TYPE_CHILD) { // купон на др ребенка - считаем сколько всего использований
                $user->child_birth_discount += 1;
                $user->save();
            }

        }
        $this->save();
    }

    /**
     * Cнять использование купону
     */
    public function unused()
    {
        $this->used = $this->used - 1;
        $this->save();
    }

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return ['active', 'in1c'];
    }

    /**
     * Update coupon - set him as known by 1c
     * @return Database_Query
     */
    function in1c()
    {
        return DB::update('z_coupon')
            ->set(array('in1c' => 1))
            ->where('id', '=', $this->id)
            ->execute();
    }

    /**
     * Получить товары купона, сгруппировать по скидкам и минимальному кол-ву товара
     */
    function get_goods()
    {
        $return = [];

        $good_discount = DB::select('good_id', 'discount', 'min_qty')
            ->from('z_coupon_good')
            ->where('coupon_id', '=', $this->id)
            ->order_by('min_qty') // важно для правильного применения скидок если у одного товара есть их несколько при разном кол-ве
            ->order_by('discount', 'DESC')
            ->execute()
            ->as_array();

        if (empty($good_discount)) return $return;

        // собираем товары
        $idz = [];
        foreach($good_discount as $g) $idz[$g['good_id']] = $g['good_id'];

        $goods = ORM::factory('good')
            ->where('id', 'IN', $idz)
            ->find_all()
            ->as_array('id');

        // распихиваем по скидкам и кол-ву
        foreach($good_discount as $g) $return[$g['discount']][$g['min_qty']][$g['good_id']] = $goods[$g['good_id']];

        return $return;
    }

    /**
     * сохранение купона в админке - надо крепить товары или подарки
     */
    function admin_save()
    {
        if ($this->type == self::TYPE_PERCENT) { //Прикрепить товары к купону типа TYPE_PERCENT
            $goods = Request::current()->post('goods');
            if ( ! is_array($goods)) $goods = [];

            $old_good_idz = [];
            $old_goods = $this->get_goods();

            foreach($old_goods as $discount => $qty_goodz) {
                foreach($qty_goodz as $min_qty => $goodz) {
                    foreach($goodz as $id => $good) {
                        $old_good_idz[$id] = [$discount, $min_qty]; // запоминаем скидку и товар
                    }
                }
            }

            $add_disc = Request::current()->post('misc');

            // чистим все товары и добавляем согласно списку, заодно собираем id => [discount, min_qty] для полученных из POST
            DB::delete('z_coupon_good')->where('coupon_id', '=', $this->id)->execute();
            $new_good_idz = [];
            foreach($goods as $discount => $qty_goodz) {
                foreach($qty_goodz as $min_qty => $goodz) {
                    $idz = array_unique($goodz);
                    if ( ! empty($idz)) {
                        $ins = DB::insert('z_coupon_good')->columns(['good_id', 'coupon_id', 'discount', 'min_qty']);
                        if ($discount == 0) $discount = ! empty($add_disc['discount']) ? $add_disc['discount'] : 0;
                        if ($min_qty == 0) $min_qty = ! empty($add_disc['min_qty']) ? $add_disc['min_qty'] : 1;
                        foreach ($idz as $id) {
                            $ins->values([
                                'good_id' => $id,
                                'coupon_id' => $this->id,
                                'discount' => $discount,
                                'min_qty' => $min_qty,
                            ]);
                            $new_good_idz[$id] = [$discount, $min_qty];
                        }
                        DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE ', $ins))->execute();
                    }
                }
            }

            $goods_del = array_diff_assoc($old_good_idz, $new_good_idz); // Удалены товары или смена скидки/кол-ва
            $goods_new = array_diff_assoc($new_good_idz, $old_good_idz); // Добавлены товары или смена скидки
            if ( ! empty($goods_new)) {
                Model_History::log('coupon', $this->id, 'Добавлено товаров: '.count($goods_new), $goods_new);
            }
            if ( ! empty($goods_del)) {
                Model_History::log('coupon', $this->id, 'Удалено товаров: '.count($goods_del), $goods_del);
            }
        }
        if ($this->type == self::TYPE_PRESENT) {
            $presents = Request::current()->post('misc');
            if ( ! empty($presents['presents'])) {
                $presents = array_unique($presents['presents']);
            } else {
                $presents = [];
            }
            $presents = array_filter($presents);

            // старый список подарков
            $old_presents = DB::select('good_id')
                ->from('z_coupon_good')
                ->where('coupon_id', '=', $this->id)
                ->execute()
                ->as_array('good_id', 'good_id');

            $old_presents = array_values($old_presents);
            sort($old_presents);
            sort($presents);

            if ($old_presents != $presents) {
                // чистим все подарки и потом добавляем заново
                DB::delete('z_coupon_good')->where('coupon_id', '=', $this->id)->execute();

                if ( ! empty($presents)) {
                    $ins = DB::insert('z_coupon_good')->columns(['good_id', 'coupon_id', 'discount']);
                    foreach ($presents as $id) {
                        $ins->values([
                            'good_id' => $id,
                            'coupon_id' => $this->id,
                            'discount' => 1,
                        ]);
                    }
                    DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE ', $ins))->execute();
                }

                Model_History::log('coupon', $this->id, 'Подарки ' . count($presents), $presents);
            }
        }
        return;
    }

    /**
     * Поиск неиспользованного купона для юзера по типу
     * @param $user_id
     * @param $type
     * @return bool|ORM
     */
    static function for_user($user_id, $type = self::TYPE_SUB)
    {
        $coupon = self::factory('coupon')
            ->where('user_id', '=', $user_id)
            ->where('type', '=', $type)
            ->where('used', '=', 0)
            ->where('active', '=', 1)
            ->find();

        if ( ! $coupon->loaded()) return FALSE;
        if ( ! $coupon->is_begun()) return FALSE;
        if ( $coupon->is_expired()) return FALSE;

        return $coupon;
    }
}
