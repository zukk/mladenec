<?php

require('../../../www/preload.php');

ob_end_clean();

$Good = ORM::factory('good');

$result = DB::query(Database::SELECT, "
SELECT g.id, section_id, spoiler_title, `desc`, spoiler, spoiler2_title, spoiler2, spoiler3_title, spoiler3
FROM z_good g INNER JOIN z_good_prop p ON g.id = p.id
")->execute();

$section2tabs = [];

while( $c = $result->current() ){

	if( ! empty( $c['spoiler_title'] ) && ! empty( $c['spoiler'] ) ){
		
		$section2tabs[$c['section_id']][$c['spoiler_title']] = 1;
		DB::query(Database::INSERT, "INSERT INTO z_good_text SET name = '$c[spoiler_title]', content = '" . mysql_real_escape_string($c['spoiler']) . "', good_id = $c[id]")->execute();
	}
	
	if( ! empty( $c['spoiler2_title'] ) && !empty( $c['spoiler2'] ) ){
		
		$section2tabs[$c['section_id']][$c['spoiler2_title']] = 1;
		DB::query(Database::INSERT, "INSERT INTO z_good_text SET name = '$c[spoiler2_title]', content = '" . mysql_real_escape_string($c['spoiler2']) . "', good_id = $c[id]")->execute();
	}
	
	if( ! empty( $c['spoiler3_title'] ) && !empty( $c['spoiler3'] ) ){
		
		$section2tabs[$c['section_id']][$c['spoiler3_title']] = 1;
		DB::query(Database::INSERT, "INSERT INTO z_good_text SET name = '$c[spoiler3_title]', content = '" . mysql_real_escape_string($c['spoiler3']) . "', good_id = $c[id]")->execute();
	}
	
	if( ! empty( $c['desc'] ) ){
		
		$section2tabs[$c['section_id']]['Полное описание'] = 1;
		DB::query(Database::INSERT, "INSERT INTO z_good_text SET name = 'Полное описание', content = '" . mysql_real_escape_string($c['desc']) . "', good_id = $c[id]")->execute();
	}
	
	$result->next();
}

echo 'start sections' . "\n";
foreach( $section2tabs as $sectionId => &$names ){
	$names = array_keys( $names );
	$names = array_merge( $names, ['Отзывы'] );
	$section = ORM::factory('section', $sectionId );
	
	if( $sectionId > 0 && $section->loaded() ){
		
		$section->setting('goodTabs', $names );

		$section->save();
		echo 'section ' . $sectionId . ' saved' . "\n";
	}
}
unset( $names );
