<?php

/**
 * Генератор картинок нового размера из картинок 1600
 * Если ломается - надо переписать where перед перезапуском
 */

require('../www/preload.php');

set_time_limit(0);

/*
$lost = DB::query(Database::SELECT, "SELECT id, good_id, file_id FROM z_good_img WHERE good_id NOT IN (SELECT id FROM z_good)")->execute()->as_array();
$deleted = 0;
foreach($lost as $l) {
	$f = new Model_File($l['file_id']);
	if ($f->loaded()) $f->delete();
	DB::query(Database::DELETE, "DELETE FROM z_good_img WHERE id = ".$l['id'])->execute();
	echo $deleted++."\n";
	flush();
	ob_end_flush();
}
*/


$where = "1";
while (TRUE) {

    $_380 = DB::query(Database::SELECT, "SELECT id, good_id, file_id FROM z_good_img WHERE size = '1600' AND " . $where . " ORDER BY id LIMIT 1000")->execute()->as_array();
    if (empty($_380)) {
        die('all done' . "\n");
    }

    $deleted = 0;
    foreach ($_380 as $i) {
        echo $i['id'] . "\n";
        $f = ORM::factory('file', $i['file_id']);
        if (!$f->loaded()) {
            echo 'file ' . $i['file_id'] . ' lost' . "\n";
            DB::query(Database::DELETE, "DELETE FROM z_good_img WHERE id = " . $i['id'])->execute();
        } else {
            $f = $f->resize(173, 255);
            DB::query(Database::INSERT, "INSERT INTO z_good_img (`good_id`, `file_id`, `size`) VALUES (" . $i['good_id'] . ", " . $f->ID . ", '173x255')")->execute();
            $deleted++;
        }
    }
    $where = 'id > ' . $i['id'];
    echo 'deleted ' . $deleted . "\n";
    flush();
    ob_end_flush();
}
