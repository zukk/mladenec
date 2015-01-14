<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.admin_qty.php
 * Type:     modifier
 * Name:     admin_qty
 * Purpose:  show qty text, for admin panel
 * -------------------------------------------------------------
 */
/**
 * @param $data
 * @param bool $text
 * @return string
 */
function smarty_modifier_admin_qty($data)
{
    if ($data instanceof Model_Good) $qty = $data->qty; // можно передать товар
    else $qty = intval($data);

    if ($qty < 0) // -1 = поставка от 2 дней
    { 
        $txt = 'У поставщика';
        $class = 'blue';
    }
    elseif($qty > 0)
    {
        $txt = $qty . '&nbsp;шт.';
        $class = 'green';
    }
    else
    {
        $txt = 'нет';
        $class = 'red';
    }
    
    return sprintf('<span class="%s">%s</span>', $class, $txt);
}
