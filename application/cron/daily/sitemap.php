<?php
/**
 * Скрипт для генерации Sitemap.xml
 * Запускать раз в сутки
 * Карта сайта и все подкарты кладутся в каталог /export
 */
require(__DIR__.'/../../../www/preload.php');

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
	fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>'. "\n". '<?xml-stylesheet type="text/xsl" href="/xml-sitemap.xsl"?>'. "\n". '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n");
	
	$length = $c = 0;
}

function writeUrl($url, $params = [], $images = [] )
{
	global $length, $file, $host, $c;
	
	// по стандарту ограничение на 10мб. я делаю на 8
	if( $file === null || $length > 1024 * 1024 * 8 || $c > 45000 ){
		newPart();
	}
	
	$line = '<url><loc>http://' . $host . $url . '</loc>';
	
	foreach( $params as $key => &$param ){
	
		if( in_array( $key, ['changefreq', 'priority'] ) ){
			$line .= '<' . $key . '>' . $param . '</' . $key . '>';
		}
	}
	unset( $param );
	
	foreach( $images as &$image ){
	
		$line .= '<image:image><image:loc>' . $image . '</image:loc></image:image>';
	}
	unset( $image );
	
	$line .= '</url>' . "\n";
	
	fwrite($file, $line);
	
	$length += mb_strlen($line);
	$c++;
}

clearDir($dir); // стираем старые кусочки

// главная страница
writeUrl('/',
			[
				'changefreq' => 'always',
				'priority' => '1'
			]
);

// категории каталога
$sections = get_sections();
$sections_count = count($sections);
foreach($sections as &$section){
	
	writeUrl(
			Route::url('section', ['translit' => $section['translit']]),
			[
				'changefreq' => 'daily',
				'priority' => '0.8'
			]
		);
	
	$e = DB::select('b.id')
		->from(['z_brand', 'b'])
		->join(['z_section_brand', 'sb'])
			->on('b.id', '=', 'sb.brand_id')
		->where('b.active', '=', 1)
		->where('sb.section_id', '=', $section['id'])
		->execute()
		->as_array();
	
	foreach( $e as &$b ){
		writeUrl(
				Route::url('section', ['translit' => $section['translit']]) . '?b=' . $b['id'],
				[
					'changefreq' => 'daily',
					'priority' => '0.8'
				]
			);
	}
	unset( $b );
} 
unset($section);

// товары
$result = DB::select('g.id', 'g.name', 'g.translit', 'g.group_id')
		->from(['z_good', 'g'])
		->join(['z_group', 'gr'])
			->on('g.group_id', '=', 'gr.id')
		->where('g.show', '=', 1)
		->where('gr.active', '=', 1)
		->execute()
		->as_array();
$prod_count = count($result);
foreach ($result as $row) {
	
	$f = DB::select('file_id')->from('z_good_img')->where('good_id', '=', $row['id'])->where('size', '=', 1600)->execute()->as_array('file_id');

	$images = [];
	if( !empty( $f ) ){
		
		$files = DB::select('subdir', 'file_name')->from('b_file')->where('id', 'in', array_keys( $f ))->execute()->as_array();

		foreach( $files as &$item ){

			$images[] = 'http://' . $host . '/upload/' . $item['subdir'] . '/' . $item['file_name'];
		}
		unset( $item );
	}
	
    writeUrl(
			Route::url('product', $row),
			[
				'changefreq' => 'weekly',
				'priority' => '0.6'
			], $images );
}

// теговые
$result = DB::select('code')->from('z_tag')->where('goods_count', '>', 0)/*->where('checked', '=', 1) */->execute();
$tags_count = count($result);
while( $tag = $result->current() ){
	
    writeUrl(
			'/' . $tag['code'],
			[
				'changefreq' => 'weekly',
				'priority' => '0.6'
			] );
	
	$result->next();
}
	
// статьи
$result = DB::select('id')->from('z_article')->where('active', '=', 1)->execute()->as_array();
foreach ($result as $row) {
    writeUrl(
			Route::url('article', $row),
			[
				'changefreq' => 'weekly',
				'priority' => '0.6'
			]);
}

// новости
$result = DB::select('id')->from('z_new')->where('active', '=', 1)->execute()->as_array();
foreach ($result as $row) {
    writeUrl(
			Route::url('new', $row),
			[
				'changefreq' => 'weekly',
				'priority' => '0.6'
			]);
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

/***************************** SEO STATISTICS ************************************/
$prod_result_title = DB::select('z_good.id', 'z_good.group_name', 'z_good.name')
	->distinct('z_good.id')
	->from('z_good')
	->where('z_good.show', '=', 1)
	->where('z_good.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.title', '!=', '')
		->where('z_seo.type', '=', 4))
	->join('z_group')
	->on('z_good.group_id', '=', 'z_group.id')
	->where('z_group.active', '=', 1)
	->execute()
	->as_array();
$prod_missing_title = count($prod_result_title);

$prod_result_desc = DB::select('z_good.id', 'z_good.group_name', 'z_good.name')
	->distinct('z_good.id')
	->from('z_good')
	->where('z_good.show', '=', 1)
	->where('z_good.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.description', '!=', '')
		->where('z_seo.type', '=', 4))
	->join('z_group')
	->on('z_good.group_id', '=', 'z_group.id')
	->where('z_group.active', '=', 1)
	->execute()
	->as_array();
$prod_missing_desc = count($prod_result_desc);

$prod_result_keywords = DB::select('z_good.id', 'z_good.group_name', 'z_good.name')
	->distinct('z_good.id')
	->from('z_good')
	->where('z_good.show', '=', 1)
	->where('z_good.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.keywords', '!=', '')
		->where('z_seo.type', '=', 4))
	->join('z_group')
	->on('z_good.group_id', '=', 'z_group.id')
	->where('z_group.active', '=', 1)
	->execute()
	->as_array();
$prod_missing_keywords = count($prod_result_keywords);

$cat_result_title = DB::select('z_section.id', 'z_section.name')
	->distinct('z_section.id')
	->from('z_section')
	->where('z_section.active', '=', 1)
	->where('z_section.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.title', '!=', '')
		->where('z_seo.type', '=', 3))
	->execute()
	->as_array();
$cat_missing_title = count($cat_result_title);

$cat_result_desc = DB::select('z_section.id', 'z_section.name')
	->distinct('z_section.id')
	->from('z_section')
	->where('z_section.active', '=', 1)
	->where('z_section.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.description', '!=', '')
		->where('z_seo.type', '=', 3))
	->execute()
	->as_array();
$cat_missing_desc = count($cat_result_desc);

$cat_result_keywords = DB::select('z_section.id', 'z_section.name')
	->distinct('z_section.id')
	->from('z_section')
	->where('z_section.active', '=', 1)
	->where('z_section.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.keywords', '!=', '')
		->where('z_seo.type', '=', 3))
	->execute()
	->as_array();
$cat_missing_keywords = count($cat_result_keywords);

$tag_result_title = DB::select('z_tag.id', 'z_tag.name')
	->distinct('z_tag.id')
	->from('z_tag')
	->where('z_tag.goods_count', '>', 0)
	->where('z_tag.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.title', '!=', '')
		->where('z_seo.type', '=', 2))
	->execute()
	->as_array();
$tag_missing_title = count($tag_result_title);

$tag_result_desc = DB::select('z_tag.id', 'z_tag.name')
	->distinct('z_tag.id')
	->from('z_tag')
	->where('z_tag.goods_count', '>', 0)
	->where('z_tag.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.description', '!=', '')
		->where('z_seo.type', '=', 2))
	->execute()
	->as_array();
$tag_missing_desc = count($tag_result_desc);

$tag_result_keywords = DB::select('z_tag.id', 'z_tag.name')
	->distinct('z_tag.id')
	->from('z_tag')
	->where('z_tag.goods_count', '>', 0)
	->where('z_tag.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.keywords', '!=', '')
		->where('z_seo.type', '=', 2))
	->execute()
	->as_array();
$tag_missing_keywords = count($tag_result_keywords);

$ins = DB::insert('z_seostatistics')
	->columns(array(
		'products_count',
		'prod_missing_title',
		'prod_missing_desc',
		'prod_missing_keywords',
		'categories_count',
		'categories_missing_title',
		'categories_missing_desc',
		'categories_missing_keywords',
		'tags_count',
		'tags_missing_title',
		'tags_missing_desc',
		'tags_missing_keywords',
		'date'
	))
	->values(array(
		$prod_count,
		$prod_missing_title,
		$prod_missing_desc,
		$prod_missing_keywords,
		$sections_count,
		$cat_missing_title,
		$cat_missing_desc,
		$cat_missing_keywords,
		$tags_count,
		$tag_missing_title,
		$tag_missing_desc,
		$tag_missing_keywords,
		date('Y-m-d H:i:s', time())))
	->execute();
/***************************** SEO STATISTICS ************************************/

/************************* SEO STATISTICS ЗАПИСЬ В ФАЙЛ *************************/
$prod_result_all = DB::select('z_good.id', 'z_good.group_name', 'z_good.name')
	->distinct('z_good.id')
	->from('z_good')
	->where('z_good.show', '=', 1)
	->where('z_good.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.title', '!=', '')
		->where('z_seo.description', '!=', '')
		->where('z_seo.keywords', '!=', '')
		->where('z_seo.type', '=', 4))
	->join('z_group')
	->on('z_good.group_id', '=', 'z_group.id')
	->where('z_group.active', '=', 1)
	->execute()
	->as_array();

$dir = APPPATH.'../www/export/seo_statistics/';
if(!is_dir(APPPATH.'../www/export/seo_statistics')){
	mkdir(APPPATH.'../www/export/seo_statistics', 0755);
}
// output headers so that the file is downloaded rather than displayed
$filename = 'goods_'.date('Y_m_d').'.csv';

$output = fopen($dir.$filename, 'w');

fputcsv($output, array('id', 'name', 'link'));

foreach($prod_result_all as $prod_csv){
	$link['id'] = $prod_csv['id'];
	$link['name'] = $prod_csv['group_name'].' '.$prod_csv['name'];
	$link['link'] = 'http://'.$host.'/od-men/good/'.$prod_csv['id'];
	fputcsv($output, $link);
}
fclose($output);

$cat_result_all = DB::select('z_section.id', 'z_section.name')
	->distinct('z_section.id')
	->from('z_section')
	->where('z_section.active', '=', 1)
	->where('z_section.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.title', '!=', '')
		->where('z_seo.description', '!=', '')
		->where('z_seo.keywords', '!=', '')
		->where('z_seo.type', '=', 3))
	->execute()
	->as_array();

// output headers so that the file is downloaded rather than displayed
$filename = 'categories_'.date('Y_m_d').'.csv';

$output = fopen($dir.$filename, 'w');

fputcsv($output, array('id', 'name', 'link'));

foreach($cat_result_all as $cat_csv){
	$link['id'] = $cat_csv['id'];
	$link['name'] = $cat_csv['name'];
	$link['link'] = 'http://'.$host.'/od-men/section/'.$cat_csv['id'];
	fputcsv($output, $link);
}
fclose($output);

$tag_result_all = DB::select('z_tag.id', 'z_tag.name')
	->distinct('z_tag.id')
	->from('z_tag')
	->where('z_tag.goods_count', '>', 0)
	->where('z_tag.id', 'NOT IN', DB::select('z_seo.item_id')
		->from('z_seo')
		->where('z_seo.title', '!=', '')
		->where('z_seo.description', '!=', '')
		->where('z_seo.keywords', '!=', '')
		->where('z_seo.type', '=', 2))
	->execute()
	->as_array();

// output headers so that the file is downloaded rather than displayed
$filename = 'tag_'.date('Y_m_d').'.csv';

$output = fopen($dir.$filename, 'w');

fputcsv($output, array('id', 'name', 'link'));

foreach($tag_result_all as $tag_csv){
	$link['id'] = $tag_csv['id'];
	$link['name'] = $tag_csv['name'];
	$link['link'] = 'http://'.$host.'/od-men/tag/'.$tag_csv['id'];
	fputcsv($output, $link);
}
fclose($output);
/************************* SEO STATISTICS ЗАПИСЬ В ФАЙЛ *************************/