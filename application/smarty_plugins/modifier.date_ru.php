<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.date_ru.php
 * Type:     modifier
 * Name:     plural
 * Purpose:  show dates in russian language like `13 октября 2012`
 * -------------------------------------------------------------
 */
function smarty_modifier_date_ru($date, $for_order = FALSE)
{
    static $month = array( //
        '01' => 'января',
        '02' => 'февраля',
        '03' => 'марта',
        '04' => 'апреля',
        '05' => 'мая',
        '06' => 'июня',
        '07' => 'июля',
        '08' => 'августа',
        '09' => 'сентября',
        '10' => 'октября',
        '11' => 'ноября',
        '12' => 'декабря',
    );

    static $weekday = array(
      1 => 'понедельник',
      2 => 'вторник',
      3 => 'среда',
      4 => 'четверг',
      5 => 'пятница',
      6 => 'суббота',
      7 => 'воскресенье',
    );

    if (is_int($date)) {
        $date = date('y-m-d-N', $date);
    } else {
        $date = date('y-m-d-N', strtotime($date));
    }
    list($y, $m, $d, $n) = explode('-', trim($date, '-'));

    return $for_order ?
        sprintf('%s %s/%s/%s', $weekday[$n], $d, $m, $y) :
        sprintf('%s %s 20%s', $d, $month[$m], $y);
}