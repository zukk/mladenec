<div class='mh1'>{$section->name}</div>
<div class='mvitrina'>
	{foreach from=$section->children item=item}
		<a title='{$item->name}' href='/{$item->translit}/'><img src="/{if !empty( $item->image93 )}{$item->img93->get_path()}{else}i/no.png{/if}" /> {$item->name} <i></i></a>
	{/foreach}
</div>
<div class='mh2'>Хиты продаж</div>
{include file='mobile/hits.tpl'}
