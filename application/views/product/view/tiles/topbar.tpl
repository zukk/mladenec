{foreach from=$goods item=g name=g}
	{capture assign=name}{$g->group_name|escape:'html'} {if $g->grouped eq 1}{$g->name|escape:'html'}{/if}{/capture}
	<div style="margin: 6px 3px;">
		<a href="{$g->get_link(false)}">{$name}</a>
	</div>
{/foreach}

<div id='user_history_bottom'>
	<a href="{Route::url('user_goods')}">вся история просмотров </a>&raquo;
</div>