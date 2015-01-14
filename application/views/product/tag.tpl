{assign var=column value=11}

{if ! empty($tag_section)}
{assign var=slink value=$tag_section->get_link(0)}

<div id="breadcrumb">
    <a href="/">Главная</a>
    &rarr; {$tag_section->parent->get_link()}
    &rarr; <a href="{$slink}">{$tag_section->name}</a>
    <i></i>
</div>
    <script>sphinx_location = '{$slink}';</script>
{else}

<div id="breadcrumb">
    <a href="/">Главная</a> &rarr;
    <a href="{Route::url('map')}">Карта сайта</a>  &rarr;
    <a href="{Route::url('tag_tree')}">Товары по категориям</a>
    <i></i>
</div>
{/if}

<h1 class="yell">{$tag->name}</h1>

{$search_result}

<div>{$tag->text}</div>