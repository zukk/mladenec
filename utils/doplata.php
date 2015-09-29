<?php

/**
 * создать искусственный товар на сумму доплаты S к заказу O
 * c оплатой безналом для
 */

require('../www/preload.php');

function fail($text)
{
    exit($text."\n\n");
}

if ($argc != 3) fail('Usage: doplata.php ORDER_ID SUM');

$order_id = $argv[1];
$sum = floatval($argv[2]);

if ( $sum <= 0) fail('Bad sum value');

$o = new Model_Order($order_id);
if ( ! $o->loaded()) fail('Order not found: '.$order_id);

$g = new Model_Good();
$g->name = 'Оплата заказа '.$order_id;
$g->code = $order_id.'plus';
$g->price = $sum;
$g->save();

// очищаем данные заказа от статуса и id, делаем его с оплатой картой на сумму доплаты
$oarr = $o->as_array();
unset($oarr['id']);
$oarr['status'] = 'N';
$oarr['pay_type'] = Model_Order::PAY_CARD;
$oarr['can_pay'] = 1;
$oarr['price'] = $sum;
$oarr['payment'] = $sum;
$oarr['price_ship'] = 0;

$o_copy = new Model_Order();
$o_copy->values($oarr);
$o_copy->save();

$odarr = $o->data->as_array();

$od_copy = new Model_Order_Data($o_copy->id);
$od_copy->values($odarr);
$od_copy->id = $o_copy->id;
$od_copy->save();

DB::insert('z_order_good', ['order_id', 'good_id', 'price', 'quantity'])
    ->values([$o_copy->id, $g->id, $sum, 1])
    ->execute();

fail('Created order '.$o_copy->id.' sum '.$sum);