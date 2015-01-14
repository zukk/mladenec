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
function smarty_modifier_qty($data, $text = TRUE)
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
            $class = 'wait';
            $txt = 'Доставка в течение <strong>2-5 дней</strong>';
        } elseif ($qty == 0) {
            $class = 'no';
            $txt = 'Товар закончился';
        } else {
            $class = 'many';
            $txt = 'Есть на складе';
        }
    } else {
        if ($qty < 1) {
            $class = 'no';
            $txt = 'Товар закончился';
        } elseif ($qty < 10) {
            $class = 'small';
            $txt = 'Скоро закончится';
        } elseif ($qty < 100) {
            $class = 'enough';
            $txt = 'Есть на складе';
        } else {
            $class = 'many';
            $txt = 'Много на складе';
        }
    }

    if ($text) {
        return sprintf('<small class="%s"><i></i> %s</small>', $class, $txt);
    } else {
        return sprintf('<i class="%s" title="%s"></i>', $class, strip_tags($txt));
    }
}
