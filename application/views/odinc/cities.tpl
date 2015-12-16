{foreach from=$cities item=c}
{$c->id}©{$c->name}©{$c->region_id}©{$c->region->name}
{/foreach}