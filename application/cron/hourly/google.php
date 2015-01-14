<?php
require('../../../www/preload.php');

/**
 * Формирование YML файла для google Маркета
 */
$start_memory = memory_get_usage();
$lock_file = APPPATH.'cache/google_on';

if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);
touch($lock_file);

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
for ($heap_number = 0; $goods = Model_Good::for_yml($heap_size,$heap_number);$heap_number++) {
    foreach($goods as $g) {
        fwrite($fp, View::factory('smarty:page/export/yml/good', array('g' => $g)));
        $goods_written++;
    }
    gc_collect_cycles();
}

fwrite($fp,'</offers>
</shop>
</yml_catalog>
');

fclose($fp);
unlink($lock_file);
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'Google Market XML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');

