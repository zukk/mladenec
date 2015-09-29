<?php

require('../../../www/preload.php');

$url = "https://dashboard.metacommerce.ru/api/products?file=true"; //

$post = [
    'request' => '{"companyId":"mladenec-shop.ru","date":null,"requestFacets":true,"search":"","filters":{"name":"","skuSearch":"","skuMatching":[],"articles":[""],"market":[],"region":[],"clientRegion":[],"source":[],"collectMethod":[],"availability":["inStock"],"markdown":["none"],"catalogMatching":[],"catalog":[],"sku":["dYLjL#0","18gT8B#0","vWhyL#0","KoENKC#0","5L8rn#0","ebSF9B#0","nNxPDB#0","lEwnc#0","IXViw#0","wHrzO#0"]},"limit":1000,"offset":0}',
    'fields' => '["name","url","marketId","collectDate","price.online.value","price.online.currency","availability","sku.item.name","sku.item.article"]',
];
$cookie = [
    'exportColumns-mc.desktop.search.ProductGrid' => '["name","marketId","price.online.value","sku.item.article","availability","url","price.online.currency","sku.item.name","collectDate"]',
    'mc.dashboard.authCookie' => '171a7ba7-4b81-4557-84eb-c8378b269970',
    'mh.socketsConfig.authCookieName' => 'mc.dashboard.authCookie',
    'mh.socketsConfig.port' => '8181',
];

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

$translate = array_flip([
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