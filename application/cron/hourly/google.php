<?php
require('../../../www/preload.php');

/**
 * Формирование YML файла для google Маркета
 */
$start_memory = memory_get_usage();

$filename = APPPATH . '/cache/google.xml';
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

fwrite($fp,View::factory('smarty:page/export/yml/categories', array('catalog'   => Model_Section::get_catalog())));

fwrite($fp,'</categories>
        <local_delivery_cost>350</local_delivery_cost>
        <offers>');
$goods_written = 0;
$image_types = 'originals';

for ($heap_number = 0; $goods = Model_Good::for_yml($heap_size,$heap_number, [['good.id', 'NOT IN', ['52231', '158548', '52233', '158547', '147052', '147053', '170522']]]);$heap_number++) {
    $good_ids = [];
    foreach($goods as &$g) {
        $good_ids[] = $g['id'];
    }
    unset($g);
    $images = Model_Good::many_images([$image_types], $good_ids);
    foreach($goods as $g) {
        //подготовка изображений   
        $good_images = [];
        if( isset($images[$g['id']][$image_types]) && 
            count($images[$g['id']][$image_types]) > 0 ) {    
            //загрузка только 1 фото на товар
            $good_images[] = array_pop($images[$g['id']][$image_types]); 
        } elseif($g['img1600']!='') {            
            $good_images[] = ORM::factory('file', $g['img1600']);
        }
        
        fwrite($fp, View::factory('smarty:page/export/yml/good', 
                array(
                    'g' => $g, 
                    'images' => $good_images)
                )
              );
        $goods_written++;
    }
    gc_collect_cycles();
}

fwrite($fp,'</offers>
</shop>
</yml_catalog>
');

fclose($fp);
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'Google Market XML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');

