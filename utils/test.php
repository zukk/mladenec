<?php

require('../www/preload.php');

# получить разрешенные нам озоновские типы
//$ozon = new Ozon();
//print_r($ozon->get_types());

# получить источники для заказов у кого нет
$updated = 0;
$need_source = ORM::factory('order_data')
    ->where('client_data', 'LIKE', '%utm_source%')
    ->where('source', '=', '')
//    ->limit(1)
    ->find_all();

foreach($need_source as $od) {
    if (preg_match('~utm_source=([^&\s]+)&utm_medium=([^&\s]+)&utm_campaign=([^&\s]+)~isu', $od->client_data, $matches)) {
        $arr = [
            'source' => $matches[1],
            'medium' => $matches[2],
            'campaign' => $matches[3],
        ];
        $od->source = json_encode($arr);
        $od->save();
    };
}