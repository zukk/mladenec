<?php
require('../../../www/preload.php');

/**
 * Формирование YML файла для "Товары и цены" Яндекс
 */
$start_memory = memory_get_usage();

$filename = APPPATH . 'cache/yml_gp.xml';
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

$catalog = Model_Section::get_catalog();
$id2Catalog = [];

foreach($catalog as $item) {
    $id2Catalog[$item->id] = $item;
    if ( ! empty($item->children)) {
        foreach($item->children as $child) {
            $id2Catalog[$child->id] = $child;
        }
    }
}

fwrite($fp, View::factory('smarty:page/export/yml/categories', ['catalog' => $catalog]));

fwrite($fp, '</categories><offers>');
$goods_written = 0;

$image_types = 'originals';
for ($heap_number = 0; $goods = Model_Good::for_yml($heap_size, $heap_number, [['id', '=', 51738]]); $heap_number++) {
    foreach($goods as &$g) { // тут передаем по ссылке, иначе послдний элемент дублируется
        $g['desc'] = mb_substr(Txt::clean_rude_symbols(htmlentities(strip_tags($g['desc']))), 0, 175);

        if (mb_strlen($g['desc']) == 175) { // не более 175 символов, обрезаем по слову (крайний пробел)
            $g['desc'] = mb_substr($g['desc'], 0, strrpos($g['desc'], ' '));
        }
        fwrite($fp, View::factory('smarty:page/export/yml/good', [
            'g'             => $g,
            'section'       => $id2Catalog[$g['section_id']],
        ]));
        $goods_written++;
    }

    gc_collect_cycles();
}

fwrite($fp,'</offers>
</shop>
</yml_catalog>
');

fclose($fp);

exec('gzip -c '.$filename.' > '.$filename.'.gz'); // делаем gzip
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'YML GP file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');