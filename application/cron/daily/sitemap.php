<?php
/**
 * Скрипт для генерации Sitemap.xml
 * Запускать раз в сутки
 * Карта сайта и все подкарты кладутся в каталог /export
 * А. В xml карте сайта не должно быть изображений
 * Б. В карте сайте не должно быть ссылок на товары или категории, которые редиректятся на итмарт
 * В. Карта сайта не должна содержать элементы вида http://www.mladenec-shop.ru/catalog/bytovaya-himiya/pigeon.html - т.е. категорийные страницы, заканчивающиеся на html
 * Г. Карта сайта не должна содержать ни одной ссылки с параметрами
 * Д. Самое важное - в карте сайта не должно быть неактуальных ссылок или ошибочных ссылок, например, http://www.mladenec-shop.ru/product/-dostavka-za-mkad/32801.52716.html
 * Е. Карту сайта надо разбить на следующие файлы:
 * 1. список категорийных теговых /catalog
 * 2. список всех продуктов /product
 * 3. список всех действующих теговых старого формата /tag
 * 4. Список актуальных (и только актуальных) акций /actions БЕЗ комбинаций фильтров вида http://www.mladenec-shop.ru/actions/detskaya-komnata/interest
 * 5. Список всех статей /article
 * 6. Список всех новостей /about/news/
 * 7. Список страниц карты сайта /site_map
 * 8. Список остальных страниц - список тут https://docs.google.com/spreadsheets/d/1k4TTdEi-2ebgkMQKxHrZ8gswPcu1jqkdUQf8B_qrkRo/edit?usp=sharing - все кроме тех, что в папке user
 */
ini_set('memory_limit', '1024M');
require(__DIR__.'/../../../www/preload.php');


$config = [

    'catalog' => [
        'data' => [
            DB::select('id', DB::expr("CONCAT('catalog/', translit) as url"))
                ->from('z_section')
                ->where('active', '=', 1)
                ->where('vitrina', '=', 'mladenec')
                ->where('code', '!=', '50061508') // сертификаты не показываем
                ->execute()
                ->as_array('id', 'url'),

            DB::select('t.id', 'code')
                ->from(['z_tag', 't'])
                ->join('tag_redirect', 'LEFT')->on('url', '=', 'code')->on('to_id', '>', DB::expr(0))
                ->where('section_id', '>', 1)
                ->where('code', 'LIKE', 'catalog%')
                ->where('code', 'NOT LIKE', '%.html')
                ->where('goods_count', '>', '0')
                ->where('url', 'IS', null)
                ->execute()
                ->as_array('id', 'code')
        ],
    ],

    'product' => [
        'data' => [
            DB::select('good.id',
                DB::expr("CONCAT('product/', good.translit, '/', good.group_id, '.', good.id, '.html') as url")
            )->from(['z_good', 'good'])
                ->join(['z_good_prop',   'prop'])   ->on('good.id',         '=', 'prop.id')
                ->join(['z_brand',       'brand'])  ->on('good.brand_id',   '=', 'brand.id')
                ->join(['z_section',     'section'])->on('good.section_id', '=', 'section.id')
                ->join(['z_section',     'sp'])     ->on('section.parent_id', '=', 'sp.id')
                ->join(['z_group',       'group'])  ->on('good.group_id',   '=', 'group.id')
                ->join(['b_file',        'file'])   ->on('prop.img1600',    '=', 'file.id')

                ->where('good.show',        '=', 1)
                ->where('good.section_id',  '>', 0)
                ->where('good.brand_id',    '>', 0)
                ->where('good.group_id',    '>', 0)
                ->where('good.active',      '=', 1)
                ->where('prop.img1600',     '>', 0)
                ->where('section.active',   '=', 1)
                ->where('sp.active',   '=', 1)
                ->where('section.vitrina',     '=', 'mladenec')
                ->where('group.active',     '=', 1)
                ->where('brand.active',     '=', 1)
                ->order_by('good.id')
                ->execute()
                ->as_array('id', 'url')
        ]
    ],

    'tag' => [
        'data' => [
            DB::select('t.id', 'code')
                ->from(['z_tag', 't'])
                ->join('tag_redirect', 'LEFT')->on('url', '=', 'code')->on('to_id', '>', DB::expr(0))
                ->where('code', 'LIKE', 'tag%')
                ->where('goods_count', '>', '0')
                ->where('url', 'IS', null)
                ->execute()
                ->as_array('id', 'code')
        ]
    ],

    'action' => [
        'data' => [
            DB::select('id', DB::expr('CONCAT(\'actions/\', id) as url'))
                ->from('z_action')
                ->where('active', '=', 1)
                ->where('show', '=', 1)
                ->where_open()
                    ->where('vitrina_show', '=', 'all')
                    ->or_where('vitrina_show', '=', 'mladenec')
                ->where_close()
                ->execute()
                ->as_array('id', 'url'),

            DB::select('at.id', DB::expr('CONCAT(\'actions/\', url) as url'))
                ->from(['z_actiontag', 'at'])
                ->where('at.id', 'IN',
                    DB::select('actiontag_id')->distinct(TRUE)->from('z_actiontag_ids')
                )
                ->join('z_actiontag_ids')->on('at.id', '=', 'z_actiontag_ids.actiontag_id')
                ->join('z_action')->on('z_action.id', '=', 'z_actiontag_ids.action_id')
                ->where('z_action.active', '=', 1)
                ->where('z_action.show', '=', 1)
                ->where_open()
                    ->where('vitrina_show', '=', 'all')
                    ->or_where('vitrina_show', '=', 'mladenec')
                ->where_close()
                ->order_by('at.order')
                ->execute()
                ->as_array('id', 'url')
        ]
    ],

    'article' => [
        'data' => [
            DB::select('id', DB::expr('CONCAT(\'article/\', id) as url'))
                ->from('z_article')
                ->where('active', '=', 1)
                ->execute()
                ->as_array('id', 'url')
        ]
    ],

    'news' => [
        'data' => [
            DB::select('id', DB::expr('CONCAT(\'about/news/\', id) as url'))
                ->from('z_new')
                ->where('date','<=',date('Y-m-d'))
                ->where('active', '=', 1)
                ->execute()
                ->as_array('id', 'url')
        ]
    ],

    'sitemap' => [
        'data' => [
            DB::select('id', DB::expr("CONCAT('site_map/', translit, '/', id, '.html') as url"))
                ->from('z_section')
                ->where('active', '=', 1)
                ->where('parent_id', '>', 0)
                ->where('vitrina', '=', 'mladenec')
                ->where('code', '!=', '50061508') // сертификаты не показываем
                ->execute()
                ->as_array('id', 'url'),
        ]
    ],

    'page' => [
        'data' => [
            DB::select('id', 'link')
                ->from('z_menu')
                ->where('show', '=', 1)
                ->execute()
                ->as_array('id', 'link'),
            [
                '',
                'registration',
                'catalog',
                'pampers',
                'novelty',
                'superprice',
                'hitz',
                'about/sale.php',
                'site_map/list.php',
                'about/news',
                'about/brands',
                // brand/(translit), ?
                ''
            ]
        ]
    ]
];

$domains = Kohana::$config->load('domains')->as_array();
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

$i = 0;
$c = 0;
$length = 0;
$file = null;
$prefix = '';

function newPart($name, $finish = FALSE)
{
    global $dir, $length, $file, $c;

    if ($finish) {
        fwrite($file, '</urlset>');
        fclose($file);
    } else {

        $file = fopen( $dir . $name . '.xml', 'w' );
        fwrite($file,
            '<?xml version="1.0" encoding="UTF-8"?>'. "\n".
            '<?xml-stylesheet type="text/xsl" href="/xml-sitemap.xsl"?>'. "\n".
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n"
        );
    }
    $length = $c = 0;
}

function writeUrl($url, $params = []) {
    global $length, $file, $host, $c;

    $line = '<url><loc>http://' . $host . '/' . $url . '</loc>';

    foreach($params as $key => $param){
        if (in_array($key, ['changefreq', 'priority'])) {
            $line .= '<' . $key . '>' . $param . '</' . $key . '>';
        }
    }
    $line .= '</url>' . "\n";

    fwrite($file, $line);

    $length += mb_strlen($line);
    $c++;
}

clearDir($dir); // стираем старые кусочки

$redirects = DB::select('id', 'url')->from('tag_redirect')->where('to_id', '>', 0)->execute()->as_array('id', 'url');

// главная страница
//writeUrl('/',
//    [
//        'changefreq' => 'always',
//        'priority' => '1'
//    ]
//);

foreach($config as $name => $data) {
    newPart($name);
    foreach($data['data'] as $arr) {
        foreach($arr as $id => $url)  {
            if ( ! in_array($redirects, $url)) {
                writeUrl($url,
                    [
                        'changefreq' => 'daily',
                        'priority' => '0.8'
                    ]
                );
            }
        }
    }
    newPart($name, TRUE);
}

// индекс
$index = '<?xml version="1.0" encoding="UTF-8"?>'
    . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach($config as $name => $data) {
    $index .= '<sitemap><loc>http://' . $host . '/export/sitemap/' . $name . '.xml</loc></sitemap>';
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
