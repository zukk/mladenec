<?php
require(__DIR__.'/../../../www/preload.php');

/**
 * Формирование YML файла для Google merchant
 */
$start_memory = memory_get_usage();

$filename = APPPATH . '/cache/google_merchant.xml';
$heap_size = 1000; // Сколько товаров писать в файл за 1 раз

$fp = fopen($filename, 'w');

fwrite($fp,'<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">

    <channel>
    <title>Младенец.РУ</title>
    <link>http://www.mladenec-shop.ru</link>
    <description>Младенец.ру — интернет магазин детских товаров, питания, игрушек и других</description>
    ');

$goods_written = 0;
$image_types = 'originals';

for ($heap_number = 0; $goods = Model_Good::for_google_merchant($heap_size,$heap_number);$heap_number++) {
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

        $a = preg_replace('/[\x00-\x1F\x80-\xFF]/u', '', $g['desc']);
        $clear_desc = strip_tags($a);
        $descAll = substr($clear_desc, 0, 5000);
        $descPos = strrpos($descAll, ' ');
        $desc = substr($descAll, 0, $descPos);
        $g['desc'] = $desc;

        fwrite($fp, View::factory('smarty:page/export/google/good',
            array(
                'g' => $g,
                'images' => $good_images)
        )
        );
        $goods_written++;
    }
    gc_collect_cycles();
}

fwrite($fp,'
</channel>
</rss>
');

fclose($fp);
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'Google Merchant XML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');