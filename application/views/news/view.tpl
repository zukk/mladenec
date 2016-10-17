<div id="breadcrumb">       
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{$new->get_list_link()}" title="Новости" itemprop="url"><span itemprop="title">Новости</span></a>
    </span>
    &rarr;
    <span>{$new->name}</span> 
</div>

<h1>{$new->name}</h1>

<div id="onew">
    <small>{$new->date|date_ru}</small>
    <div class="cb">
        {$new->text}
    </div>

    {include file='common/social_comment.tpl' link=$new->get_link(0)}

    <a href="{$new->get_list_link()}" class="back cb">Вернуться к списку новостей</a>
</div>