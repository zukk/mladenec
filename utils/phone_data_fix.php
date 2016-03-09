<?php

/**
 * исправить формат телефонов в таблице z_order_data
 */

require('../www/preload.php');

$phones = DB::select('id', 'phone')
    ->from('z_order_data')
    ->where('id', '>', 500000)
    ->where('phone', 'NOT REGEXP', '7[0-9]{10}')
    ->execute()
    ->as_array('id', 'phone');

$updated = 0;

// сначала пытаемся исправить те телефоны что есть в phone
foreach($phones as $id => $phone) {

    $clear_phone = Txt::phone_clear($phone);
    if ( ! $clear_phone) {
        echo 'NOT clear '.$phone."\n";
        continue;
    }

    DB::update('z_order_data')
        ->set(['phone' => $clear_phone])
        ->where('id', '=', $id)
        ->execute();

    $updated++;

    echo $id . ":" . $phone . ":" . $clear_phone . "\n";
}
echo 'Updated '.$updated."\n\n";

// у кого не удалось но есть второй хороший телефон - ставим второй первым
var_dump(DB::update('z_order_data')
    ->set(['phone' => DB::expr('mobile_phone')])
    ->where('id', '>', 500000)
    ->where('phone', 'NOT REGEXP', '7[0-9]{10}')
    ->where('mobile_phone', 'REGEXP', '7[0-9]{10}')
    ->execute());

// у кого и второй телефон плохой - проставим из данных пользователя
var_dump(DB::query(Database::UPDATE, "update z_order_data od, z_order o, z_user u set od.phone = u.phone where od.id = o.id and o.user_id = u.id and od.id > 500000 and od.phone not regexp '7[0-9]{10}'")->execute());

// теперь поработаем со вторым телефоном
$phones = DB::select('id', 'mobile_phone')
    ->from('z_order_data')
    ->where('id', '>', 500000)
    ->where('mobile_phone', '>', '')
    ->where('mobile_phone', 'NOT REGEXP', '7[0-9]{10}')
    ->execute()
    ->as_array('id', 'mobile_phone');

$updated = 0;

// сначала пытаемся исправить те телефоны что есть в mobile_phone
foreach($phones as $id => $phone) {

    $clear_phone = Txt::phone_clear($phone);
    if ( ! $clear_phone) {
        echo 'NOT clear '.$phone."\n";
        continue;
    }
    if ( ! Txt::phone_is_mobile($clear_phone)) {
        echo 'NOT mobile '.$phone."\n";
        continue;
    }

    DB::update('z_order_data')
        ->set(['mobile_phone' => $clear_phone])
        ->where('id', '=', $id)
        ->execute();

    $updated++;

    echo $id . ":" . $phone . ":" . $clear_phone . "\n";
}
echo 'Updated '.$updated."\n\n";

// если второй телефон плохой, а первый хороший и мобильный  - сделаем второй копией первого
var_dump(DB::query(Database::UPDATE, "update z_order_data set mobile_phone = phone where id > 500000 and mobile_phone > '' and mobile_phone not regexp '7[0-9]{10}' and phone > '' and phone regexp '7[0-9]{10}'")->execute());

// если второй телефон плохой а первого нет - то сотрем и второй
var_dump(DB::query(Database::UPDATE, "update  z_order_data set mobile_phone = '' where id > 500000 and mobile_phone > '' and mobile_phone not regexp '7[0-9]{10}'")->execute());