<?php
require('../../../www/preload.php');

/**
 * Формирование XML файла для Товары@mail.ru
 */
$start_memory = memory_get_usage();

$filename = APPPATH . '/cache/mailru.xml';
$heap_size = 1000; //Сколько товаров писать в файл за 1 раз

$fp = fopen($filename, 'w');

fwrite($fp,'<?xml version="1.0" encoding="utf-8"?>
<torg_price date="' . date('Y-m-d H:i') . '">
    <shop>
        <shopname>ООО &quot;TД Младенец.РУ&quot;</shopname>
        <company>Младенец.РУ</company>
        <url>http://www.mladenec-shop.ru/</url>
        <currencies><currency id="RUR" rate="1" /></currencies>
        <categories>');

fwrite($fp,View::factory('smarty:page/export/mailru/categories', array('catalog'   => Model_Section::get_catalog())));

fwrite($fp,'</categories>
        <offers>');

$goods_written = 0;
for ($heap_number = 0; $goods = Model_Good::for_yml($heap_size,$heap_number);$heap_number++) {
    foreach($goods as $g) {
        fwrite($fp, View::factory('smarty:page/export/mailru/good', array('g'   => $g)));
        $goods_written++;
    }
    gc_collect_cycles();
}

fwrite($fp,'</offers>
</shop>
</torg_price>
');

fclose($fp);
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'Torg@mail.ru  XML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');


