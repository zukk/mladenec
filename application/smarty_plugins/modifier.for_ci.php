<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.for_ci.php
 * Type:     modifier
 * Name:     for_ci
 * Purpose:  strip \t\n\r and tags from string
 * -------------------------------------------------------------
 */
function smarty_modifier_for_ci($txt)
{
    return trim(preg_replace('~\s+~', ' ', str_replace(array("\t", "\n", "\r"), ' ', strip_tags($txt))));
}