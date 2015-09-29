{foreach from=$terminals item=t}
{$t->id}©{$t->address}©{$t->lat},{$t->lng}©{$t->pay_cards}
{/foreach}