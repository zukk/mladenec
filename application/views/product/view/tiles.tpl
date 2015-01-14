{if not empty($goods)}
	{if not empty($is_cloth)}
		{include file='product/view/tiles/clother.tpl'}
	{else}
		{include file='product/view/tiles/general.tpl'}
	{/if}
{/if}