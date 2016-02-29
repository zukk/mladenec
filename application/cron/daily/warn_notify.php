<?php

require('../../../www/preload.php');

$result = DB::query(Database::SELECT, "SELECT good_id FROM z_warn GROUP BY good_id")->execute();

while ($row = $result->current()) {

	$warns = ORM::factory('good_warn')
		->with('user')
		->with('good')
		->where('notified','=','0')
		->where('timemark', '<=', date('Y-m-d', time()-24*3600*7))
		->where('good_id', '=', $row['good_id'])
		->find_all()
		->as_array();

	if ( ! empty($warns)) {
		$warns_send = 0;
		foreach($warns as $w) {
			if ($w->notify()){
				$warns_send++;
			}
		}
		Log::instance()->add(LOG::INFO, 'Send '.$warns_send." notifies for good ".$row['good_id']);
	}
	
	$row = $result->next();
}
