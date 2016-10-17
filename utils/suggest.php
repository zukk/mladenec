<?php
/**
 * заполняем триграммы для suggest
 */
require('../www/preload.php');

$words = [];
foreach(DB::select('words')->from('good_search')->execute()->as_array('words', 'words') as $item) {
    $q = array_filter(array_map(function ($w) { return mb_strtolower(preg_replace('~[^a-zA-ZА-Яа-яёЁ0-9]+~u', '', $w)); }, explode(' ', $item)));
    foreach($q as $w) {
        isset($words[$w]) ? $words[$w]++ : $words[$w] = 1;
    }
}

foreach ($words as $k => $w) {
    DB::insert('z_suggest', ['keyword', 'trigrams', 'freq'])
        ->values([$k, current(Txt::trigrams($k)), $w])
        ->execute();
}

echo 'updated ' . $j . ' rows';