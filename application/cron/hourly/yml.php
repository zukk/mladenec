<?php
require('../../../www/preload.php');

/**
 * Формирование YML файла для Яндекс Маркета
 */
$start_memory = memory_get_usage();
$lock_file = APPPATH.'cache/yml_on';

if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);
touch($lock_file);

$filename = APPPATH . 'cache/yml.xml';
$heap_size = 1000; // Сколько товаров писать в файл за 1 раз

$fp = fopen($filename, 'w');

fwrite($fp,'<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="' . date('Y-m-d H:i') . '">
    <shop>
        <name>ООО &quot;TД Младенец.РУ&quot;</name>
        <company>Младенец.РУ</company>
        <url>http://www.mladenec-shop.ru/</url>
        <currencies><currency id="RUR" rate="1" plus="0"/></currencies>
        <categories>');

$catalog = Model_Section::get_catalog();

$id2Catalog = array();

foreach( $catalog as &$item ){
	
	$id2Catalog[$item->id] = $item;
	if( !empty( $item->children ) ){
		
		foreach( $item->children as &$child ){
			$id2Catalog[$child->id] = $child;
		}
		unset( $child );
	}
}
unset( $item );

fwrite($fp,View::factory('smarty:page/export/yml/categories', array('catalog'   => $catalog)));

fwrite($fp,'</categories>
        <local_delivery_cost>350</local_delivery_cost>
        <offers>');
$goods_written = 0;

define( 'EXPORTXML_SEX', 1951 );
define( 'EXPORTXML_COLOR', 1952 );
define( 'EXPORTXML_SIZE', 1949 );
define( 'EXPORTXML_GROWTH', 1950 );

$goodFilters = array(

	Model_Section::EXPORTYML_CLOTHERS => array(
		EXPORTXML_GROWTH => 'Рост',
		EXPORTXML_SIZE => 'Размер',
		EXPORTXML_COLOR => 'Цвет',
		EXPORTXML_SEX => 'Пол'
	)
);

$filterClosures = [
	EXPORTXML_SEX => function( $name ){
	
		if( preg_match('#^девочка$#iu', $name ) )
			return ['name' => 'Женский'];
		else
			return ['name' => 'Мужской'];
	},
			
	EXPORTXML_GROWTH => function( $name ){
		if( preg_match('#^([0-9\- ]+(см|м))$#iu', $name, $matches ) ){
			return [
				'name' => (int)$matches[1],
				'unit' => $matches[2]
			];
		}
		
		return false;
	},
	EXPORTXML_SIZE => function( $name ){
		
		if( preg_match('#^([0-9\- ]+)$#iu', $name, $matches ) ){
			return [
				'name' => (int)$matches[1],
				'unit' => 'RU'
			];
		}
		
		return false;
	},
	EXPORTXML_COLOR => function( $name ){
		return ['name' => mb_convert_case( $name, MB_CASE_TITLE )];
	},
];

$goodFiltersLabels = [];

foreach( $goodFilters as $type => $filters ){

	foreach( $filters as $id => $label ){
		$goodFiltersLabels[$id] = $label;
	}
}

$goodFiltersIds = [];

for ($heap_number = 0; $goods = Model_Good::for_yml($heap_size,$heap_number);$heap_number++) {
	$c = 0;
	
    foreach($goods as &$g) {
		if ($id2Catalog[$g['section_id']]->parent_id > 0){
			$section = $id2Catalog[$id2Catalog[$g['section_id']]->parent_id];
		}
		else{
			$section = $id2Catalog[$g['section_id']];
		}

		$g['real_section'] = $section->id;

		if( $section->export_type > 0 ){
			$goodFiltersIds[$section->export_type][] = $g['id'];
		}
	}
	unset( $g );

	$goodFiltersV = [];
	if( !empty ( $goodFiltersIds ) )
	foreach($goodFiltersIds as $filterType => &$ids ){
		
		$filtersIds = array_keys( $goodFilters[$filterType] );

		$result = DB::select('value_id', 'good_id', 'filter_id')->from('z_good_filter')->where('filter_id', 'in', $filtersIds)->where('good_id', 'in', $ids )->execute();
		
		$filterValuesIds = [];
		while( $row = $result->current() ){
			$filterValuesIds[$row['value_id']] = 1;
			$result->next();
			$goodFiltersV[$row['good_id']][$row['filter_id']][] = $row['value_id'];
		}
	}
	unset( $ids );

	$filterValues = [];
	if( !empty( $filterValuesIds ) ){
		
		$filterValuesIds = array_keys( $filterValuesIds );
		
		$result = DB::select('name', 'id')->from('z_filter_value')->where('id', 'in', $filterValuesIds )->execute();
		while( $row = $result->current() ){

			$filterValues[$row['id']] = $row['name'];
			$result->next();
		}
	}

    foreach($goods as &$g) {
		
		// Если одновременно мальчик-девочка, то пол не передаем
		if( !empty( $goodFiltersV[$g['id']][EXPORTXML_SEX] ) && count( $goodFiltersV[$g['id']][EXPORTXML_SEX] ) > 1 ){
			unset( $goodFiltersV[$g['id']][EXPORTXML_SEX] );
		}
		
		if( !empty( $goodFiltersV[$g['id']] ) ){
			
			foreach( $goodFiltersV[$g['id']] as $filter_id => &$valuesIds ){

				foreach( $valuesIds as $key => $valueId ){

					$rr = $filterClosures[$filter_id]($filterValues[$valueId]);

					if( $rr !== false )
						$valuesIds[$key] = $rr;
					
					// Яндекс примет только первое значение
					break;
				}
			}
			unset( $valuesIds );
		}
		
        fwrite($fp, View::factory('smarty:page/export/yml/good', array(
			'g' => $g, 
			'section' => $id2Catalog[$g['real_section']], 
			'filter_labels' => $goodFiltersLabels,
			'good_filter' => !empty( $goodFiltersV[$g['id']] ) ? $goodFiltersV[$g['id']] : [],
			'label' => 'market.yandex.ru')));
        $goods_written++;
    }
	unset( $g );
	
    gc_collect_cycles();
}

fwrite($fp,'</offers>
</shop>
</yml_catalog>
');

fclose($fp);

exec('gzip -c '.$filename.' > '.$filename.'.gz');
unlink($lock_file);
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'Yandex Market XML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');

