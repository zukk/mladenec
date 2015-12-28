<?php

/**
 * сгенерировать детский промокод для тех кто заполнил данные о детях но не использовал kidz
 * запускать один раз, иначе продублирует купоны
 */

require('../www/preload.php');

$users = ORM::factory('user')
    ->where('child_discount', '=', Model_User::CHILD_DISCOUNT_ON)
    ->find_all()
    ->as_array();

foreach($users as $u) {
    $coupon = Model_Coupon::generate(200, 201, 1, 1, $u->id);
}


