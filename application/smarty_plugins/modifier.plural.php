<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.plural.php
 * Type:     modifier
 * Name:     plural
 * Purpose:  show words in special plural form depending on number
 * -------------------------------------------------------------
 */
function smarty_modifier_plural($word, $number, $with_number = TRUE)
{
    static $array = array( // 1 => (2, 5)
        'вариант' => array('варианта', 'вариантов'),
        'отзыв' => array('отзыва', 'отзывов'),
        'мнение' => array('мнения', 'мнений'),
        'товар' => array('товара', 'товаров'),
        'найден' => array('найдено', 'найдено'),
        'штуке' => array('штуки', 'штук'),
    );

    $mod10 = $number % 10;
    $mod100 = $number % 100;

    if ($mod10 == 0 or ($mod10 > 4) or ($mod100 > 9 and $mod100 < 21)) return ($with_number?$number:'') . ' ' . $array[$word][1]; // 5
    if ($mod10 == 1) return ($with_number?$number:'') .' '.$word; // 1
    if ($mod10 > 1 and $mod10 < 5) return ($with_number?$number:'') . ' ' . $array[$word][0]; // 2
    return $number;
}