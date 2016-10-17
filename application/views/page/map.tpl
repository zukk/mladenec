{if not  empty($section)}

	<div id="breadcrumb">                       
            <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
                <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
            </span>
            &rarr;
            <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
                <a href="{Route::url('map')}" itemprop="url" title="Карта сайта"><span itemprop="title">Карта сайта</span></a>
            </span>
            &rarr;
            <span>Карта товарной категории &laquo;{$section->name}&raquo;</span>
	</div>

	<h1>Карта товарной категории &laquo;{$section->name}&raquo;</h1>

	{foreach from=$goods item=g}
		<a href="{$g->get_link(0)}">{$g->group_name} {$g->name}</a><br />
	{/foreach}

{else}

	<h1>Карта сайта</h1>

	<div class="site_map">
		{$menu}
	</div>

	<a href="/tag" class="tag_map">Товары по категориям</a>

	<h2 class="mt">Каталог товаров</h2>
	<div class="cat_map">
		{foreach from=$catalog item=item name=i key=k}
			<div class="{if $td mod 4 eq 0}cl{/if}">
				{assign var=td value=$td+1}
				<a href="{$item->get_link(0)}"><strong>{$item->name|replace:' и ':' и&nbsp;'}</strong></a>
				{if $item->children}
					<ul>
						{foreach from=$item->children item=ch key=kk}
							<li><a href="{Route::url('map_section', $ch->as_array())}">{$ch->name}</a></li>
						{/foreach}
					</ul>
				{/if}
			</div>
		{/foreach}
	</div>

{/if}

