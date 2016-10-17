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
    ->as_array('id'); // все новые сеансы оплаты
foreach($payment as $k => $p) $p->status();

// проверка снятия денег по доставленным заказам - этим заказам надо снять деньги всем!
// рефунд можно сделать только вручную из личного кабинета - так что его не учитываем
$payment = ORM::factory('payment')
    ->with('order')
        ->where('order.status', '=', 'F')
        ->where('order_id', '>', 400000)
    ->where('payment.session_id', '!=', '')
    ->where('payment.status', 'NOT IN', [Model_Payment::STATUS_Voided, Model_Payment::STATUS_Rejected, Model_Payment::STATUS_ChargeApproved, Model_Payment::STATUS_Refunded])
    ->find_all()
    ->as_array();

foreach($payment as $p) {
    $status = $p->status(); // проверяем статус - может сняли деньги вручную через шлюз

    if ($status != Model_Payment::STATUS_ChargeApproved) {
        if ($p->sum) $p->charge($p->sum);
    }
}

unlink($lock_file);


