<?php

require('../../../www/preload.php');

DB::query(Database::INSERT, "
CREATE TABLE IF NOT EXISTS `z_good_good_temp` (
  `max_good_id` INT UNSIGNED NOT NULL ,
  `min_good_id` INT UNSIGNED NOT NULL ,
  `qty` SMALLINT UNSIGNED NOT NULL DEFAULT  '0',
  PRIMARY KEY (  `max_good_id` ,  `min_good_id` )
) ENGINE = INNODB;
")->execute();

$result = DB::query(Database::SELECT, "SELECT id FROM z_order WHERE status = 'F' AND status_time >= '" . date('Y-m-d H:i:s', time()-15552000) . "'")->execute();
$oIds = array();
while( $c = $result->current() ){
	
	$oIds[] = $c['id'];
	$result->next();
}

if( !empty( $oIds ) ){

	$result = DB::query(Database::SELECT, "SELECT good_id, order_id FROM z_order_good WHERE order_id IN( " . implode( ', ', $oIds ) . ") ")->execute();

	$data = array();
	$i = 0;
	while( $c = $result->current() ){
		
		$data[$c['order_id']][] = $c['good_id'];
		
		$result->next();
	}

	foreach( $data as $goods ){
		
		if( count( $goods ) > 1 ){
			
			$sql = "INSERT INTO z_good_good_temp (`max_good_id`, `min_good_id`, `qty`) VALUES ";

			foreach( $goods as $i => $gId ){

				$bind = array();
				foreach( $goods as $j => $gId2 ){

					if( $i != $j ){
						$bind[] = "($gId, $gId2, 1)";
					}
				}

			}

			$sql .= implode( ' , ', $bind);
			$sql .= " ON DUPLICATE KEY UPDATE qty = qty + 1";
			DB::query(Database::INSERT, $sql)->execute();
		}
	}

	DB::query(Database::DELETE, "TRUNCATE TABLE z_good_good")->execute();
	DB::query(Database::INSERT, "INSERT INTO z_good_good SELECT * FROM z_good_good_temp")->execute();
    DB::query(Database::DELETE, "DROP TABLE z_good_good_temp")->execute();
}
