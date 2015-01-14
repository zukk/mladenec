{if not $article}
<div id="breadcrumb">
    <a href="/">Главная</a> |
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
    <a href="/">Главная</a> &rarr;
    <a href="{$article->get_list_link()}" title="Статьи">Статьи</a>
</div>

<h1>{$article->name}</h1>

<div id="onew">
    <div class="cb">
        {$article->text}
    </div>

    <a href="{$article->get_list_link()}" class="back cb">Вернуться к списку статей</a>
</div>


{/if}