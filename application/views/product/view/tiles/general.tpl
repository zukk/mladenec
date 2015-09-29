<div class="good_tiles {if $row|default:4 eq 3}row3{/if}">
{foreach from=$goods item=g name=g}
    <div class="g short">
        {include file='common/good.tpl'}
    </div>
{/foreach}
</div>
{include file="google/impressions.tpl"}
