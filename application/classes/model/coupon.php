<?php
// скидочные купоны
class Model_Coupon extends ORM {

    const TYPE_SUM = 0; // купон со скидкой на сумму от суммы
    const TYPE_PERCENT = 1; // купон со скдкой на процент на часть товаров
    const TYPE_LK = 2; // купон даёт ЛК

    const CHILD_DISCOUNT = 'kidz'; // скрытый купон со скидкой за данные о детях/беременности

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
        'used' => '', // число использований купона
    ];

    protected $_has_many = [
        'goods' => [
            'model' => 'good',
            'through'    => 'z_coupon_good',
            'foreign_key'   => 'coupon_id',
            'far_key'   => 'good_id',
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
            0 => 'Купон со скидкой на сумму',
            1 => 'Купон со скидкой на процент на товары',
            2 => 'Купон дающий статус Любимый Клиент навсегда',
        ];

        if ($type === FALSE) return $types;
        return ! empty($types[$type]) ? $types[$type] : FALSE;
    }


    /**
     * Сгенерировать новый купон
     */
    public static function generate($sum, $min_sum = 0, $per_user = 1, $uses = 1)
    {
        $c = new self;
        if ($min_sum == 0) $min_sum = $sum + 1;
        do {
            $code = Text::random('distinct');

            $c->values(array(
                'name'      => $code,
                'per_user'  => $per_user,
                'uses'      => $uses,
                'sum'       => $sum,
                'min_sum'   => $min_sum,
                'active'    => 1
            ));
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
        if (($user = Model_User::current()) && ($this->name == Model_Coupon::CHILD_DISCOUNT)) {
            $user->child_discount = Model_User::CHILD_DISCOUNT_USED;
            $user->save();
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
     * Получить товары купона, сгруппировать по скидкам
     */
    function get_goods()
    {
        $return = [];

        $good_discount = DB::select('good_id', 'discount')
            ->from('z_coupon_good')
            ->where('coupon_id', '=', $this->id)
            ->order_by('discount', 'DESC')
            ->execute()
            ->as_array('good_id', 'discount');

        if (empty($good_discount)) return $return;

        $goods = ORM::factory('good')
            ->where('id', 'IN', array_keys($good_discount))
            ->find_all()
            ->as_array('id');

        foreach($good_discount as $id => $discount) {
            $return[$discount][$id] = $goods[$id];
        }
        return $return;
    }

    /**
     * Прикрепить товары к купону типа TYPE_PERCENT
     */
    function admin_save()
    {
        if ($this->type == self::TYPE_PERCENT) {
            $goods = Request::current()->post('goods');
            if ( ! is_array($goods)) $goods = [];

            $old_good_idz = [];
            $old_goods = $this->get_goods();

            foreach($old_goods as $discount => $goodz) {
                foreach($goodz as $id => $good) $old_good_idz[$id] = $discount; // запоминаем скидку и товар
            }

            $add_disc = Request::current()->post('misc');

            // чистим все товары и добавляем согласно списку, заодно собираем id => discount для полученных из POST
            DB::delete('z_coupon_good')->where('coupon_id', '=', $this->id)->execute();
            $new_good_idz = [];
            foreach($goods as $discount => $goodz) {
                $idz = array_unique($goodz);
                if ( ! empty($idz)) {
                    $ins = DB::insert('z_coupon_good')->columns(['good_id', 'coupon_id', 'discount']);
                    if ($discount == 0) $discount = ! empty($add_disc['discount']) ? $add_disc['discount'] : 0;
                    foreach($idz as $id) {
                        $ins->values([
                            'good_id' => $id,
                            'coupon_id' => $this->id,
                            'discount' => $discount,
                        ]);
                        $new_good_idz[$id] = $discount;
                    }
                    DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE ', $ins))->execute();
                }
            }

            $goods_del = array_diff_assoc($old_good_idz, $new_good_idz); // Удалены товары или смена скидки
            $goods_new = array_diff_assoc($new_good_idz, $old_good_idz); // Добавлены товары или смена скидки
            if ( ! empty($goods_new)) {
                Model_History::log('coupon', $this->id, 'Добавлено товаров: '.count($goods_new), $goods_new);
            }
            if ( ! empty($goods_del)) {
                Model_History::log('coupon', $this->id, 'Удалено товаров: '.count($goods_del), $goods_del);
            }
        }
        return;
    }
}
