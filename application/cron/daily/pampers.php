<?php

require('../../../www/preload.php');

$pampers = ORM::factory('pampers')->find_all()->as_array('id');

foreach( $pampers as &$pamper ){

	Mail::htmlsend('admin_pampers', array('o' => $pamper, 'time' => date('d.m.y H:59:59')), 'contest@new-point.ru', 'анкета памперс');
}