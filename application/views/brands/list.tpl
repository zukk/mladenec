<div id="breadcrumb">       
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span>Бренды</span> 
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