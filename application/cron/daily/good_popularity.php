<?php

require('../../../www/preload.php');

// заказы за последний 31 день
$oIds = DB::select('id')
    ->from('z_order')
    ->where('status', '=', 'F')
    ->where('status_time', '>=', date('Y-m-d H:i:s', time() - 31 * 24 * 60 * 60))
    ->execute()
    ->as_array('id', 'id');

// популярность - это число заказанных товаров в штуках за период
if ( ! empty($oIds)) {

	$goods = DB::select('good_id', DB::expr('SUM(quantity) as popularity'))
        ->from('z_order_good')
        ->where('order_id', 'IN', $oIds)
        ->group_by('good_id')
        ->order_by('popularity')
        ->execute()
        ->as_array('good_id', 'popularity');

	if ( ! empty($goods)) {
		
		DB::update('z_good')->set(['popularity' => 0])->execute();

        $ins = DB::insert('z_good', ['id', 'popularity']);
		foreach($goods as $id => $pop) {
            $ins->values(['id' => $id, 'popularity' => $pop]);
        }
		DB::query(Database::INSERT, $ins.' ON DUPLICATE KEY UPDATE popularity = VALUES(popularity)')->execute();
	}
}
