<?php

require_once('../www/preload.php');

/* парсим названия подгузов для выяснения веса и количесвтва в пачке */

$goods = DB::select('id', 'name', 'section_id')
    ->from('z_good')
    ->where('section_id', 'IN', [29798, 29781])
    ->where('active', '=', 1)
    ->execute()
    ->as_array('id');

$weights = DB::select('id', 'name') // фильтр по весу для поиска
    ->from('z_filter_value')
    ->where('filter_id', '=', 100)
    ->execute()
    ->as_array('name', 'id');

foreach($goods as $id => $good) {
    if ( $good['section_id'] == 29798) { // вес только у подгузов
        if (preg_match('~(до|от )?((\d+)\s*-\s*)?(\d+)(\+)? кг~isu', $good['name'], $matches)) { // (до)?

            $min = $max = FALSE;
            if (!empty($matches[1]) && $matches[1] == 'до ') { // до
                $min = 0;
                $max = intval($matches[4]);
            } elseif ((!empty($matches[1]) && $matches[1] == 'от ') || !empty($matches[5])) { // +
                $min = intval($matches[4]);
                $max = $min + 5;
            } else {
                $min = intval($matches[3]);
                $max = intval($matches[4]);
            }

            $max = min($max, 35);

            for ($i = $min; $i <= $max; $i++) { // проставляем всем вес
                $ins = DB::insert('z_good_filter')
                    ->columns(['good_id', 'filter_id', 'value_id'])
                    ->values([$id, 100, $weights[$i]]);

                DB::query(Database::INSERT, str_replace('INSERT ', 'INSERT IGNORE ', $ins))->execute();
            }


            /*        if ($min === 0 || $max === 0) {
                        echo $id;
                        print_r($matches);
                    }
            */
        } else {
            echo $good['name']. "\n";
        }
    }

    if (preg_match('~(\d+) шт~isu', $good['name'], $matches)) { // проставляем всем число штук в пачке

        DB::update('z_good')
            ->set([ 'per_pack' => intval($matches[1]) ])
            ->where('id', '=', $id)
            ->execute();
    }

}
echo count($goods);

# рещдиректы для теговых
$r = [
    19917 => 18684,
    19915 => 18688,
    19914 => 18688,
    19913 => 18688,
    19864 => 18688,
    19865 => 18688,
    19918 => 18688,
    19866 => 18682,
    19916 => 18683,
    19912 => 18684,
    19853 => 18684,
    18681 => 18685,
    18689 => 18685,
    18690 => 18682,
    18904 => 18683,
    18693 => 18684,
    18691 => 18684,
    18694 => 18688,
    18903 => 18688,
    18902 => 18688,
    18687 => 18688,
];

$tag = DB::select('id', 'params')->from('z_tag')->where('params', 'LIKE', '%2198=%')->where('section_id', '=', 29798)->execute()->as_array('id', 'params');
foreach($tag as $id => $params) {
//    echo $id. ' '.$params. '|'.strtr($params, $r)."\n";
    DB::update('z_tag')->set(['params' => strtr($params, $r)])->where('id', '=', $id)->execute();
}

echo 'run tagsmth and settag now';