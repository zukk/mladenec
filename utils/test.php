<?php

require('../www/preload.php');

# получить разрешенные нам озоновские типы
//$ozon = new Ozon();
//print_r($ozon->get_types());

# получить источники для заказов у кого нет
$updated = 0;
$need_source = DB::select('id', 'client_data')
    ->from('z_order_data')
    ->where('client_data', 'LIKE', '%utm_source%')
//    ->where('source', '=', '')
//    ->limit(1)
    ->execute()
    ->as_array('id', 'client_data');
//echo '%25D0%2591%25D1%2580%25D0%25B5%25D0%25BD%25D0%25B4%25D0%25BE%25D0%25B2%25D1%258B%25D0%25B5%2520%25D0%25B7%25D0%25B0%25D0%25BF%25D1%2580%25D0%25BE%25D1%2581%25D1%258B'))
foreach($need_source as $id => $data) {
    $data = str_replace('%25', '%', $data);
    $data = str_replace('%3D', '=', $data);
    $data = str_replace('%26', '&', $data);
    if (preg_match_all('~utm_([a-z]+)(=)([^&\s]+)~isu', $data, $matches)) {
        $arr = [];
        //print_r($matches);
        foreach($matches[1] as $k => $utm) {
            $arr[$utm] = urldecode(urldecode($matches[3][$k]));
        }
        $updated += DB::update('z_order_data')->set(['source' => json_encode($arr)])->where('id', '=', $id)->execute();
    };
}
echo $updated;