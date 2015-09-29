<div id="breadcrumb">       
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span>Результаты поиска</span> 
</div>
<div class="yell">
    <h1>По запросу 
        {if Kohana::$server_name eq 'mladenec'}
            &laquo;{$smarty.get.q}&raquo; на Младенец.ру
        {else}
            &laquo;{$smarty.get.q}&raquo; на Eatmart.ru
        {/if}
        найдено:
    </h1>
</div>
{if ! empty($banner_pampers)}
<p>
    <a href="{Route::url('pampers')}">перейти в Магазин Pampers на нашем сайте</a>
</p>
{/if}

{if ! empty($search_error)}
	<div class="error">{$search_error}</div>
{/if}

{$search_result}

{* google adwords remarketing params *}
<script>
    var google_tag_params = {
        ecomm_pagetype: 'searchresults'
    };
</script>

{* findologic search results tracking *}
{if $config->instant_search == 'findologic'}
<script type="text/javascript">
    _paq.push(['trackSiteSearch',
        "{$smarty.get['q']}",         // Search keyword searched for; e.g. "shirt"
        "{$CATEGORY_NAME}", // Search category selected in your search engine. If you do not need this, set to false; e.g. "cat=Men, color=red"
        {$NUM_RESULTS}      // Number of results on the Search results page. Zero indicates a 'No Result Search Keyword'. Set to false if you don't know; e.g. 1
    ]);
</script>
{/if}
