<?php

require('../../../www/preload.php');

$Good = ORM::factory('good');

$result = DB::query(Database::SELECT, "SELECT id FROM z_order WHERE status  = 'F' AND status_time >= '" . date('Y-m-d H:i:s', time()-7776000) . "' ")->execute();

$oIds = array();

while( $c = $result->current() ){

	$oIds[] = $c['id'];
	$result->next();
}

if( !empty( $oIds ) ){

	$result = DB::query(Database::SELECT, "SELECT good_id, COUNT(order_id) AS count FROM z_order_good WHERE order_id IN ( " . implode( ', ', $oIds ) . ") GROUP BY good_id ORDER BY count DESC")->execute();
	
	$goods = array();
	while( $c = $result->current() ){
		
		$goods[$c['good_id']] = $c['count'];
		$result->next();
	}
	
	if( !empty( $goods ) ){
		
		DB::query(Database::UPDATE, "UPDATE z_good SET popularity = 0")->execute();
		
		$exist = array();
		
		$result = DB::query(Database::SELECT, "SELECT id FROM z_good WHERE id IN( " . implode( ', ', array_keys( $goods ) ) . ") ")->execute();
		
		while( $c = $result->current() ){
			
			$exist[$c['id']] = 1;
			$result->next();
		}

		$sql = 'INSERT INTO z_good (id, popularity) VALUES ';
		
		$to_update = array();
		foreach( $goods as $good_id => $popularity ){
			
			if( !isset( $exist[$good_id] ) ){
				
				Log::instance()->add(Log::NOTICE, 'not found good ' . $good_id);
				continue;
			}
			
			$to_update[] = "( '" . $good_id . "', '" . $popularity . "' )";
		}
		
		$sql .= implode( ', ', $to_update );
		$sql .= '  ON DUPLICATE KEY UPDATE popularity = VALUES(popularity);';
		DB::query(Database::UPDATE, $sql)->execute();
	}
}
