<?php

/**
 * расставить транслит в бренды
 */

require('../www/preload.php');

$brands = ORM::factory('brand')->find_all();

foreach($brands as $b) {
    if (empty($b->translit)) {
        $b->translit = Txt::translit($b->name);
        $b->save();
    }
}

$dups = DB::query(Database::SELECT, "
        SELECT b.translit, b1.id
        FROM z_brand b
        JOIN z_brand b1 ON ( b1.translit = b.translit )
        WHERE b1.id > b.id
    ")
    ->execute()
    ->as_array();

$translit = array();
foreach ($dups as $dup) {
    $translit[$dup['translit']] = empty($translit[$dup['translit']]) ? 1 : $translit[$dup['translit']] + 1;
    DB::update('z_brand')
        ->set(array('translit' => $dup['translit'] . '-' . $translit[$dup['translit']]))
        ->where('id', '=', $dup['id'])
        ->execute();
    echo $dup['id'] . ' updated to ' . $translit[$dup['translit']] . "/n";
}

// сделать translit - уникальным ключом в брендах