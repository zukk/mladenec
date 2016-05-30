<?php
require('../../../www/preload.php');

/**
 * Формирование YML файла для Яндекс Маркета
 */
$start_memory = memory_get_usage();

$filename = APPPATH . 'cache/yml.xml';
$heap_size = 1000; // Сколько товаров писать в файл за 1 раз

$fp = fopen($filename, 'w');

fwrite($fp, '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="' . date('Y-m-d H:i') . '">
    <shop>
        <name>ООО &quot;TД Младенец.РУ&quot;</name>
        <company>Младенец.РУ</company>
        <url>http://www.mladenec-shop.ru/</url>
        <currencies><currency id="RUR" rate="1" plus="0"/></currencies>
        <categories>');

$catalog = Model_Section::get_catalog();
$id2Catalog = [];

foreach($catalog as $item) {
	$id2Catalog[$item->id] = $item;
	if ( ! empty($item->children)) {
		foreach($item->children as $child) {
			$id2Catalog[$child->id] = $child;
		}
	}
}

fwrite($fp, View::factory('smarty:page/export/yml/categories', ['catalog' => $catalog]));

fwrite($fp, '</categories>
    <delivery-options>
        <option cost="'.Model_Zone::min_price(Model_Zone::DEFAULT_ZONE, 1).'" days="0" order-before="12"/>
    </delivery-options><offers>');

$goods_written = 0;

define('EXPORTXML_SEX', 1951);
define('EXPORTXML_COLOR', 1952);
define('EXPORTXML_SIZE', 1949);
define('EXPORTXML_GROWTH', 1950);

$goodFilters = [
	Model_Section::EXPORTYML_CLOTHERS => [
		EXPORTXML_GROWTH	=> 'Рост',
		EXPORTXML_SIZE		=> 'Размер',
		EXPORTXML_COLOR		=> 'Цвет',
		EXPORTXML_SEX		=> 'Пол'
	]
];

$filterClosures = [

	EXPORTXML_SEX => function($name) {
		return ['name' => preg_match('#^девочка$#iu', $name) ? 'Женский' : 'Мужской'];
	},
			
	EXPORTXML_GROWTH => function($name) {
		if ( ! preg_match('#^([0-9\- ]+(см|м))$#iu', $name, $matches)) return FALSE;
		return [
			'name' => (int)$matches[1],
			'unit' => $matches[2]
		];
	},

	EXPORTXML_SIZE => function($name) {
		
		if( ! preg_match('#^([0-9\- ]+)$#iu', $name, $matches)) return FALSE;
		return [
			'name' => (int)$matches[1],
			'unit' => 'RU'
		];
	},

	EXPORTXML_COLOR => function($name) {
		return ['name' => mb_convert_case($name, MB_CASE_TITLE)];
	},
];

$goodFiltersLabels = [];
foreach($goodFilters as $type => $filters) {
	foreach($filters as $id => $label) {
		$goodFiltersLabels[$id] = $label;
	}
}

$goodFiltersIds = [];
$image_types = '500';
for ($heap_number = 0; $goods = Model_Good::for_yml($heap_size, $heap_number); $heap_number++) {
    $c = 0;
    $good_ids = [];
    foreach ($goods as &$g) {
        if ($id2Catalog[$g['section_id']]->parent_id > 0) {
            $section = $id2Catalog[$id2Catalog[$g['section_id']]->parent_id];
        } else {
            $section = $id2Catalog[$g['section_id']];
        }

        if (empty($section)) continue;
        $g['real_section'] = $section->id;

        if ($section->is_cloth()) {
            $goodFiltersIds[1][] = $g['id'];
        }
        $good_ids[] = $g['id'];
    }

    $goodFiltersV = [];
    if ( ! empty($goodFiltersIds)) {
        foreach ($goodFiltersIds as $filterType => $ids) {

            $filtersIds = array_keys($goodFilters[$filterType]);

            $result = DB::select('value_id', 'good_id', 'filter_id')
                ->from('z_good_filter')
                ->where('filter_id', 'IN', $filtersIds)
                ->where('good_id', 'IN', $ids)
                ->execute();

            $filterValuesIds = [];
            while ($row = $result->current()) {
                $filterValuesIds[$row['value_id']] = 1;
                $result->next();
                $goodFiltersV[$row['good_id']][$row['filter_id']][] = $row['value_id'];
            }
        }
    }

	$filterValues = [];
	if ( ! empty($filterValuesIds)) {
		
		$filterValuesIds = array_keys( $filterValuesIds );
		
		$result = DB::select('name', 'id')
            ->from('z_filter_value')
            ->where('id', 'IN', $filterValuesIds)
            ->execute();

		while ($row = $result->current()) {
			$filterValues[$row['id']] = $row['name'];
			$result->next();
		}
	}

    $images = Model_Good::many_images([$image_types], $good_ids);
    foreach($goods as &$g) { // тут передаем по ссылке, иначе послдний элемент дублируется
		// Если одновременно мальчик-девочка, то пол не передаем
		if ( ! empty($goodFiltersV[$g['id']][EXPORTXML_SEX]) && count($goodFiltersV[$g['id']][EXPORTXML_SEX]) > 1) {
			unset($goodFiltersV[$g['id']][EXPORTXML_SEX]);
		}
		

        if ( ! empty($goodFiltersV[$g['id']])) {
			foreach($goodFiltersV[$g['id']] as $filter_id => $valuesIds) {
				foreach($valuesIds as $key => $valueId) {
					$rr = $filterClosures[$filter_id]($filterValues[$valueId]);
					if ($rr !== FALSE) $valuesIds[$key] = $rr;
					break; // Яндекс примет только первое значение
				}
			}
		}
        //подготовка изображений
        $good_images = [];
        if (isset($images[$g['id']][$image_types]) && count($images[$g['id']][$image_types]) > 0) {
            //загрузка только 1 фото на товар
            $good_images[] = array_pop($images[$g['id']][$image_types]);

        } elseif ( ! empty($g['img1600'])) {
            $good_images[] = ORM::factory('file', $g['img1600']); // если нет картинок никаких, добавим 1600 - но она с вотермаркой
        }

        fwrite($fp, View::factory('smarty:page/export/yml/good', [
            'g'             => $g,
            'images'        => $good_images,
            'section'       => $id2Catalog[$g['real_section']],
            'filter_labels' => $goodFiltersLabels,
            'good_filter'   => ! empty($goodFiltersV[$g['id']]) ? $goodFiltersV[$g['id']] : [],
            'label'         => 'market.yandex.ru'
        ]));
        $goods_written++;
    }

    gc_collect_cycles();
}

fwrite($fp,'</offers>
</shop>
</yml_catalog>
');

fclose($fp);

exec('gzip -c '.$filename.' > '.$filename.'.gz'); // делаем gzip
$memory = memory_get_usage() - $start_memory;
Log::instance()->add(Log::INFO, 'Yandex Market XML file generated ok. Memory used: ' . $memory . '. Heap size: ' . $heap_size . '. Exported ' . $goods_written . ' offers.');