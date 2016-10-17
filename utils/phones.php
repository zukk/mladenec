<?php

require_once('../www/preload.php');

ob_end_clean();

DB::query(Database::UPDATE, "TRUNCATE z_user_phone")->execute();
		
$result = DB::query(Database::SELECT, "SELECT o.id, d.phone, o.user_id FROM z_order_data AS d, z_order AS o WHERE o.id = d.id AND user_id > 0 GROUP BY d.phone")->execute();

$inserted = 0;
while( $row = $result->current() ){
	
	list( $id ) = DB::query(Database::INSERT, "INSERT INTO z_user_phone (`user_id`, `phone`) VALUES ('$row[user_id]', '$row[phone]')")->execute();
	DB::query(Database::UPDATE, "UPDATE z_user SET phone_active = $id WHERE id = $row[user_id]")->execute();
	
	$inserted++;
	$result->next();
}

echo "inserted " . $inserted . " phones";