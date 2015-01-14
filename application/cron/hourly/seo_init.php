<?php

require('../../../www/preload.php');

$tables = [
	'article' => 1,
	'section' => 3,
	'good_prop' => 4,
	'tag' => 2,
	'brand' => 5,
	'new' => 6,
	'action' => 7
];

$fields = [
	'article' => ['title', 'description'],
	'section' => ['title', 'keywords', 'description'],
	'good_prop' => ['title', 'description'],
	'tag' => ['title', 'keywords', 'description'],
	'brand' => ['description'],
	'new' => ['title', 'description'],
];

foreach( $tables as $modelName => $type ){

	if( !empty( $fields[$modelName ] ) )
		$fl = $fields[$modelName ];
	elseif( $modelName == 'action' ){
		$fl = ['name', 'preview'];
	}
	else
		$fl = [];
	
	$sql_fl = implode( ' , ', $fl );
	
	if( !empty( $sql_fl ) )
		$sql_fl = ', ' . $sql_fl;
	
	$result = DB::query(Database::SELECT, "SELECT id " . $sql_fl . " FROM z_$modelName /* WHERE title != '' OR description != '' */")->execute();
	$oIds = array();
	while( $c = $result->current() ){

		if( $modelName == 'action' ){
			
			$sql = "title = '" . str_replace( "'", '', $c['name'] ) . "', description = '" . str_replace( "'", '', $c['preview'] ) . "'";
		}
		else{
			
			$sql = [];
			foreach( $fl as $field ){
				$sql[] = "$field = '" . str_replace( "'", '', $c[$field] ) . "'";
			}
			$sql = implode( ' , ', $sql );
		}
		
		DB::query(Database::INSERT, "INSERT INTO z_seo SET $sql, item_id = '" . $c['id'] . "', type = " . $type . "")->execute();
		
		$result->next();
	}
	
}