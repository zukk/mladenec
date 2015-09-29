<?php

require('../../../../www/preload.php');

$result = DB::query(Database::SELECT, "SELECT id, name FROM z_section WHERE active = 1 LIMIT 1")->execute();
$s = 0;
while( $c = $result->current() ){
	break;
	$d = json_decode( file_get_contents('http://export.yandex.ru/inflect.xml?name=' . urlencode($c['name']) . '&format=json'), true );
	DB::query(Database::UPDATE, "UPDATE z_section SET roditi = '$d[2]' WHERE id = $c[id]")->execute();
	$result->next();
	$s++;
}

echo 'updated ' . $s . ' sections' . "\n";

$result = DB::query(Database::SELECT, "SELECT id, name FROM z_filter_value LIMIT 1")->execute();
$s = 0;
while( $c = $result->current() ){

	$d = json_decode( file_get_contents('http://export.yandex.ru/inflect.xml?name=' . urlencode($c['name']) . '&format=json'), true );
	
	if( !empty( $d[2] ) ) {
		
		DB::query(Database::UPDATE, "UPDATE z_filter_value SET roditi = '$d[2]' WHERE id = $c[id]")->execute();
		$s++;
	}
	$result->next();
}

echo 'updated ' . $s . ' values' . "\n";
