<?php

/**
 * Пересчет числа товаров на теговых страницах
 */
require('../../../www/preload.php');

$tags = ORM::factory('tag')->find_all();

foreach ($tags as $t) {

    if (!($params = $t->parse_params())) {
        echo 'Bad params for tag ' . $t->id . "\n";
    }

    $q = DB::select('id')->from('goods_zukk');

    // секции
    if (!empty($params['c'])) $q->where('section_id', 'IN', array_map('intval', $params['c']));

    // брэнды
    if (!empty($params['b'])) $q->where('brand_id', 'IN', array_map('intval', $params['b']));

    // значения фильтров
    if (!empty($params['f'])) {
        foreach ($params['f'] as $values) {
            $q->where('fvalue', 'IN', array_map('intval', $values)); // группируем по фильтрам
        }
    }

    $result = Database::instance('sphinx')->query(Database::SELECT, strval($q))->as_array('id');
    $meta = Database::instance('sphinx')->query(Database::SELECT, 'SHOW META')->as_array('Variable_name', 'Value');

    if (!empty($t)) { // если это была теговая страница - запишем в неё число найденных товаров
        $t->goods_count = $meta['total_found'];
        $t->goods_count_ts = time();
        $t->save();
        echo 'Tag ' . $t->id . ' updated to ' . $t->goods_count . "\n";
    }
}

