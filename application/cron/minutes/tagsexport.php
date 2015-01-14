<?php

/**
 * снимаем активность у слайдов в ротаторе
 */
require('../../../www/preload.php');

$_SERVER['HTTP_HOST'] = 'mladenec.ak';
$reply = Request::factory('/taggg')->execute()->body();
