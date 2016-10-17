<?php
require('../../../www/preload.php');

/**
 * Формирование YML файла для Яндекс Маркета
 */
$start_memory = memory_get_usage();

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
/*
    ->where_open()
        ->where('parent_id', 'IN', array(28934)) // детское питание
        ->or_where('id', 'IN', array(29891, 29982)) // автокресла, коляски
    ->where_close()
*/
    ->where('active', '=', 1)
    ->order_by('sort')
    ->find_all()
    ->as_array('id');

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
        [ // where!
            array('good.section_id', 'IN', array_keys($catalog)),
            array('good.id1c', '>', 0),
            array('good.big', '=', 0), // @NOTE в озон не выгружаем КГТ
            array('prop.to_ozon', '=', 1)
        ]
        );
    $heap_number++
    ) 
{
    foreach($goods as &$g) {
		
		$minqty = 0;

		if( $g['id1c'] > 0 && ( $g['qty'] > $minqty || $g['qty'] == -1 ) ) {
            // $g['id'] = $g['id1c'];
			fwrite($fp, View::factory('smarty:page/export/yml/good_ozon', ['g' => $g])); // label used inside for Qty!
			$goods_written++;
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
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'OZON YML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');

