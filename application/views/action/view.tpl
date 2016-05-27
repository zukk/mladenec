<div id="breadcrumb">   
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{Route::url('action_list')}"><span itemprop="title">Акции</span></a>
    </span>
    &rarr;
    <span>Акция: &laquo;{$action->name}&raquo;</span>
</div>

<div id="onew" class="yell">
    {if $action->active && $action->total}
        <h1>Акция: &laquo;{$action->name}&raquo;</h1>
    {/if}

	{if not $action->active}
        {if $action->from}
        <div class="closed_action">АКЦИЯ ЗАКОНЧИЛАСЬ!!!</div>
        {/if}
        <div class="closed_action">АКЦИЯ ЕЩЁ НЕ НАЧАЛАСЬ!!!</div>
    {/if}

    <div class="cb action_header {if not $action->active}o50{/if}">
        {if $action->banner}
            <img src="{$action->banner}" alt="{$action->name|escape:html}" title="{$action->name|escape:html}" />
        {/if}
		{$action->text}
    </div>
    {if $action->active && not $action->total && $action->show_goods}
	    <h1>Акция: &laquo;{$action->name}&raquo;</h1>

        <script>
        $(window).load(function() {
            $('#menu').css('marginTop', Math.max(0, $('.yell h1').position().top - $('#menu').position().top) + 25);
        });
        </script>

        {$search_result}
        {if $action->id == 193190}
            <div style="font-size: 14px">
                <div><a href="http://www.mladenec-shop.ru/actions/193191" target="_blank">Товары 1001 - 2000</a></div>
                <div><a href="http://www.mladenec-shop.ru/actions/193192" target="_blank">Товары 2001 - 2258</a></div>
            </div>
        {/if}

        {include file='action/star.tpl'}

    {/if}
</div>

<div class="fr cr">
	<a href="{Route::url('action_list')}">Перейти к списку активных акций</a><br /><br />
</div>

{if not $action->active}
	<div class="fl cl">
		<a href="{Route::url('action_arhive')}">Перейти в архив акций</a>
	</div>
{/if}
