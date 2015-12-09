<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.url_encode.php
 * Type:     modifier
 * Name:     qty
 * Purpose:  encode text for url
 * -------------------------------------------------------------
 */
/**
 * @param $url
 * @param bool $add_domain
 * @return string
 */
function smarty_modifier_urlencode($text, $add_domain = FALSE)
{
    return urlencode( ($add_domain ? 'http://mladenec-shop.ru/' : '') . $text );
}