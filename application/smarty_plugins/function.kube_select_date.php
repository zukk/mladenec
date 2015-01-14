<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.kube_select_date.php
 * Type:     function
 * Name:     kube_select_date
 * Purpose:  outputs a date selector 
 * -------------------------------------------------------------
 */
function smarty_function_kube_select_date($params, Smarty_Internal_Template $template) {
    $field_array = 'date';
    if ( ! empty($params['field_array'])) $field_array = $params['field_array'];
    
    $start_year = '-3';
    if     ( ! empty($params['start_year']))    $start_year = $params['start_year'];
    if     ( FALSE !== strpos($start_year,'+')) $start_year = date('Y') + str_replace('+','',$start_year);
    elseif ( FALSE !== strpos($start_year,'-')) $start_year = date('Y') - str_replace('-','',$start_year);
    
    $end_year = '+3';
    if     ( ! empty($params['end_year']))      $end_year = $params['end_year'];
    if     ( FALSE !== strpos($end_year,'+'))   $end_year = date('Y') + str_replace('+','',$end_year);
    elseif ( FALSE !== strpos($end_year,'-'))   $end_year = date('Y') - str_replace('-','',$end_year);
    
    $all_empty = NULL;
    if     ( ! empty($params['all_empty'])) $all_empty = $params['all_empty'];
    
    
    $return = '<ul class="forms-inline-list"><li><select name="' . $field_array . '[Date_Day]">';
    for ($i = 1; $i<=31;$i++) $return .= '<option>'.$i.'</option>';
    $return .= '</select><div class="forms-desc">День</div></li><li><select name="' . $field_array . '[Date_Month]">';
    for ($i = 1; $i<=12;$i++) $return .= '<option>'.$i.'</option>';
    $return .= '</select><div class="forms-desc">Месяц</div></li><li><select name="' . $field_array . '[Date_Year]">';
    for ($i = $start_year; $i<=$end_year;$i++) $return .= '<option>'.$i.'</option>';
    $return .= '</select><div class="forms-desc">Год</div></li></ul>';

    return $return;
}