<?php

/**
 * привести параметры теговых к одному виду, повесить на них уникальный ключ
 */

require('../www/preload.php');

$total = 0;
try {
    $tags = ORM::factory('tag')->find_all()->as_array();
    foreach ($tags as $t) {
        $link = $params = $t->parse_params();
        if ( ! empty($params['f'])) {
            foreach($params['f'] as &$p) {
                sort($p);
                $p = implode('_', $p);
            }
            ksort($params['f']);
            foreach($params['f'] as $k => &$p) {
                $params['f'.$k] = [$p];
            }
            unset($params['f']);
        }
        if ( ! empty($params['c']) && count($params['c']) == 1) {
            $t->section_id = $params['c'][0];
        }
        foreach ($params as &$p) {
            asort($p);
        }
        foreach ($params as &$p) {
            $p = implode('_', $p);;
        }
        ksort($params);

        $t->query = http_build_query($params);

        // добавим привязки к категории
        if ( ! empty($link['c'])) {
            $ins = DB::insert('z_tag_section')->columns(array('tag_id', 'section_id'));
            foreach ($link['c'] as $c) $ins->values(array($t->id, $c));
            DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE ', $ins))->execute();
        }
        if ( ! empty($link['b'])) { // к бренду
            $ins = DB::insert('z_tag_brand')->columns(array('tag_id', 'brand_id'));
            foreach($link['b'] as $b) $ins->values(array('tag_id' => $t->id, 'brand_id' => $b)); // $b = ;
            DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();
        }

        if ( ! empty($link['f'])) { // к фильтрам
            $ins = DB::insert('z_tag_filter_value')->columns(array('tag_id', 'filter_value_id'));
            foreach($link['f'] as $f) {
                foreach($f as $vid) {
                    $ins->values(array('tag_id' => $t->id, 'filter_value_id' => $vid));
                }
            }
            DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();
        }
        $total++;

        $t->save();
    }
} catch (ErrorException $e) {

    echo $e->getMessage().$e->getLine();

    print_r($params);
}

/*
if ($t->code == 'tag/gigiena_i_uxod/posuda_dlya_kormleniya.html') $t->add_redirect('tag/detskoe_pitanie/sredstva_dlya_kormleniya.html');
if ($t->code == 'tag/detskoe_pitanie/sredstva_dlya_kormleniya.html') $t->delete(); // от этого оставляем только редирект

'tag/detskoe_pitanie/detskie_smesi/vse_pro_detskie_smesi.html'
*/

