<div id="breadcrumb">    
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr; 
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{Route::url('action_list')}"><span itemprop="title">Акции</span></a>
    </span>
    &rarr; 
    <span>{$config->actions_header|default:'Акции месяца'}</span>
</div>

<div id="simple">

    <h1>{$config->actions_header|default:'Акции месяца'}</h1>

    {if ! empty($tags)}
    <div id="bg_actiontags">
        {foreach from=$tags item=tag}
            {if $tag->active}
                <span class="active">
                    {$tag->title}
                    <a href="{$tag->url}" class="delete_tag_link">&#10006;</a>
                </span>
            {else}
                <a href="{$tag->url}" class="tag_link">{$tag->title}</a>
            {/if}
        {/foreach}
    </div>
    {/if}

    <br />

    <ul id="action_banner_list">
        {include file='action/current_list_item.tpl' actions=$actions}
    </ul>

	<a class='more'></a>

    {if (not empty($smarty.get.all))}
        <div class="fl cl">
            <a href="{Route::url('action_list')}?all=1">Все акции</a>
        </div>
    {/if}

    <div class="fr cr">
        <a href="{Route::url('action_arhive')}">Архив акций</a>
    </div>

    {include file='action/star.tpl'}

</div>