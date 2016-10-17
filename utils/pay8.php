<?php

/**
 * проставить заказам pay8 и pay1, а также оплаченную сумму
 */

require('../www/preload.php');

$data = DB::select('id', 'payment', 'price', 'price_ship')
    ->from('z_order')
    ->where('payment', '>', '')
    ->where('pay_type', '=', 8)
    ->order_by('id')
    ->execute()
    ->as_array('id');

foreach($data as $id => $d) {
    $payment = json_decode($d['payment'], TRUE);
    $pay8 = $pay1 = 0;
    if (is_array($payment)) {
        if ( ! empty($payment['ОПЛАТА:8'])) {
            $pay8 = $payment['ОПЛАТА:8'];
        }
        if ( ! empty($payment['ОПЛАТА:1'])) {
            $pay1 = $payment['ОПЛАТА:1'];
        }
        DB::update('z_order')
            ->set(['pay8' => $pay8, 'pay1' => $pay1, 'payment' => ''])
            ->where('id', '=', $id)
            ->execute();
        echo $id. " updated\n";
    }
}

