<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.time1c.php
 * Type:     modifier
 * Name:     time1c
 * Purpose:  Datetime formatting from unix timestamp to 1C format
 * -------------------------------------------------------------
 */
/**
 * @param $data
 * @param bool $text
 * @return string
 */
function smarty_modifier_time1c($data)
{
    $timestamp = intval($data);
    
    
    return date('d-m-y,H:i:s ',$timestamp);
}
