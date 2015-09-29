<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.child_age.php
 * Type:     modifier
 * Name:     child_age
 * Purpose:  show child age from date of birth
 * -------------------------------------------------------------
 */
/**
 * @param $date
 * @return string
 */
function smarty_modifier_child_age($date)
{
    if(strpos($date, '.') !== false){
        $dob = explode('.', $date);
        $date = $dob[2].'-'.$dob[1].'-'.$dob[0];
    }
    $age = floor((time() - strtotime($date)) / (24*60*60*365));
    if($age>0) {
        $age.= ' '.format_num($age, ['год', 'года', 'лет']);
    }
    if($age < 2) {
        $current_month = date('n');
        $birth_month   = date('n', strtotime($date));
        $month = ($birth_month > $current_month) ? 12-$birth_month+$current_month : $current_month-$birth_month;
        if(date('d', strtotime($date)) > date('d')) $month--;
        
        if($month>0) {            
            if($age == 0) {
                $age = $month.' '.format_num($month, ['месяц', 'месяца', 'месяцев']);
            } else
                $age .= ' '.$month.' '.format_num($month, ['месяц', 'месяца', 'месяцев']);
        } elseif($age == 0) {
            $age = '11 месяцев';
        }
    }
    return $age;
} 

function format_num($number, $endingArray)
{
    $number = $number % 100;
    if ($number>=11 && $number<=19) {
        $ending=$endingArray[2];
    }
    else {
        $i = $number % 10;
        switch ($i)
        {
            case (1): $ending = $endingArray[0]; break;
            case (2):
            case (3):
            case (4): $ending = $endingArray[1]; break;
            default: $ending=$endingArray[2];
        }
    }
    return $ending;
}