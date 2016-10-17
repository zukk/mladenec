{foreach from=$catalog item=c}
{if not empty($c->id)}
<category id="{$c->id}" parentId="0">{Txt::clean_rude_symbols($c->name|escape:'html')}</category>
{if $c->children}{foreach from=$c->children item=ch}<category id="{$ch->id}" parentId="{$c->id}">{Txt::clean_rude_symbols($ch->name|escape:'html')}</category>{/foreach}{/if}
{/if}
{/foreach}