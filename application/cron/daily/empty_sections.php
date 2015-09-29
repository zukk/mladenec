<?php
require('../../../www/preload.php');

// Check

$all_sections = DB::select('id',DB::expr('0 as `cnt`')) // Все разделы
            ->from('z_section')
            ->where('parent_id','!=',0)
            ->execute()->as_array('id','id');

$section_with_goods = DB::select(DB::expr('count(z_good.id) as `cnt`'),'section_id')
            ->from('z_good')
            ->where('z_good.active',     '=',  1)
            ->where('z_good.show',       '=',  1)
            ->where('z_good.qty',        '!=', 0)
            ->where('z_good.section_id', 'IN', array_keys($all_sections)) // чтобы древние зомби не ломали всю малину
            ->group_by('section_id')
            ->execute()
            ->as_array('section_id','cnt');

$empty_section_ids = $not_empty_section_ids = array();

foreach ($all_sections as $sid) {
    
    if ( ! empty($section_with_goods[$sid])) {
        
        $not_empty_section_ids[] = $sid;
        
    } else {
        
        $empty_section_ids[] = $sid;
        
    }
}
//echo("\r\nNESI" . implode(',',$not_empty_section_ids));

// У которых есть товары - тем сбрасываем дату отсутствия товаров
DB::update('z_section')
        ->set(array('empty_date'=>NULL))
        ->where('parent_id', '!=', 0)
        ->where('id', 'IN', $not_empty_section_ids)
        ->execute();


// У которых нет, и дата не проставлена - проставляем
DB::update('z_section')
        ->set(array('empty_date'=> date('Y-m-d')))
        ->where('id', 'IN', $empty_section_ids)
        ->where('parent_id', '!=', 0)
        ->where('empty_date', 'IS', NULL)
        ->execute();

echo("\r\nESI" . implode(',',$empty_section_ids));

$sections = ORM::factory('section')
        ->where('parent_id', '!=', 0)
        ->where('empty_date', '<', date('Y-m-d', time() - 864000)) // 10 дней
        ->find_all()->as_array('id');
        
if ( ! empty( $sections )) {
    
    $to = Conf::instance()->mail_empty_section;
	
    Mail::htmlsend('empty_sections', array('sections' => $sections), $to, 'Пустые категории!');
	
}
