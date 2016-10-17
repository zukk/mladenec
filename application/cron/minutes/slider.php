<?php

/**
 * снимаем активность у слайдов в ротаторе
 */
require('../../../www/preload.php');

DB::query(Database::UPDATE, "
	UPDATE z_slider_banner
	SET
		active = 0
	WHERE
		`from` >= '" . date('Y-m-d H:i:s') . "'
	OR
		`to` <= '" . date('Y-m-d H:i:s') . "'
	OR `allow` = 0
")->execute();
DB::query(Database::UPDATE, "
	UPDATE z_slider_banner
	SET
		active = 1
	WHERE
		`from` <= '" . date('Y-m-d H:i:s') . "'
	AND
		`to` >= '" . date('Y-m-d H:i:s') . "'
	AND `allow` = 1
")->execute();
