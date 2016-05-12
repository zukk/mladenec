<script>
    allowGoodsTopBar = false;

    // required object
    window.ad_product = {
        id: "{$cgood->id}",   // required
        vendor: "{$cgood->brand->name|escape:html}",
		price: "{$cgood->get_price()}",
        url: "http://{$host}{$cgood->get_link(false)}",
        picture: "http://{$host}{$cgood->prop->image255->get_img(0)}",
        name: "{$cgood->group_name|escape:html} {$cgood->name|escape:html}",
        category: "{$section->id}"
    };
</script>

{include file='common/retag.tpl' level=2}

<div id="breadcrumb">
               
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1" itemref="breadcrumb-2">
        <a href="{$parent->get_link(false)}" itemprop="url"><span itemprop="title">{$parent->name}</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-2" {if $cgood->is_cloth()}itemref="breadcrumb-3"{/if}>
        <a href="{$section->get_link(false)}" itemprop="url"><span itemprop="title">{$section->name}</span></a>
    </span>
    {if $cgood->is_cloth()}
    &rarr; 
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-3">
        <a href="{$section->get_link(0, $big_filter.id)}" itemprop="url"><span itemprop="title">{$big_filter.value}</span></a>
    </span>
    {/if}
    &rarr;
    <span>{$group->name} {if not $group->good}{$cgood->name}{/if}</span>
    
    
    {if $section->id eq Model_Section::MILK_ID}<abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>{/if}
</div>

{assign var=good_pics value=$cgood->get_images()}
{capture assign=good_name}{$group->name|escape:html} {if not $group->good}{$goods[{$prop->id}]->name|escape:html}{/if}{/capture}

{if $cgood->is_cloth()}
    {assign var=imgsize value='380x560'}
{else}
    {assign var=imgsize value='380'}
{/if}

<div id="ajax-product-card">
	{if $cgood->big}
	    {include file='product/view/big.tpl'}
	{else}
	    {include file='product/view/inner.tpl'}
	{/if}
</div>

{if $config->rr_enabled}
    <div class="cl rr_slider" title="С этим товаром смотрят:" data-func="UpSellItemToItems" data-param="{$cgood->id}"></div>
{/if}

{*if not empty($frequent)}
    <div class="cl">
        <h2 class="h1">{$slider_header|default:'С этим товаром также заказывают:'}</h2>
        {assign var=rel value="/frequent/"|cat:$prop->id}
        {include file='common/goods_slider.tpl' total=$cgood->totalFrequently goods=$frequent images=$images rel=$rel}
    </div>
{/if*}

<script>
var good_id = {$prop->id};
$(document).ready(function() {
    $('#stats_for').trigger('change');
    $('#reviews_for').trigger('change');
});
</script>
<script src="/j/zoombox.js"></script>

{if not empty($goods[{$prop->id}]->upc)}
    <script src="http://cts.channelintelligence.com/321604975_landing.js"></script>
{/if}

{* google adwords remarketing params *}
<script>
    var google_tag_params = {
        ecomm_prodid: {$cgood->id},
        ecomm_pagetype: 'product',
        ecomm_totalvalue: {$cgood->price},
        ecomm_category: '{$cgood->section->name}'
    };
</script>

{* Retail rocket *}
{if $config->rr_enabled}
<script>
    rrApiOnReady.push(function() {
        try{ rrApi.view({$cgood->id}); } catch(e) { }
    })
</script>
{/if}

{* findologic product tracking *}
{if $config->instant_search == 'findologic'}
<script>
    // all parameters are optional, but we recommend to set at minimum productSKU and productName
    _paq.push(['setEcommerceView',
        "{$cgood->code}",       // (required) SKU: Product unique identifier; e.g. "a-123"
        "{$good_name|escape}",  // (optional) Product name; e.g. "Брюки"
        ["{$section->name}"],   // (optional) Product category, or array of up to 5 categories; e.g. article`s first category-path "Мужская одежда -> Брюки": ["Мужская одежда", "Брюки"]
        {$cgood->price}         // (optional) Product Price as displayed on the page; e.g. 1.23
    ]);
    _paq.push(['trackPageView']);
</script>
{/if}

<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
<script type="text/javascript">
    window.criteo_q = window.criteo_q || [];
    window.criteo_q.push(
            { event: "setAccount", account: {$cur_user->id} },
            { event: "setEmail", email: "{$cur_user->email}" },
            { event: "setSiteType", type: "d" },
            { event: "viewItem", item: "{$cgood->id}" }
    );
</script>