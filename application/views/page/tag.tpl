<div id="breadcrumb">           
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{Route::url('map')}" title="Карта сайта" itemprop="url"><span itemprop="title">Карта сайта</span></a>
    </span>
    &rarr;
    <span>Товары по категориям</span>
</div>

<h1>Товары по категориям</h1>

{foreach from=$tree item=t}
{if $t.depth eq 1}
    <h3>{$t.name}</h3>
{elseif $t.depth == 2}
    <h4 style="margin-left:20px;">{$t.name}</h4>
{else}
    <strong style="margin-left:40px;">{$t.name}</strong><br />
{/if}

<div style="margin-left:{$t.depth*20}px">
{foreach from=$tag_by_section[$t.id] item=tt}
{$tt->get_link()}<br />
{/foreach}
</div>

{/foreach}

<div>
    {foreach from=$tag_by_section[0] item=tt}
        {$tt->get_link()}<br />
    {/foreach}
</div>
