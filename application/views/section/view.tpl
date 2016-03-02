<script>
window.ad_category = "{$section->id}";   // required
allowGoodsTopBar = false;
</script>

{include file='common/retag.tpl' level=1}

{if not isset($column)}{assign var=column value=11}{/if}

<div id="breadcrumb">
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"{if ! empty($parent)} itemref="breadcrumb-1"{/if}>
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    {if ! empty($parent)} 
        &rarr;        
        <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1"{if ! empty($third_level)} itemref="breadcrumb-2"{/if}>
            <a href="{$parent->get_link(false)}" itemprop="url"><span itemprop="title">{$parent->name}</span></a>
        </span>
        {if ! empty($third_level)}
            &rarr;
             <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-2">
                <a href="{$section->get_link(false)}" itemprop="url"><span itemprop="title">{$section->name}</span></a>
            </span>
        {/if}
    {/if}
    &rarr;
    <span>{if ! $section->is_cloth() and ! $third_level}{$section->h1}{/if} {if $third_level}{$third_level}{/if}</span>
    <i></i>
</div>

<div {if empty($subs)}class="yell"{/if}>
    <h1>{$seoname}
        {if $section->id eq Model_Section::MILK_ID}<abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>{/if}
    </h1>
</div>

{assign var=pops value=0}
{foreach from=$block_links item=block_link}
    {if !empty($block_link->blocklinksanchor->title)}
        {assign var=pops value=1}
    {/if}
{/foreach}

{if $pops == 1}
    {if $block_links && !empty($block_links)}
        <div id="tagsinsection">
            <div class="tagsinsectiontitle">
                <div id="tagsinsectionlink">
                    <b style="float: left">Популярное:&nbsp;</b>
                    {foreach from=$block_links item=block_link}
                        <a href="/{$block_link->blocklinksanchor->url}">{$block_link->blocklinksanchor->title}</a>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}
{/if}

{if ! empty($subs)}{* есть подкатегориии - показываем их *}

    {assign var=item value=$top_menu[$section->id]}
    <table id="subs" class="subs{$row}">
    <tr>
    {foreach from=$item->children item=s name=s}
        <td>
            {assign var=link value=$s->get_link(0)}
            <a href="{$link}" class="big">{$s->name}</a><a href="{$link}">{$s->img->get_img()}</a>

            {if $s->sub && empty($hide_sub)}
            <ul>
                {assign var=subs value=$s->sub|count}
                {foreach from=$s->sub item=sub key=k name=n}
                    <li {if $smarty.foreach.n.iteration gt $column}class="hide"{/if}>
                        <a href="{$sub.href}">{$sub.name}</a><abbr abbr="Ассортимент товаров">{$sub.qty}</abbr>
                    </li>
                {/foreach}
                {if $smarty.foreach.n.total gt $column}
                    <li><a class="toggler">+ Показать все</a></li>
                {/if}
            </ul>
            {/if}

        </td>
        {if $smarty.foreach.s.iteration % $row eq 0}
    </tr>
    <tr>
        {/if}
    {/foreach}
    </tr>
</table>
{/if}

{if ! empty($subs_filter)}{* значения фильтра как подкатегории *}

    <table id="subs" class="subs{$row}">
    <tr>
        {foreach from=$subs_filter item=f name=f}
        <td>
            {assign var=link value=$section->get_link(0, $f.id)}
            <a href="{$link}" class="big">{$f.name}</a><a href="{$link}">{$f.img}</a>

            {if $f.sub && $column}
                <ul>
                {foreach from=$f.sub item=sub key=k name=n}
                    <li {if $smarty.foreach.n.iteration gt $column}class="hide"{/if}>
                        <a href="{$sub.href}">{$sub.name}</a><abbr abbr="Ассортимент товаров">{$sub.qty}</abbr>
                    </li>
                {/foreach}
                {if $smarty.foreach.n.total gt $column}
                    <li><a class="toggler">+ Показать все</a></li>
                {/if}
                </ul>
            {/if}
        </td>
        {if $smarty.foreach.f.iteration % $row eq 0}
    </tr>
    <tr>
        {/if}
        {/foreach}
    </tr>
    </table>
{/if}

{$search_result|default:''} {* товары *}

{if empty($hide_text) && not $section->is_cloth()}
    <div {if not empty($search_result)}id="tag_text"{/if}>{$section->text}</div>
{/if}

{* google adwords remarketing params *}
<script>
    var google_tag_params = {
        ecomm_pagetype: 'category',
        ecomm_category: '{$section->name}'
    };
</script>

{* RR *}
{if $config->rr_enabled}
<script>
    rrApiOnReady.push(function() {
        try { rrApi.categoryView({$section->id}); } catch(e) { }
    })
</script>
{/if}

{* findologic category tracking *}
{if $config->instant_search == 'findologic'}
<script>
    _paq.push(['setEcommerceView',
        productSku = false,  
        productName = false,          
        category = ["{$section->name}"] 
    ]);
    _paq.push(['trackPageView']);
</script>
{/if}

<script>
    $(document).ready(function(){
        $('#tagsinsectionlink').trunk8({
            fill: '&hellip; <a id="read-more" href="#">Показать все</a>',
            lines: 2
        });

        $(document).on('click', '#read-more', function (event) {
            $(this).parent().trunk8('revert').append(' <a id="read-less" href="#">Основные метки</a>');
            return false;
        });

        $(document).on('click', '#read-less', function (event) {
            $(this).parent().trunk8();
            return false;
        });
    });
</script>