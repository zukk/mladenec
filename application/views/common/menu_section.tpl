{* левое меню для категорий верхнего уровня - повторяет выпадающее, но полное *}
{strip}
{assign var=column value=8}
<div id="menu">
	{if $vitrina eq 'kotdog'}
		<a href="/" id="logo"><img src="/i/kotdog/head.png" alt="Kot-dog.ru" /></a>
	{/if}

	{assign var=item value=$top_menu[$section->id]}
	<div id="ff">
{if not empty($item->children)}
		{foreach from=$item->children item=ch key=kk}
			{assign var=sublink value=$ch->get_link(0)}
			<strong>{$ch->name} <i class="toggler"></i></strong>

            {if not empty($ch->sub)}
                <ul class="ms">
                    {assign var=subs value=$ch->sub|count}
                    {foreach from=$ch->sub item=n key=k name=n}
                        <li class="item {if $smarty.foreach.n.iteration gt $column}hide{/if}">
                            <a href="{$n.href}">{$n.name} <small>{$n.qty}</small></a>
                        </li>
                        {if $smarty.foreach.n.total gt $column and $smarty.foreach.n.iteration eq $column}
                            <li><a class="toggler">+ Показать все</a></li>
                        {/if}
                    {/foreach}
                </ul>
            {/if}

		{/foreach}
{/if}
	</div>
</div>
{/strip}