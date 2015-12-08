{foreach from=$catalog item=c}
    {if not empty($c->wikimart_cat_id)}
        <category id="{$c->wikimart_cat_id}">{Txt::clean_rude_symbols($c->wiki_categories->name|escape:'html')}</category>
        {if not empty($c->children)}
            {foreach from=$c->children item=ch}
                <category id="{$ch->wikimart_cat_id}" parentId="{$c->wikimart_cat_id}">{Txt::clean_rude_symbols($ch->name|escape:'html')}</category>
            {/foreach}
        {/if}
    {/if}
{/foreach}