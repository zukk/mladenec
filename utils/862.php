<?php

/**
 * привести параметры теговых к одному виду, повесить на них уникальный ключ
 */

require('../www/preload.php');

try {
    $tags = ORM::factory('tag')->find_all()->as_array();
    foreach ($tags as $t) {
        $params = $t->parse_params();
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

        $t->save();
    }
} catch (ErrorException $e) {

    print_r($params);
}

/*
if ($t->code == 'tag/gigiena_i_uxod/posuda_dlya_kormleniya.html') $t->add_redirect('tag/detskoe_pitanie/sredstva_dlya_kormleniya.html');
if ($t->code == 'tag/detskoe_pitanie/sredstva_dlya_kormleniya.html') $t->delete(); // от этого оставляем только редирект

'tag/detskoe_pitanie/detskie_smesi/vse_pro_detskie_smesi.html'
*/

