<?php

/**
 * анализ начатых сеансов оплаты на предмет текущего состояния платежа
 */

require('../../../www/preload.php');

$lock_file = APPPATH.'cache/payment_on';

if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);

touch($lock_file);

$payment = ORM::factory('payment')
    ->where('order_id', '>', 400000)
    ->where('status', '<', Model_Payment::STATUS_Authorized)
    ->where('session_id', '!=', '')
    ->where(DB::expr('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(status_time)'), '>', 1200)
    // проверяем только заказы которые проверяли больше 20 минут назад
    ->find_all()
    ->as_array('order_id'); // все новые сеансы оплаты
foreach($payment as $k => $p) $p->status();

// проверка снятия денег по доставленным заказам - этим заказам надо снять деньги всем!
// рефунд можно сделать только вручную из личного кабинета - так что его не учитываем
$payment = DB::query(Database::SELECT, "
    SELECT order_id, payment FROM z_payment
        JOIN z_order ON (z_order.id = order_id AND z_order.status = 'F')
    WHERE order_id > 400000
        AND session_id != ''
        AND z_payment.status != ".Model_Payment::STATUS_ChargeApproved."
        AND z_payment.status != ".Model_Payment::STATUS_Refunded
)->execute()->as_array();

foreach($payment as $order_id) {
    $p = ORM::factory('payment', $order_id['order_id']);
    $status = $p->status(); // проверяем статус - может сняли деньги вручную через шлюз

    if ($status != Model_Payment::STATUS_ChargeApproved) {
        $topay = json_decode($order_id['payment'], 1);
        $rouble = $topay['ОПЛАТА:8'];
        if ($rouble > 0) $p->charge($rouble * 100);
    }
}

unlink($lock_file);


