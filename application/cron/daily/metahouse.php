<?php

require('../../../www/preload.php');

// TODO - переписать так чтобы сам логинился если кончилась авторизация
$url = "https://dashboard.metacommerce.ru/api/products?file=true"; //
//$url = "﻿http://passport.metahouse.ru/login.html?appId=dashboard&returnUrl=https%253A%252F%252Fdashboard.metacommerce.ru%252Fapi%252Fproducts%253Ffile%253Dtrue&companyId=";

$post = [
    'request' => '{"companyId":"mladenec-shop.ru","date":null,"requestFacets":true,"search":"","filters":{"name":"","skuSearch":"","skuMatching":[],"articles":[""],"market":[],"region":[],"clientRegion":[],"source":["origin"],"collectMethod":[],"availability":["inStock"],"markdown":["none"],"catalogMatching":[],"validations":[],"catalog":[],"sku":["dYLjL#0","18gT8B#0","vWhyL#0","KoENKC#0","5L8rn#0","ebSF9B#0","nNxPDB#0","lEwnc#0","IXViw#0","wHrzO#0"]},"offset":0,"limit":1000}',
    'fields' => '["name","url","marketId","source","collectDate","price.online.value","availability","sku.item.name","sku.item.article","sku.matchings"]',
];

$cookie = [
    'exportColumns-mc.desktop.search.ProductGrid' => '["name","availability","sku.item.name","marketId","price.online.value","price.online.currency","sku.item.article","collectDate","source","url"]',
    'mc.dashboard.authCookie' => '981245ab-793b-49ad-85b7-d7985f97074a',
    'mh.socketsConfig.authCookieName' => 'mc.dashboard.authCookie',
    'mh.socketsConfig.port' => '8181',
];
/**
 * Авторизация
 * https://dashboard.metacommerce.ru/
 * mladenec-shop.ru
 * executive@mladenec.ru
 * Gw8fOnq3Vh
 */

$ch = curl_init();
array_walk($cookie, function (&$v, $k) { $v = $k.'='.urlencode($v);});
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookie));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);


$tmp_name = APPPATH . 'cache/metahouse.zip';
$data = curl_exec($ch);
if ( ! $data) {
    echo curl_error($ch);
} else {

    file_put_contents($tmp_name, curl_exec($ch));
}
curl_close($ch);

$translate = array_flip([ // не нужно
    "name" => "name",
    "updated" => 'collectDate',
    "marketId" => "marketId",
	"url" => "url",
    "onlinePrice" => "price.online.value",
	"currency" => "price.online.currency",
	"availability" => "availability",
	"sku name" => "sku.item.name",
    "sku article" => "sku.item.article",
]);

$zip = new ZipArchive();
if ($zip->open($tmp_name) === TRUE) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        $zip->renameName($filename, 'search_'.date('Ymdhis').'.csv');
    }
    $zip->close();
} else {
    echo 'no open zip';
}