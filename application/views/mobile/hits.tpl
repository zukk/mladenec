<div class='mslider'>
{foreach from=$hits item=item}
	<a title='Перейти к просмотру товара' href='{$item->get_link(false)}' class='mslider-item'>
		<img src='/{$item->prop->image255->get_path()}' />
		<div class='mslider-item-name'>
			<span>{$item->group->name}</span>
			<br />{$item->name}
		</div>
		<div class='mslider-item-price'>
			{$item->price|price}
		</div>
	</a>
{/foreach}
<div class='clear'></div>
</div>