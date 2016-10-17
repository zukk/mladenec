<?php

require('../../../www/preload.php');
ob_end_clean();
$result = DB::query(Database::SELECT, "
	SELECT  `z_section`.`id` ,  `z_section`.`name` ,  `z_section`.`translit` ,  `z_brand`.`id` AS brand_id,  `z_brand`.`name` AS brand_name ,
 	`z_brand`.`name` AS  `con` , COUNT(  `z_good`.id ) AS  `count`
FROM  `z_section` ,  `z_brand` ,   `z_good`
WHERE  `z_section`.`id` =  `z_good`.`section_id`
AND  `z_section`.`vitrina` =  'mladenec'
AND  `z_brand`.`id` =  `z_good`.`brand_id`
AND  `z_good`.`show` =1
AND  `z_good`.`qty` !=0
GROUP BY  `con`
")->execute();


/*
SELECT  `z_section`.`id` ,  `z_section`.`name` ,  `z_section`.`translit` ,  `z_brand`.`id` AS brand_id,  `z_brand`.`name` AS brand_name ,
 	`z_filter`.`id` AS filter_id ,  `z_filter_value`.`id` AS filter_value_id ,  `z_filter_value`.`name` AS filter_value_name ,
	CONCAT(`z_brand`.`name` ,  '-',  `z_filter_value`.`name` ) AS  `con` , COUNT(  `good_id` ) AS  `count`
FROM  `z_section` ,  `z_brand` ,  `z_filter_value` ,  `z_good` ,  `z_good_filter` ,  `z_filter`
	WHERE  `z_section`.`id` =  `z_good`.`section_id`
	AND  `z_section`.`vitrina` =  'mladenec'
	AND  `z_brand`.`id` =  `z_good`.`brand_id`
	AND  `z_filter_value`.`id` =  `z_good_filter`.`value_id`
	AND  `z_filter_value`.`filter_id` =  `z_filter`.`id`
	AND (
		`z_filter`.`name` LIKE  'По виду'
		OR  `z_filter`.`name` LIKE  'По Виду'
		OR  `z_filter`.`name` LIKE  'Большой тип'
	)
	AND  `z_good`.`id` =  `z_good_filter`.`good_id`
	AND  `z_good`.`show` =1
	AND  `z_good`.`qty` !=0

	GROUP BY  `con`
*/

$content = '"ID раздела";"Название раздела";"Бренд ID";"Бренд имя";"URL";"ЧПУ";"Товаров";"URL теговой если есть"' . "\r\n";  //"ID фильтра";"ID значения фильтра";"Название значения фильтра"
while( $row = $result->current() ){

	if( $row['count'] < 5 ){
		$result->next();
		continue;
	}

	$tag = ORM::factory('tag')
		->where('section_id', '=',  $row['id'])
		->where('params', '=', 'SECTION_ID='.$row['id']. ',PROPERTY_BRAND='.$row['brand_id'])
		->limit(1)
		->find();

	$row['found'] = '';
	$row['4pu'] = '/catalog/' . Txt::translit($row['name']) . '/' . Txt::translit($row['con']) . '.html';

	$s = new Model_Section($row['id']);
	$row['url'] = $s->get_link(0).'#!b='.$row['brand_id'];

	if ($tag->loaded()) {
		$row['found'] = $tag->code;
	}

	$content .= 
			'"' . $row['id'] . '";' .
			'"' . $row['name'] . '";' .
			'"' . $row['brand_id'] . '";' .
			'"' . $row['brand_name'] . '";' .
/*
			'"' . $row['filter_id'] . '";' .
			'"' . $row['filter_value_id'] . '";' .
			'"' . $row['filter_value_name'] . '";' .
*/
			'"'. $row['url'] . '";' .
			'"'. $row['4pu'] . '";' .
			'"' . $row['count'] . '";' .
			'"' . $row['found'] . '"' .
			"\r\n";
	
	$result->next();
}

$content = iconv('utf-8', 'windows-1251', $content );

$file = fopen( 'autotags.csv', 'w');
fwrite($file, $content);
fclose( $file );