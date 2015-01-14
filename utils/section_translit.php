<?php

/**
 * ликвидация дублей транслита в section
 */

require('../www/preload.php');


$dups = DB::query(Database::SELECT, "
        SELECT s.translit, s1.id
        FROM z_section s
        JOIN z_section s1 ON ( s1.translit = s.translit )
        WHERE s1.id > s.id
    ")
    ->execute()
    ->as_array();

$translit = array();
foreach ($dups as $dup) {
    $translit[$dup['translit']] = empty($translit[$dup['translit']]) ? 1 : $translit[$dup['translit']] + 1;
    DB::update('z_section')
        ->set(array('translit' => $dup['translit'] . '-' . $translit[$dup['translit']]))
        ->where('id', '=', $dup['id'])
        ->execute();
    echo $dup['id'] . ' updated to ' . $translit[$dup['translit']] . "/n";
}

