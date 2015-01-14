<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.week_day.php
 * Type:     modifier
 * Name:     week_day
 * Purpose:  show checkboxes for weekdays according to value or show values only
 * -------------------------------------------------------------
 */
function smarty_modifier_week_day($wd, $show_only = FALSE)
{
    if ($show_only) {
        $return = array();
        foreach(Model_Zone_Time::$week_days as $name => $code) {
            if ($code & $wd) $return[] = $name;
        }
        return implode(',', $return);
    } else {
        $return = '<table class="table-simple"><tbody><tr>';
        foreach(Model_Zone_Time::$week_days as $name => $code) {
            $return .= '<td>'.$name.'&nbsp;'.Form::checkbox('week_day[]', $code, ($code & $wd) != 0, array('title' => $name)).'</td>';
        }
        $return .= '</tr><tbody></table>';
        return $return;
    }

}