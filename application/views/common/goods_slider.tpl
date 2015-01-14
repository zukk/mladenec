<div class="slider" rel="{$rel}"{if !empty( $style)} style="{$style}"{/if}>
	{if $total gt 5}
	<i></i>
	{/if}
	{include file='common/goods.tpl' goods=$goods short=1}
	{if $total gt 5}
	<i></i>
	{/if}
</div>
