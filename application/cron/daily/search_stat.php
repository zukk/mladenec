<?php

require('../../../www/preload.php');

$filename = Kohana::$config->load('database')->sphinx['log'];

$file = fopen($filename, 'r');
ini_set('display_errors', 1);

while( $string = fgets($file) ){

	if( preg_match( '#^\[(.*?)\](?:.*?)\[ext2/2/ext ([0-9]+) \(([0-9]+),([12356789]|[0-9]{2,})\)\] \[(?:.*?)\](.*)$#', $string, $matches ) ){
		$timestr = &$matches[1];
		
		$date = \DateTime::createFromFormat("??? F j H:i:s.??? Y", $timestr);
		
		$timestamp = $date->getTimestamp();
		$time = date( 'Y-m-d H:i:s', $timestamp );
		
		$today = mktime(0, 0, 0);
		$yestoday = $today - 86400;
		
		if( ( $timestamp < $yestoday || $timestamp > $today ) ){
			echo 'not in range' . "\n";
			continue;
		}
		
		$count = &$matches[2];
		
		$is_error = $count > 0 ? 0 : 1;
		
		$string = mb_strtolower( trim( $matches[5] ) );
		
		if( empty( $string ) )
			continue;

		$search_text = $string;
		
        $insert = DB::insert('search_words')->columns(array('name', 'is_error'))->values(array($search_text, $is_error ? 0 : 1))->__toString();

		
        $sWord = DB::select('id')->from('search_words')
                ->where('name','=',$search_text)
                ->execute()->as_array();

        if( empty( $sWord ) ){
			$re = ORM::factory('searchwords');
			$re->name = $search_text;
			$re->is_error = $is_error;
			$re->save();
			
			$sWordId = $re->id;
        }
        else{
            $sWordId = $sWord[0]['id'];
			$re = ORM::factory('searchwords', $sWordId);
			$re->is_error = $is_error;
			$re->save();
        }

		DB::insert('search_words_stat')->columns(array('word_id', 'time', 'is_error'))->values( array($sWordId, date('Y-m-d H:i:s', time()), $is_error))->execute();
		DB::delete('search_words_brands')->where('word_id', '=', $sWordId)->execute();

		$brandsIds = DB::select('id')->from('z_brand')->where('search_words', 'like', "%$search_text%")->execute()->as_array('id');
        foreach( $brandsIds as $bId ){
			DB::insert('search_words_brands')->columns(array(
				'word_id', 'brand_id'
			))->values(array(
				$sWordId, $bId['id']
			))->execute();
        }
	}
}
