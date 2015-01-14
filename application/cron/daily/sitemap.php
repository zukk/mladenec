<?php
/**
 * Скрипт для генерации Sitemap.xml
 * Запускать раз в сутки
 * Карта сайта и все подкарты кладутся в каталог /export
 */
require('../../../www/preload.php');

$domains = Kohana::$config->load('domains')->as_array(); // = Kohana::$config->load('domains')->as_array();
$host = $domains['mladenec']['host'];

$dir = APPPATH.'../www/export/sitemap/';
$indexFile = APPPATH.'../www/sitemap.xml';

function clearDir($dir) {
	if ($objs = glob($dir . "/*")) {
		foreach ($objs as $obj) {
			is_dir($obj) ? clearDir($obj) : unlink($obj);
		}
	}
}

function get_sections( $data = [], $parent = 0 ){
	
	$result = DB::select('id', 'name', 'translit')
        ->from('z_section')
        ->where('parent_id', '=',  $parent)
        ->where('active', '=', 1)
        ->execute();
	
	while ($row = $result->current()) {
		$result->next();
		$data[] = $row;
		$data = get_sections($data, $row['id']);
	}
	
	return $data;
}

$i = 0;
$c = 0;
$length = 0;
$file = null;

function newPart()
{
	global $dir, $i, $length, $file, $c;
	
	if ($file !== null) {
		fwrite($file, '</urlset>');
		fclose($file);
	}
	$i++;
	
	$file = fopen( $dir . $i . '.xml', 'w' );
	fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>'. "\n". '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
	
	$length = $c = 0;
}

function writeUrl($url)
{
	global $length, $file, $host, $c;
	
	// по стандарту ограничение на 10мб. я делаю на 8
	if( $file === null || $length > 1024 * 1024 * 8 || $c > 45000 ){
		newPart();
	}
	
	$line = '<url><loc>http://' . $host . $url . '</loc></url>' . "\n";
	fwrite($file, $line);
	
	$length += mb_strlen($line);
	$c++;
}

clearDir($dir); // стираем старые кусочки

// категории каталога
$sections = get_sections();
foreach($sections as $section) writeUrl(Route::url('section', ['translit' => $section['translit']]));
unset($section);

// товары
$result = DB::select('id', 'name', 'translit', 'group_id')->from('z_good')->where('show', '=', 1)->execute()->as_array();
foreach ($result as $row) {
    writeUrl(Route::url('product', $row));
}
	
// статьи
$result = DB::select('id')->from('z_article')->where('active', '=', 1)->execute()->as_array();
foreach ($result as $row) {
    writeUrl(Route::url('article', $row));
}

// новости
$result = DB::select('id')->from('z_new')->where('active', '=', 1)->execute()->as_array();
foreach ($result as $row) {
    writeUrl(Route::url('new', $row));
}

fwrite($file, '</urlset>');
fclose( $file );

// индекс
$index = '<?xml version="1.0" encoding="UTF-8"?>'
		. '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

for ($j = 1; $j <= $i; $j++) {
	$index .= '<sitemap><loc>http://' . $host . '/export/sitemap/' . $j . '.xml</loc></sitemap>';
}
$index .= '</sitemapindex>';
		
if(is_file($indexFile)) unlink($indexFile);
if($in = fopen($indexFile, 'w')) fwrite($in, $index );

fclose( $in );

