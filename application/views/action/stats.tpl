<dl id="action_stats">
	<dt>Название акции:</dt>
		<dd>{$name}</dd>
	<dt>Акция действует:</dt>
		<dd>
			{if not empty($from)}c&nbsp;<nobr>{$from|date_ru}</nobr>{/if}
			{if not empty($to)}
					по&nbsp;<nobr>{$to|date_ru}</nobr>
				{else}
					{if $presents_instock}до окончания подарков{else}бессрочно{/if}
			{/if}
		</dd>
	<dt>Участвует товаров:</dt>
		<dd>{if $total}весь ассортимент{else}{$visible_goods_active}{/if}</dd>

</dl>