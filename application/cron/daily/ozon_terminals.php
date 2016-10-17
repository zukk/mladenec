<?php

/**
 * обновление списка терминалов из озона
 */

require('../../../www/preload.php');

$ozon = new OzonDelivery();
$ozon->update_terminals();
