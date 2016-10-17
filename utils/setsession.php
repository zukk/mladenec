<?php

/**
 * Проставляет user_id в таблицу сессий
 */

require('../www/preload.php');

$offset = 0;

do {
    $updated = 0;
    $sessions = DB::select('data', 'id')
        ->from('z_session')
        ->where('user_id', '=', 0)
        ->where('data', 'LIKE', '%Model_User%')
        ->limit(1000)
        ->execute()
        ->as_array('id', 'data');

    foreach ($sessions as $id => $d) {

        $data = unserialize($d);

        if (!empty($data['user'])) {

            $u = unserialize($data['user']);

            if (!empty($u->id)) {
                DB::update('z_session')->value('user_id', $u->id)->where('id', '=', $id)->execute();
                $updated++;
            }
        }
    }
    echo $offset . ':' . $updated . "\n";
    ob_flush();
    flush();
    $offset += 1000;

} while (count($sessions) == 1000);

