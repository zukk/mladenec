<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.qty.php
 * Type:     modifier
 * Name:     qty
 * Purpose:  show qty text with icon depended on qty
 * -------------------------------------------------------------
 */
/**
 * @param $data
 * @param bool $text
 * @return string
 */
function smarty_modifier_qty_bars($data)
{
    if ($data instanceof Model_Good) { // можно передать товар
        $big = $data->big;
        $qty = $data->qty;
    } else {                    // остальное рассматривается как мелкогабаритка
        $qty = intval($data);
        $big = 0;
    }

    if ($big) { // крупногабаритка
        if ($qty == -1) { // специальное значение - есть на складе поставщика, используется только для крупногабаритки
            $class = 'supplier';
        } elseif ($qty == 0) {
            $class = '';
        } else {
            $class = 'many';
        }
    } else {
        if ($qty < 1) {
            $class = '';
        } elseif ($qty < 10) {
            $class = 'few';
        } elseif ($qty < 100) {
            $class = 'enough';
        } else {
            $class = 'many';
        }
    }

    return $class;
}
