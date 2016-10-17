{if not $article}
<div id="breadcrumb">       
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span>Статьи</span> 
</div>

<div id="simple">
    <h1>Статьи</h1>

    <ul id="news_list" class="na">
    {foreach from=$articles item=n}
        <li>
            {$n->get_link()}
            <a href="{$n->get_link(0)}">{$n->minimg->get_img()}</a>
            <p>{$n->preview|nl2br}</p>
        </li>
    {/foreach}
    </ul>
    {$pager->html('Статьи')}
</div>

{else}

<div id="breadcrumb">           
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{$article->get_list_link()}" title="Статьи" itemprop="url"><span itemprop="title">Статьи</span></a>
    </span>
    &rarr;
    <span>{$article->name}</span>
</div>

<h1>{$article->name}</h1>

<div id="onew">
    <div class="cb">
        {$article->text}
    </div>

    {include file='common/social_comment.tpl' link=$article->get_link(0)}

    <a href="{$article->get_list_link()}" class="back cb">Вернуться к списку статей</a>
</div>

{/if}