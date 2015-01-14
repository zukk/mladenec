<div id="breadcrumb">
    <a href="/">Главная</a> &rarr;
    <a href="{Route::url('action_list')}" title="Все акции">Акции</a>
    {if $action->show_goods eq 1}  &rarr; <a href="{Route::url('action_current_list')}" title="{$config->actions_header|default:'Акции месяца'}">{$config->actions_header|default:'Акции месяца'}</a>{/if}
</div>

<div id="onew">
    {if $action->active && $action->total}
        <h1>Акция: &laquo;{$action->name}&raquo;</h1>
    {/if}

	{if not $action->active}<div class="closed_action">АКЦИЯ ЗАКОНЧИЛАСЬ!!!</div>{/if}

    <div class="cb action_header {if not $action->active}o50{/if}">
        {if $action->banner}
            <img src="{$action->banner}" alt="{$action->name|escape:html}" title="{$action->name|escape:html}" />
        {/if}
		{$action->text}
    </div>
    {if $action->active && not $action->total && $action->show_goods}
	    <h1 class="yell">Акция: &laquo;{$action->name}&raquo;</h1>

        <script>
        $(window).load(function() {
            $('#menu').css('marginTop', Math.max(0, $('h1.yell').position().top - $('#menu').position().top) + 25);
        });
        </script>

        {$search_result}
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
