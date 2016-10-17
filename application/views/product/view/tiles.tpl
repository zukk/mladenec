{if not empty($goods)}
	{if not empty($is_cloth)}
		{include file='product/view/tiles/clother.tpl'}
	{elseif not empty($is_topbar)}
		{include file='product/view/tiles/topbar.tpl'}
	{else}
		{include file='product/view/tiles/general.tpl'}
	{/if}
{/if}