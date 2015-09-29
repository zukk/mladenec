<?php

require('../../../../www/preload.php');

$file = fopen( './headers.csv', 'r' );

$i = 0;
while( $s = fgets( $file ) ){
	
	list( $url, $title ) = explode( ';', $s );
	
	$title = trim( $title );
	
	$item = ORM::factory('section')->where('translit', '=', $url)->find();
	
	if( !empty( $item ) ){
		$i++;
		$item->seo->title = $title;
		$item->title = $title;
		$item->save();
	}
	else{
		echo $title . ' not found' . "\n";
	}
}

echo 'updated ' . $i . "\n";