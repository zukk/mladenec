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
				<ul>
					{assign var=subs value=$ch->sub|count}
					{foreach from=$ch->sub item=n key=k name=n}
						<li {if $smarty.foreach.n.iteration gt $column}class="hide"{/if}>
							{if $ch->settings.sub eq Model_Section::SUB_BRAND}
								<label data-url="{$sublink}?b={$k}" title="{$n}" class="label">{$n}</label>
							{elseif $ch->settings.sub eq Model_Section::SUB_FILTER}
								{if $item->id eq Model_Section::CLOTHS_ROOT or $ch->settings.list neq Model_Section::LIST_GOODS}
									<label data-url="{$ch->get_link(0, $k)}" title="{$n}" class="label">{$n}</label>
								{else}
									<label data-url="{$sublink}?f{$ch->settings.sub_filter}={$k}" title="{$n}" class="label">{$n}</label>
								{/if}
							{/if}
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