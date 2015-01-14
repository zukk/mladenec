<?php
/**
 * Раз в день забираем файл с ценами от PriceLabs
 */

require('../../../www/preload.php');

$prices = file_get_contents('http://mladenec:e8ov0os4@pricelabs.ru/export/mladenec-shop.ru/prices.csv');

$now = new DateTime();
$log_dir = APPPATH.'logs/'.date_format($now, 'Y/m/d');
if ( ! file_exists($log_dir)) mkdir($log_dir, 0777);
$filename = $log_dir.'/'.'prices.csv';
if (file_put_contents($filename, $prices)) echo 'Prices saved to '.$filename;
