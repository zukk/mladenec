<div class='mvitrina'>
	{foreach from=$vitrina item=item}
		<a title='{$item->name}' href='/catalog/{$item->translit}'><img src="/{if !empty( $item->image93 )}{$item->img93->get_path()}{else}i/no.png{/if}" /> {$item->name} <i></i></a>
	{/foreach}
</div>