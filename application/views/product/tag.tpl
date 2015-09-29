{assign var=column value=11}

{if ! empty($tag_section)}
{assign var=slink value=$tag_section->get_link(0)}

<div id="breadcrumb">
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1" itemref="breadcrumb-2">
        <a href="{$tag_section->parent->get_link(false)}" itemprop="url"><span itemprop="title">{$tag_section->parent->name}</span></a>
    </span>
    {if $tag->name neq $tag_section->name}
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-2">
        <a href="{$slink}" itemprop="url"><span itemprop="title">{$tag_section->name}</span></a>
    </span>
    {/if}
    &rarr;
    <span>{$tag->name}</span>
    <i></i>
</div>
    <script>sphinx_location = '{$slink}';</script>
{else}

<div id="breadcrumb">          
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1" itemref="breadcrumb-2">
        <a href="{Route::url('map')}" itemprop="url"><span itemprop="title">Карта сайта</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-2">
        <a href="{Route::url('tag_tree')}" itemprop="url"><span itemprop="title">Товары по категориям</span></a>
    </span>
    &rarr;
    <span>{$tag->name}</span>
    <i></i>
</div>
{/if}

<div class="yell">
    <h1>{$tag->name}</h1>
</div>

{if $tag->code == 'catalog/japan/fruit'}
    <div>{$tag->text}</div>
{/if}

{$search_result}

{if ($smarty.get.page|default:0 lt 2) and $found and $tag->code != 'catalog/japan/fruit'}
    <div id="tag_text">{$tag->text}</div>
{/if}

