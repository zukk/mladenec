<?php
// скидочные купоны
class Model_Coupon extends ORM {

    protected $_table_name = 'z_coupon';

    protected $_table_columns = array(
        'id' => '',
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
    );

    /**
     * Сгенерировать новый купон
     */
    public static function generate($code)
    {
        $c = new self;

        $c->values(array(
            'name' => $code,
            'per_user' => 1,
            'uses' => 1,
            'sum' => 100,
            'min_sum' => 2000
        ))->save();

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

        return $this->in1c && $this->active && $this->is_begun() && ( ! $this->is_expired()) && $this->used < $this->uses && (is_null($sum) || $sum >= $this->min_sum);
    }

    /**
     * Добавить использование купону
     */
    public function used()
    {
        $this->used = $this->used + 1;
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
        return array('active', 'in1c');
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

}
