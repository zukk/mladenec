<div id="breadcrumb">
    <a href="/">Главная</a> |
</div>

<div id="simple">
    <h1>Новости</h1>

    <ul id="news_list" class="na">
    {foreach from=$news item=n}
    <li>
        <small>{$n->date|date_ru}</small>
        {$n->get_link()}
        <a href="{$n->get_link(0)}">{$n->image->get_img()}</a>
        <p>{$n->preview|nl2br}</p>
    </li>
    {/foreach}
    </ul>
    {$pager->html('Новости')}
</div>