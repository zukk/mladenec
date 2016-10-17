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

if ($argc != 2) fail('Usage: copy.php ORDER_ID');

$order_id = $argv[1];

$o = new Model_Order($order_id);
if ( ! $o->loaded()) fail('Order not found: '.$order_id);

// очищаем данные заказа от статуса и id, делаем его с оплатой картой на сумму доплаты
$oarr = $o->as_array();
unset($oarr['id']);
$oarr['status'] = 'N';
$oarr['pay_type'] = Model_Order::PAY_CARD;
$oarr['can_pay'] = 1;
$oarr['payment'] = $o->get_total();

$o_copy = new Model_Order();
$o_copy->values($oarr);
$o_copy->save();

$odarr = $o->data->as_array();

$od_copy = new Model_Order_Data($o_copy->id);
$od_copy->values($odarr);
$od_copy->id = $o_copy->id;
$od_copy->save();

DB::insert('z_order_good', ['order_id', 'good_id', 'price', 'quantity'])
    ->select(DB::select(DB::expr($o_copy->id), 'good_id', 'price', 'quantity')
        ->from('z_order_good')
        ->where('order_id', '=', $o->id)
    )
    ->execute();

fail('Created order '.$o_copy->id.' as a copy of '.$order_id);