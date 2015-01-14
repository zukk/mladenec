<div id="breadcrumb">
    <a href="/">Главная</a> |
</div>

<div id="simple">
    <h1>Бренды</h1>

    <ul class="brands_list">
    {foreach from=$brands item=brand}
    <li><a href="/search?q={urlencode($brand->name)}">{$brand->name}</a></li>
    {/foreach}
    </ul>
    {$pager->html('Бренды')}
</div>