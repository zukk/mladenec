<div id="breadcrumb">
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{Route::url('action_list')}"><span itemprop="title">Акции</span></a>
    </span>
    &rarr;
    <span>Архив акций</span>
</div>

<div id="simple">
    <h1>Архив акций</h1>

    <ul id="action_list" class="na">
    {foreach from=$actions item=n}
    <li>
        {if $n->type == 1}<a href="{$n->get_link(0)}" class="fl"><img src="/i/podarok.png" alt="Акция с подарками" width="50" /></a>{/if}
        {if $n->type == 2}<a href="{$n->get_link(0)}" class="fl"><img src="/i/sale.png" alt="Акция со скидкой" width="50" /></a>{/if}
        {$n->get_link()}
        <p>{$n->preview|strip_tags|nl2br}</p>
    </li>
    {/foreach}
    </ul>
    {$pager->html('Акции')}
</div>
<div class="fr cr">
    <a href="{Route::url('action_list')}">Все акции</a>
</div>