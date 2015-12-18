{foreach from=$catalog item=c}
    {if not empty($c.category_id)}
        <category id="{$c.category_id}" parentId="{$c.parent_id}">{Txt::clean_rude_symbols($c.wiki_name|escape:'html')}</category>
    {/if}
{/foreach}