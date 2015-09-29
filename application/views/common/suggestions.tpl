{if ! empty($goods)}
	{foreach from=$goods item=g name=g}
		{assign var=link value=$g->get_link(0)}
		{capture assign=name}<b>{$g->group_name}</b> {$g->name}{/capture}
		<a data-id="{$g->id}" style="width:100%; display: block;" href="{$link}"><div style="padding: 5px;">{$name}</div></a>
	{/foreach}
{/if}
