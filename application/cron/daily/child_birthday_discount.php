<?php
/**
 * Проверка на скидку за день рождения детей
 * делать утром раз в день
 */

require('../../../www/preload.php');

// ------------   1. проверяем у кого из детей день рождения через неделю

$bd = strtotime('+7 days');

$kids = DB::select('user_id')
    ->from('z_user_child')
    ->where(DB::expr('MONTH(birth)'), '=', date('m', $bd))
    ->where(DB::expr('DAY(birth)'), '=', date('d', $bd))
    ->execute()
    ->as_array();

if (empty($kids)) exit('no kids');

$users = ORM::factory('user')
    ->where('sub', '=', 1)
    //->where('email_approved', '=', 1)
    ->where('id', 'IN', $kids)
    ->find_all()
    ->as_array('id');

if (empty($users)) exit('no subscribed users');

//  у кого нет купона - создаем, у кого есть - передаем тот что есть
$coupons = ORM::factory('coupon')
    ->where('type', '=', Model_Coupon::TYPE_CHILD)
    ->where('used', '=', 0)
    ->where('user_id', 'IN', $kids)
    ->find_all()
    ->as_array('user_id');

echo implode(',', array_keys($users))."\n";
foreach($users as $u) {

    if (empty($coupons[$u->id])) {
        // генерим новый детский купон на две недели
        $coupon = Model_Coupon::generate(0, 1, 1, 1, $u->id, Model_Coupon::TYPE_CHILD, date('Y-m-d'), date('Y-m-d', strtotime('+7 days', $bd)));
    }  else {
        $coupon = $coupons[$u->id]; // если есть не использованный детский купон - сдвигаем сроки
        $coupon->from = date('Y-m-d');
        $coupon->to = strtotime('+7 days', $bd);
    }

    if ($u->can_sub()) { // оповещение getresponse

        $quest = new Model_Daemon_Quest();
        $quest->values([
            'action'    => 'getresponse',
            'params'    => json_encode([
                'user' => $u->as_array(),
                'customs' => [
                    [
                        'name' => 'coupon',
                        'content' => $coupon->name,
                    ],
                    [
                        'name' => 'child_birth_discount',
                        'content' => Model_User::CHILD_BIRTH_BEFORE,
                    ],
                ],
            ])
        ]);
        $quest->save();
        Daemon::new_task();
    }
}

// ------------   2. проверяем у кого из детей день рождения сегодня

$bd = time();

$kids = DB::select('user_id')
    ->from('z_user_child')
    ->where(DB::expr('MONTH(birth)'), '=', date('m', $bd))
    ->where(DB::expr('DAY(birth)'), '=', date('d', $bd))
    ->execute()
    ->as_array();

if (empty($kids)) exit('no kids');

$users = ORM::factory('user')
    ->where('sub', '=', 1)
    ->where('id', 'IN', $kids)
    ->find_all()
    ->as_array('id');

if (empty($users)) exit('no subscribed users');

// есть неиспользованные купоны
$coupons = ORM::factory('coupon')
    ->where('type', '=', Model_Coupon::TYPE_CHILD)
    ->where('used', '=', 0)
    ->where('user_id', 'IN', $kids)
    ->find_all()
    ->as_array('user_id');

echo implode(',', array_keys($users))."\n";
foreach($users as $u) {

    if ( ! empty($coupons[$u->id])) {
        $coupon = $coupons[$u->id]->name; // если есть не использованный детский купон
    } else {
        $coupon = NULL;
    }

    if ($u->can_sub()) { // оповещение getresponse

        $quest = new Model_Daemon_Quest();
        $quest->values([
            'action'    => 'getresponse',
            'params'    => json_encode([
                'user' => $u->as_array(),
                'customs' => [
                    [
                        'name' => 'coupon',
                        'content' => $coupon,
                    ],
                    [
                        'name' => 'child_birth_discount',
                        'content' => Model_User::CHILD_BIRTH_TODAY,
                    ],
                ],
            ])
        ]);
        $quest->save();
        Daemon::new_task();
    }
}

// ------------   3. проверяем у кого из детей день рождения был 5 дней назад

$bd = strtotime('-5 days');

$kids = DB::select('user_id')
    ->from('z_user_child')
    ->where(DB::expr('MONTH(birth)'), '=', date('m', $bd))
    ->where(DB::expr('DAY(birth)'), '=', date('d', $bd))
    ->execute()
    ->as_array();

if (empty($kids)) exit('no kids');

$users = ORM::factory('user')
    ->where('sub', '=', 1)
    ->where('id', 'IN', $kids)
    ->find_all()
    ->as_array('id');

if (empty($users)) exit('no subscribed users');

// есть неиспользованные купоны
$coupons = ORM::factory('coupon')
    ->where('type', '=', Model_Coupon::TYPE_CHILD)
    ->where('used', '=', 0)
    ->where('user_id', 'IN', $kids)
    ->find_all()
    ->as_array('user_id');

echo implode(',', array_keys($users))."\n";
foreach($users as $u) {

    if ( ! empty($coupons[$u->id])) {
        $coupon = $coupons[$u->id]->name; // если есть не использованный детский купон

        if ($u->can_sub()) { // оповещение getresponse

            $quest = new Model_Daemon_Quest();
            $quest->values([
                'action' => 'getresponse',
                'params' => json_encode([
                    'user' => $u->as_array(),
                    'customs' => [
                        [
                            'name' => 'coupon',
                            'content' => $coupon,
                        ],
                        [
                            'name' => 'child_birth_discount',
                            'content' => Model_User::CHILD_BIRTH_AFTER,
                        ],
                    ],
                ])
            ]);
            $quest->save();
            Daemon::new_task();
        }
    }
}