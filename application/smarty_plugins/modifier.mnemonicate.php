<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.mnemonicate.php
 * Type:     modifier
 * Name:     price
 * Purpose:  show price specially tagged
 * -------------------------------------------------------------
 */
function smarty_modifier_mnemonicate($text)
{
    $convert_entities = array(
            '«' => '&laquo;',
            '»' => '&raquo;',
            '—' => '&mdash;',
            '‘' => '&lsquo;',
            '’' => '&rsquo;',
            '“' => '&ldquo;',
            '”' => '&rdquo;',
            '®' => '&reg;',
            'º' => '&deg;',
            '°' => '&deg;',
            'é' => '&eacute;',
            '<' => '&lt;',
            '>' => '&gt;',
            '"' => '&guot;',
            '©' => '&copy;',
            '±' => '&plusmn;',
            '±' => '&plusmn;',
            '²' => '&sup2;',
            '³' => '&sup3;',
            'α' => '&alpha;',
            'Α' => '&Alpha;',
            'ω' => '&omega;',
            'Ω' => '&Omega;',
        );
    
    $text = str_replace('&nbsp;', " ", $text);
    
    $text = str_replace(array_keys($convert_entities), $convert_entities, $text);
    
    $text = preg_replace('|[^a-zA-Zа-яА-Я0-9 \&\.\,\;\%\*\@\!\-\(\)\/]+|u', " ", $text);
    
    $text = preg_replace('|\s+|u', " ", $text);
    
    return $text;
}
