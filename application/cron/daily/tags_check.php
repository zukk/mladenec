<?php

/**
 * проверяет, когда теговая стала пустой
 */
require('../../../www/preload.php');

ob_end_clean();
 
$logFilename = APPPATH . 'cache/tags_log.txt';

DB::query(Database::UPDATE, "UPDATE z_tag SET filter_not_exists = 0")->execute();

$result = DB::query(Database::SELECT, "SELECT id, name, goods_count FROM z_tag")->execute();

Request::$current = Request::factory(false);

$emptyTags = [];
while( $row = $result->current() ){
	
	$sphinx = new Sphinx('tag', $row['id'], FALSE);
	$sphinx->search();
	
	$count = 0;
	if( ! empty( $sphinx->goods ) ){
		$count = $sphinx->pager->total;
	} else {
		if( $row['goods_count'] != 0 ){
			$emptyTags[$row['id']] = $row['name'];
			DB::query(Database::UPDATE, "UPDATE z_tag SET goods_empty_ts = '" . time() . "' WHERE id = " . $row['id'])->execute();
			Model_History::log('tag', $row['id'], 'tag is empty');
		}
	}
	
	DB::query(Database::UPDATE, "UPDATE z_tag SET goods_count = '$count', goods_count_ts = '" . time() . "' WHERE id = " . $row['id'])->execute();

	// проверка на несуществующие фильтры
	$tag = ORM::factory('tag', $row['id']);

		$blocks = explode( ',', $tag->params );

		$filtersIds = [];
		foreach( $blocks as $block ){
		
			if( empty( $block ) ){
				continue;
			}
			
			list( $name, $value ) = explode( '=', $block );
			
			// Это фильтр
			if( $name > 0 ){
				$filtersIds[(int)$name] = (int)$name;
			}
		}
		
		if( !empty( $filtersIds ) ){
			
			$items = ORM::factory('filter')->select('id')->where('id', 'IN', $filtersIds )->find_all()->as_array('id');

			foreach( $items as $id => $v ){
				unset( $filtersIds[$id] );
			}

			if( !empty( $filtersIds ) ){
				echo "bad params for tag " . $row['id'] . "\n";
				DB::query(Database::UPDATE, "UPDATE z_tag SET filter_not_exists = 1 WHERE id = $row[id]")->execute();
			}
		}
	
	$result->next();
}


if( ! empty( $emptyTags ) ){
	
	$to = "a.melnikov@mladenec.ru";
    Mail::htmlsend('empty_tags', array('tags' => $emptyTags), $to, 'Информация о теговых, ставших сегодня пустыми');
	
	if( $file = fopen( $logFilename, 'a+' ) ){
		
		foreach( $emptyTags as $id => $title ){
			
			fwrite($file, $id . ' ' . $title . ' ' . date('Y-m-d') . "\r\n");
		}
		
		fclose( $file );
	}
}

echo "empty tags: " . count($emptyTags) . "\n";