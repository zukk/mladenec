{foreach from=$coupon item=c}
{if $c->type neq Model_Coupon::TYPE_SUM or ($c->type eq Model_Coupon::TYPE_SUM and $c->sum gt 0)}{$c->name}©{$c->sum}©{$c->min_sum}©{$c->uses}{/if}

{/foreach}