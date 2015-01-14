<?php

require('../www/preload.php');

// скрипт добавляет редиректы в таблицу теговых редиректов
// данные берёт из redirect.csv, первый столблец - куда, второй столбец - откуда

$res = fopen('redirect.csv', 'r');
while ($data = fgetcsv($res, null, ';', '"')) {
    $from = $data[1];
    $to = $data[0];

    echo 'FROM ' . $from . ' TO ' . $to . "\n";

    $to_id = DB::select('id')->from('tag_redirect')->where('url', '=', $to)->execute()->get('id');
    $from_id = DB::select('id')->from('tag_redirect')->where('url', '=', $from)->execute()->get('id');

    if (empty($to_id)) {
        $q = DB::insert('tag_redirect')->columns(array('url'))->values(array($to));
        $to_id = $q->execute()[0];
    }
    if (empty($from_id)) {
        $q = DB::insert('tag_redirect')->columns(array('url', 'to_id'))->values(array($from, $to_id));
        $q->execute();
    } else {
        DB::update('tag_redirect')->set(array('to_id' => $to_id))->where('id', '=', $from_id)->execute();
    }

}
