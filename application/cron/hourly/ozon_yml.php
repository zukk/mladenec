<?php
require('../../../www/preload.php');

/**
 * Формирование YML файла для Яндекс Маркета
 */
$start_memory = memory_get_usage();
$lock_file = APPPATH.'cache/ozon_yml_on';

if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);
touch($lock_file);

$filename = APPPATH . '/cache/ozon_yml.xml';
$heap_size = 1000; // Сколько товаров писать в файл за 1 раз

$fp = fopen($filename, 'w');

fwrite($fp,'<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="' . date('Y-m-d H:i') . '">
    <shop>
        <name>ООО &quot;TД Младенец.РУ&quot;</name>
        <company>Младенец.РУ</company>
        <url>http://www.mladenec-shop.ru/</url>
        <currencies><currency id="RUR" rate="1" plus="0"/></currencies>
        <categories>');

$catalog = ORM::factory('section')
    ->where_open()
        ->where('parent_id', 'IN', array(28934)) // детское питание
        ->or_where('id', 'IN', array(29891, 29982)) // автокресла, коляски
    ->where_close()
    ->order_by('sort')
    ->find_all()
    ->as_array('id');

$qtyE  = ORM::factory('ozon')->find_all()->as_array();
$elements = array();
foreach( $qtyE as $key => $element ){

	$elements[$element->type][$element->id_item] = $element->scount;
}
    
fwrite($fp,View::factory('smarty:page/export/yml/categories', array('catalog' => $catalog)));

fwrite($fp,'</categories>
        <local_delivery_cost>350</local_delivery_cost>
        <offers>');
$goods_written = 0;

for (
    $heap_number = 0;
    $goods = Model_Good::for_yml(
        $heap_size,
        $heap_number, 
        array( // where!
            array('good.section_id', 'IN', array_keys($catalog)),
            array('good.id1c', '>', 0),
            array('prop.to_ozon', '=', 1)
            )
        );
    $heap_number++
    ) 
{
    foreach($goods as &$g) {
		
		$minqty = 0;
		$sparent = $catalog[$g['section_id']]->parent_id;
		if( $sparent > 0 && !empty( $elements[Model_Ozon::T_CATEGORY][$sparent] ) ){
			$minqty = $elements[Model_Ozon::T_CATEGORY][$sparent];
		}
		
		if( !empty( $elements[Model_Ozon::T_CATEGORY][$g['section_id']] ) ){
			$minqty = $elements[Model_Ozon::T_CATEGORY][$g['section_id']];
		}
		
		if( !empty( $elements[Model_Ozon::T_BRAND][$g['brand_id']] ) ){
			$minqty = $elements[Model_Ozon::T_BRAND][$g['brand_id']];
		}
		
		if( !empty( $elements[Model_Ozon::T_GOOD][$g['id']] ) ){
			$minqty = $elements[Model_Ozon::T_GOOD][$g['id']];
		}
		
		if( $g['id1c'] > 0 && ( $g['qty'] > $minqty || $g['qty'] == -1 ) ) {
            // $g['id'] = $g['id1c'];
			fwrite($fp, View::factory('smarty:page/export/yml/good_ozon', array('g' => $g))); // label used inside for Qty!
			$goods_written++;
		}
		else{
			echo $g['id1c'] .' : '. $g['name'] . ' - ' . $g['qty'] . ' - ' . $minqty . "\n";
		}
    }
	unset( $g );
	
    gc_collect_cycles();
}

fwrite($fp,'</offers>
</shop>
</yml_catalog>
');

fclose($fp);
unlink($lock_file);
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'OZON YML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');

