<?php
require('../../../www/preload.php');

/**
 * Формирование YML файла для Яндекс Маркета
 */
$start_memory = memory_get_usage();
$lock_file = APPPATH.'cache/ozon_xml_on';

if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);
touch($lock_file);

$filename = APPPATH . '/cache/ozon_xml.xml';
$heap_size = 1000; // Сколько товаров писать в файл за 1 раз

$goods = ORM::factory('good')
    ->with('prop')
    ->where('id1c', '>', 0)
    ->where('prop.weight', '>', 0)
    ->where('ozon_type_id', '>', 0)
    ->find_all();

$fp = fopen($filename, 'w');

fwrite($fp,View::factory('smarty:page/export/ozon', ['goods' => $goods]));

fclose($fp);
unlink($lock_file);
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'OZON YML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');

