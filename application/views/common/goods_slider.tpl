<div class="slider" data-url="{$rel}" data-page="0" {if not empty($style)} style="{$style}"{/if}>
	{if $total gt 5}
	<i></i>
	{/if}
	{include file='common/goods.tpl' goods=$goods short=1}
	{if $total gt 5}
	<i></i>
	{/if}
</div>
