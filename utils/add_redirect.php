<?php

require('../www/preload.php');

// скрипт добавляет редиректы в таблицу теговых редиректов
// данные берёт из redirect.csv, первый столблец - куда, второй столбец - откуда

$res = fopen('redirect.csv', 'r');
while ($data = fgetcsv($res, null, ';', '"')) {
    $from = $data[1];

    // в адресе from - удаляем query_string если есть
    $has_query = strpos($from, '?');
    if ($has_query !== FALSE) {
        $from = substr($from, 0, $has_query);
    }
    if (strpos($from, '&') !== FALSE || strpos($from, '=') !== FALSE) { // ошибка в get - пропускаем
        echo 'skipped redirect from ' . $from . "\n";
        continue;
    }

    if (strpos($from, 'catalog/') === 0
        || strpos($from, 'actions/') === 0
        || strpos($from, 'product/') === 0
        || strpos($from, 'index.html') === 0
        || strpos($from, 'about/news/') === 0
        || strpos($from, 'contacts/') === 0
        || strpos($from, 'tag/') === 0
        || strpos($from, 'site_map/') === 0
        || strpos($from, 'shop/') === 0
        || strpos($from, 'advertise/') === 0
        || strpos($from, 'about/') === 0
        || strpos($from, 'delivery/') === 0
        || strpos($from, 'article/') === 0

        // эти после выполнения можно удалить из кода - они на один раз
        || in_array($from, ['special1', 'book', 'modulo', 'modulo', 'talk', 'show.php', 'condition.php', 'birthday',
            'favicon.ico', 'first', 'Work', 'advices', 'brend.php', 'avtochair', 'podarok3', 'moskva', 'payd',
            'special2', 'catalog0-1', 'MiniMiniL', 'diaper', 'poll', 'MiniMax0', 'collection_2007', 'wo.php', 'sharap',
            'vozvrat', 'links', 'Pampers_promotion_rules', 'dietolog.php', 'news/index.php', 'gb', 'catalog1-3',
            'manager', 'special3', 'forum/index.php'])

    ) { // это хорошие редиректы


    } elseif ( // это плохие - их не будем добавлять
        strpos($from, 'upload/') === 0 // редиректы с несуществующих файлов статики
        || strpos($from, 'img/') === 0 // редиректы с несуществующих файлов статики
        || strpos($from, 'baby_img/') === 0 // редиректы с несуществующих файлов статики
        || strpos($from, 'http:/') === 0 // редиректы с урла с протоколом - не сработает
        || strpos($from, 'payment/') === 0 // редиректы с обработки оплаты
        || ctype_digit($from) // редиректы со страницы оплаты

    ) {
        echo 'skipped redirect from ' . $from . "\n";
        continue;

    }

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

echo "\nfinished\n";
