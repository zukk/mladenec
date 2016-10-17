<meta name="robots" content="{$robots|default:'index, follow'}" />
{if !empty($pager_prev)}
	<link rel="prev" href="http://{$host}{$pager_prev}" />
{/if}
{if !empty($pager_next)}
	<link rel="next" href="http://{$host}{$pager_next}" />
{/if}