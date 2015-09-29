<?php

/**
 * получение списка городов для доставки DPD
 */

require('../../../www/preload.php');

$dpd = new DpdSoap();
$dpd->fill_cities();
$dpd->fill_terminals();
