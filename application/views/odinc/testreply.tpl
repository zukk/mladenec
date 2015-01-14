Заголовки
{foreach $headers|default:[] as $h}
{$h}
{/foreach}
Переданные строки:
{foreach $strings|default:[] as $s}
{$s};    
{/foreach}