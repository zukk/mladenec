<?php

/**
 * Авторизация
 * https://dashboard.metacommerce.ru/
 * mladenec-shop.ru
 * executive@mladenec.ru
 * Gw8fOnq3Vh
 */
require('../../../www/preload.php');

$api_key = 'd5d47415-866a-415b-a63d-df6f94a1d998';
$url = "http://api.metacommerce.ru/products?apiKey=".$api_key."&format=csv"
    ."&fields=name,url,marketId,source,collectDate,price.online.value,availability,sku.item.name,sku.item.article,sku.matchings";

$post = [
    'request' => '{"requestFacets":true,"filters":{"source":["origin"],"availability":["inStock"],"markdown":["none"],
    "sku":["dYLjL#0","18gT8B#0","vWhyL#0","KoENKC#0","5L8rn#0","ebSF9B#0","nNxPDB#0","lEwnc#0","IXViw#0","wHrzO#0"]}',
    'fields' => '["name","url","marketId","source","collectDate","price.online.value","availability","sku.item.name","sku.item.article","sku.matchings"]',
];

$ch = curl_init();

/*
$cookie = [
    'exportColumns-mc.desktop.search.ProductGrid' =>
        '["name","availability","sku.item.name","marketId","price.online.value","price.online.currency","sku.item.article","collectDate","source","url"]',
    'mc.dashboard.authCookie' => '6e07aa87-1795-41a1-b02d-1588f5b3ffbc',
    'mh.socketsConfig.authCookieName' => 'mc.dashboard.authCookie',
    'mh.socketsConfig.port' => '8181',
];

array_walk($cookie, function (&$v, $k) { $v = $k.'='.urlencode($v);});
curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookie));
*/

$post = [
    "requestFacets" => TRUE,
    //"request" => json_decode('{"requestFacets":true,"filters":{"source":["origin"],"availability":["inStock"],"markdown":["none"]}'),
    "marketIds" => [
        "mladenec-shop.ru", "esky.ru", "akusherstvo.ru", "dochkisinochki.ru", "babadu.ru",
        "utkonos.ru", "baby-country.ru", "detmir.ru", "wikimart.ru"
    ],
    "sources" => ["origin"],
    "skuArticles" => ["30019899","30003690"],
    "onlyMatchedSkus" => TRUE,
];

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

$fn = 'search_'.date('Ymdhis').'.csv';
$tmp_name = APPPATH . 'cache/'.$fn;
$zip_name = APPPATH . 'cache/metahouse.zip';
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
$zip->open($zip_name, ZIPARCHIVE::OVERWRITE | ZIPARCHIVE::CREATE);
$zip->addFile($tmp_name, basename($tmp_name));
$zip->close();

echo 'well done';