{* товары для слайдера *}

{if not empty($goods)}
    {if empty($imgs)}{assign var=imgs value=NULL}{/if}
    {if empty($short)}{assign var=short value=NULL}{/if}
<ul>
    {foreach from=$goods item=g name=g}
    <li class="g short">
        {include file='common/good.tpl' g=$g}
    </li>
    {/foreach}
</ul>
    {include file="google/impressions.tpl" ga_list=$ga_list|default:'slider' ga_ajax=$ga_ajax|default:""}
{/if}