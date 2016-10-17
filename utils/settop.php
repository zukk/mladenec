<?php
/**
 * Выкручивание искусственой популярности товарам из списка
 */
require('../www/preload.php');

$content = file_get_contents('top.csv');

$content = iconv('windows-1251', 'utf-8//IGNORE', $content);
$j = 0;
if (preg_match_all('#([0-9]{1,3})\;([0-9a-zА-Я_/]{2,10})#ius', $content, $matches)) {

    foreach ($matches[0] as $key => $i) {

        $art = $matches[2][$key];
        $e = $matches[1][$key];

        $arts = explode('/', $art);

        foreach ($arts as $a) {

            DB::update('z_good')->value('order', 200 - $e)->where('code', '=', $art)->execute();
            $j++;
        }

    }
}

echo 'updated ' . $j . ' rows';