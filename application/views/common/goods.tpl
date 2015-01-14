{* товары для слайдера *}

{if not empty($goods)}
    {if empty($imgs)}{assign var=imgs value=NULL}{/if}
    {if empty($short)}{assign var=short value=NULL}{/if}
<ul>
    {foreach from=$goods item=g name=g}
        {capture assign=name}{$g->group_name|escape:'html'} {$g->name|escape:'html'}{/capture}
        {capture assign=link}{$g->get_link(0)}{/capture}
    <li class="g {if $short}short{/if}">
        <span class="stars"><span style="width:{$g->rating*20}%"></span></span>
        <a class="review" title="{'отзыв'|plural:$g->review_qty}" href="{$g->get_review_link()}" >{$g->review_qty}</a>
        <a href="{$link}" title="{$name}"><img src="{$g->get_img($imgs)}" alt="{$name}" /></a>
        <a href="{$link}" title="{$name}">{$name}</a>
        {include file='common/buy.tpl' good=$g can_buy=$g->id}
        
        <div class="price">
            {if $g->old_price > 0}<span><del>{$g->old_price|price}</del></span>{/if}
            
            <strong>{$g->price|price}</strong>
            {if not empty($price[$g->id])}
                <abbr>{$price[$g->id]|price}</abbr>
            {/if}
        </div>
    </li>
    {/foreach}
</ul>
{/if}