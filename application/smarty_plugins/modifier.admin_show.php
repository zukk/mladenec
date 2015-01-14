<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.admin_qty.php
 * Type:     modifier
 * Name:     admin_show
 * Purpose:  is good visible for customers - text, for admin panel
 * -------------------------------------------------------------
 */
/**
 * @param $data
 * @param bool $text
 * @return string
 */
function smarty_modifier_admin_show($data)
{
    if ($data instanceof Model_Good) $show = ($data->show > 0); // можно передать товар
    else $show = ($data > 0);

    if ($show) // -1 = поставка от 2 дней
    {
        $txt = 'отобр.';
        $class = 'green';
    }
    else
    {
        $txt = 'скр.';
        $class = 'red';
    }
    
    return sprintf('<span class="%s">%s</small>', $class, $txt);
}
