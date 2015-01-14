{* пейджер по любым страницам, может быть ажаксовым *}
<div class="pager">
    <span>### {$from}-{$to} из {$total}</span>
    <div>
    {if $total gt $per_page}
    {section loop=$pages name=loop}
        {assign var=n value=$smarty.section.loop.iteration}

        {if ($n eq 1) OR ($n eq $pages) OR (abs($p-$n) < 8)}
            {assign var=hellip value=0}

            {if $p eq $n}
                <strong>{$p}</strong>
            {else}
				{if !$hash and $n eq 1}
	                <a href="{$base}{$link}{if $hash};{/if}" {if $fancy|default:0}rel="ajax" data-fancybox-type="ajax"{/if}>{$n}</a>
				{else}
	                <a href="{$base}{$link}{Pager::PARAM}={$n}{if $hash};{/if}" {if $fancy|default:0}rel="ajax" data-fancybox-type="ajax"{/if}>{$n}</a>
				{/if}
            {/if}
        {else}
            {if not $hellip}&hellip;{/if}
            {assign var=hellip value=1}

        {/if}
    {/section}
    {/if}
    </div>
</div>
