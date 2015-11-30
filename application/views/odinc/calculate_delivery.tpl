{if not empty($return.error)}
{$return.error}
{else}
{foreach from=$return item=r}
{$r.company}©{$r.tariff}©{$r.price}©{$r.days}
{/foreach}
{/if}
