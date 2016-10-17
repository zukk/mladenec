<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.price.php
 * Type:     modifier
 * Name:     price
 * Purpose:  show price specially tagged
 * -------------------------------------------------------------
 */
function smarty_modifier_price($price, $notags = FALSE)
{
    $floor = floor($price);

    if ($floor == $price) return number_format($floor, 0, '', ' ').($notags === FALSE ? '<small> р.</small>': '');
    return str_replace('.', ($notags === FALSE ? '<small>.': '.'), number_format($price, 2, '.', ' ')).($notags === FALSE ? ' р.</small>': '');
}
