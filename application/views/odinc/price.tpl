{$old_doc_id = 0}
ОШИБКИ:
{foreach $errors as $e}
{$e}
{/foreach}
КОНЕЦОШИБОК
{foreach $answer as $doc_id => $prices}
ЦЕНЫ:{$doc_id}
{foreach $prices as $id1c => $time}
{$id1c}©{$time|time1c}
{/foreach}
КОНЕЦЦЕН
{/foreach}